<?php

namespace App\Payments;

use App\Models\Order;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * PayPay Open Payment API (web payment via a dynamic QR code).
 *
 * Requests are signed with PayPay's "OPA-Auth" HMAC scheme. This talks to
 * the API directly because the official PHP SDK depends on firebase/php-jwt
 * versions with open security advisories.
 *
 * @see https://developer.paypay.ne.jp/products/docs/webpayment
 */
class PayPayGateway implements DepositGateway
{
    private const SANDBOX_URL = 'https://stg-api.sandbox.paypay.ne.jp';

    private const PRODUCTION_URL = 'https://api.paypay.ne.jp';

    public function __construct(
        private readonly string $apiKey,
        private readonly string $apiSecret,
        private readonly string $merchantId,
        private readonly bool $production = false,
    ) {}

    public function createPayment(Order $order, string $returnUrl): PaymentSession
    {
        $data = $this->send('POST', '/v2/codes', [
            'merchantPaymentId' => $order->payment_reference,
            'amount' => ['amount' => $order->deposit_amount, 'currency' => 'JPY'],
            'codeType' => 'ORDER_QR',
            'orderDescription' => "注文番号{$order->id} 前払い",
            'isAuthorization' => false,
            'redirectUrl' => $returnUrl,
            'redirectType' => 'WEB_LINK',
        ]);

        return new PaymentSession($data['url'], $data['codeId'] ?? null);
    }

    public function fetchPayment(Order $order): PaymentResult
    {
        $data = $this->send('GET', "/v2/codes/payments/{$order->payment_reference}");

        return new PaymentResult(
            completed: ($data['status'] ?? null) === 'COMPLETED',
            paymentId: $data['paymentId'] ?? null,
        );
    }

    public function cancelPayment(Order $order): void
    {
        if ($order->payment_code_id) {
            $this->send('DELETE', "/v2/codes/{$order->payment_code_id}");
        }
    }

    public function refund(Order $order): void
    {
        $this->send('POST', '/v2/refunds', [
            'merchantRefundId' => "{$order->payment_reference}-refund",
            'paymentId' => $order->payment_id,
            'amount' => ['amount' => $order->deposit_amount, 'currency' => 'JPY'],
            'requestedAt' => now()->getTimestamp(),
            'reason' => '注文キャンセル',
        ]);
    }

    /**
     * @return array<string, mixed> the response's "data" object
     *
     * @throws PaymentException
     */
    private function send(string $method, string $path, ?array $body = null): array
    {
        $json = $body === null ? null : json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $request = Http::withHeaders([
            'Authorization' => $this->authorization($method, $path, $json),
            'X-ASSUME-MERCHANT' => $this->merchantId,
        ])->timeout(15);

        if ($json !== null) {
            $request = $request->withBody($json, 'application/json;charset=UTF-8');
        }

        try {
            $response = $request->send($method, $this->baseUrl().$path);
        } catch (ConnectionException $e) {
            throw new PaymentException("PayPay {$method} {$path}: {$e->getMessage()}", previous: $e);
        }

        return $this->dataOrFail($response, "{$method} {$path}");
    }

    /**
     * "hmac OPA-Auth:<key>:<signature>:<nonce>:<epoch>:<body hash>", where the
     * signature is HMAC-SHA256 over path, method, nonce, epoch, content type
     * and body hash, and the body hash is MD5 of content type + body.
     */
    private function authorization(string $method, string $path, ?string $body): string
    {
        $nonce = Str::random(8);
        $epoch = (string) now()->getTimestamp();

        if ($body === null) {
            $contentType = 'empty';
            $hash = 'empty';
        } else {
            $contentType = 'application/json;charset=UTF-8';
            $hash = base64_encode(md5($contentType.$body, true));
        }

        $signed = implode("\n", [$path, $method, $nonce, $epoch, $contentType, $hash]);
        $signature = base64_encode(hash_hmac('sha256', $signed, $this->apiSecret, true));

        return "hmac OPA-Auth:{$this->apiKey}:{$signature}:{$nonce}:{$epoch}:{$hash}";
    }

    private function baseUrl(): string
    {
        return $this->production ? self::PRODUCTION_URL : self::SANDBOX_URL;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws PaymentException
     */
    private function dataOrFail(Response $response, string $operation): array
    {
        $code = $response->json('resultInfo.code');

        if ($response->failed() || ! in_array($code, ['SUCCESS', 'REQUEST_ACCEPTED'], true)) {
            $message = $response->json('resultInfo.message') ?? $response->body();

            throw new PaymentException("PayPay {$operation} failed ({$response->status()} {$code}): {$message}");
        }

        return $response->json('data') ?? [];
    }
}
