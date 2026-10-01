<?php

namespace Tests\Feature\Payments;

use App\Models\Order;
use App\Payments\PaymentException;
use App\Payments\PayPayGateway;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PayPayGatewayTest extends TestCase
{
    private const SANDBOX = 'https://stg-api.sandbox.paypay.ne.jp';

    private PayPayGateway $gateway;

    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo('2026-10-01 12:00:00');
        $this->gateway = new PayPayGateway('test-key', 'test-secret', 'merchant-1');

        $this->order = new Order;
        $this->order->forceFill([
            'id' => 42,
            'total_amount' => 1234,
            'deposit_amount' => 124,
            'payment_reference' => 'order-42-abc',
            'payment_code_id' => 'code-1',
            'payment_id' => 'pay-1',
        ]);
    }

    private static function success(array $data = []): array
    {
        return ['resultInfo' => ['code' => 'SUCCESS', 'message' => 'Success'], 'data' => $data];
    }

    /**
     * Recompute the OPA-Auth header from the request the way PayPay checks it.
     */
    private function assertSigned(Request $request, ?string $body): void
    {
        $this->assertSame('merchant-1', $request->header('X-ASSUME-MERCHANT')[0]);

        $this->assertMatchesRegularExpression('/^hmac OPA-Auth:test-key:[^:]+:[^:]+:\d+:[^:]+$/', $request->header('Authorization')[0]);
        [, , $signature, $nonce, $epoch, $hash] = explode(':', substr($request->header('Authorization')[0], strlen('hmac OPA-Auth')));
        $this->assertSame((string) now()->getTimestamp(), $epoch);

        $contentType = $body === null ? 'empty' : 'application/json;charset=UTF-8';
        $expectedHash = $body === null ? 'empty' : base64_encode(md5($contentType.$body, true));
        $this->assertSame($expectedHash, $hash);

        $path = parse_url($request->url(), PHP_URL_PATH);
        $signed = implode("\n", [$path, $request->method(), $nonce, $epoch, $contentType, $expectedHash]);
        $this->assertSame(base64_encode(hash_hmac('sha256', $signed, 'test-secret', true)), $signature);
    }

    public function test_支払い用のQRコードを作成して支払い画面のURLを返す(): void
    {
        Http::fake([self::SANDBOX.'/v2/codes' => Http::response(self::success(['url' => 'https://paypay.example/pay', 'codeId' => 'code-9']))]);

        $session = $this->gateway->createPayment($this->order, 'https://shop.example/return');

        $this->assertSame('https://paypay.example/pay', $session->paymentUrl);
        $this->assertSame('code-9', $session->codeId);

        Http::assertSent(function (Request $request) {
            $this->assertSame('POST', $request->method());
            $this->assertSame([
                'merchantPaymentId' => 'order-42-abc',
                'amount' => ['amount' => 124, 'currency' => 'JPY'],
                'codeType' => 'ORDER_QR',
                'orderDescription' => '注文番号42 前払い',
                'isAuthorization' => false,
                'redirectUrl' => 'https://shop.example/return',
                'redirectType' => 'WEB_LINK',
            ], $request->data());
            $this->assertSame('application/json;charset=UTF-8', $request->header('Content-Type')[0]);
            $this->assertSigned($request, $request->body());

            return true;
        });
    }

    public function test_支払いの状況を取得する(): void
    {
        Http::fake([self::SANDBOX.'/v2/codes/payments/order-42-abc' => Http::sequence()
            ->push(self::success(['status' => 'COMPLETED', 'paymentId' => 'pay-77']))
            ->push(self::success(['status' => 'CREATED']))]);

        $completed = $this->gateway->fetchPayment($this->order);
        $created = $this->gateway->fetchPayment($this->order);

        $this->assertTrue($completed->completed);
        $this->assertSame('pay-77', $completed->paymentId);
        $this->assertFalse($created->completed);
        $this->assertNull($created->paymentId);

        Http::assertSent(function (Request $request) {
            $this->assertSame('GET', $request->method());
            $this->assertSigned($request, null);

            return true;
        });
    }

    public function test_未払いの支払いを取り消す(): void
    {
        Http::fake([self::SANDBOX.'/v2/codes/code-1' => Http::response(self::success())]);

        $this->gateway->cancelPayment($this->order);

        Http::assertSent(fn (Request $request) => $request->method() === 'DELETE');
    }

    public function test_QRコードが作られていなければ取り消しの通信はしない(): void
    {
        Http::fake();
        $this->order->payment_code_id = null;

        $this->gateway->cancelPayment($this->order);

        Http::assertNothingSent();
    }

    public function test_前払い額を返金する(): void
    {
        Http::fake([self::SANDBOX.'/v2/refunds' => Http::response(['resultInfo' => ['code' => 'REQUEST_ACCEPTED'], 'data' => ['status' => 'CREATED']])]);

        $this->gateway->refund($this->order);

        Http::assertSent(function (Request $request) {
            $this->assertSame([
                'merchantRefundId' => 'order-42-abc-refund',
                'paymentId' => 'pay-1',
                'amount' => ['amount' => 124, 'currency' => 'JPY'],
                'requestedAt' => now()->getTimestamp(),
                'reason' => '注文キャンセル',
            ], $request->data());
            $this->assertSigned($request, $request->body());

            return true;
        });
    }

    public function test_本番設定では本番のAPIに接続する(): void
    {
        Http::fake(['https://api.paypay.ne.jp/v2/codes/payments/*' => Http::response(self::success(['status' => 'COMPLETED']))]);

        $result = (new PayPayGateway('k', 's', 'm', production: true))->fetchPayment($this->order);

        $this->assertTrue($result->completed);
    }

    public function test_エラーの応答は例外になる(): void
    {
        Http::fake([self::SANDBOX.'/*' => Http::response(['resultInfo' => ['code' => 'UNAUTHORIZED', 'message' => 'Invalid auth']], 401)]);

        $this->expectException(PaymentException::class);
        $this->expectExceptionMessage('UNAUTHORIZED');

        $this->gateway->fetchPayment($this->order);
    }

    public function test_成功以外の結果コードは例外になる(): void
    {
        Http::fake([self::SANDBOX.'/*' => Http::response(['resultInfo' => ['code' => 'DYNAMIC_QR_ALREADY_PAID']])]);

        $this->expectException(PaymentException::class);

        $this->gateway->createPayment($this->order, 'https://shop.example/return');
    }

    public function test_接続できないときは例外になる(): void
    {
        Http::fake(fn () => throw new ConnectionException('timeout'));

        $this->expectException(PaymentException::class);
        $this->expectExceptionMessage('timeout');

        $this->gateway->fetchPayment($this->order);
    }
}
