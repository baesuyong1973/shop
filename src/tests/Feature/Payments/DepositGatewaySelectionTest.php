<?php

namespace Tests\Feature\Payments;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Shop;
use App\Models\User;
use App\Payments\DepositPayments;
use App\Payments\FakeDepositGateway;
use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionProperty;
use RuntimeException;
use Tests\TestCase;

class DepositGatewaySelectionTest extends TestCase
{
    use RefreshDatabase;

    private function resolvePayments(): DepositPayments
    {
        $this->app->forgetInstance(DepositPayments::class);
        (new AppServiceProvider($this->app))->register();

        return $this->app->make(DepositPayments::class);
    }

    private function gatewayOf(DepositPayments $payments): ?object
    {
        return (new ReflectionProperty($payments, 'gateway'))->getValue($payments);
    }

    public function test_設定がなければ前払いは無効(): void
    {
        config(['payment.driver' => null]);

        $this->assertFalse($this->resolvePayments()->enabled());
    }

    public function test_fake設定では疑似PayPayを使う(): void
    {
        config(['payment.driver' => 'fake']);

        $this->assertInstanceOf(FakeDepositGateway::class, $this->gatewayOf($this->resolvePayments()));
    }

    public function test_paypay設定ではPayPayのAPIを使う(): void
    {
        config(['payment.driver' => 'paypay', 'payment.paypay' => ['api_key' => 'k', 'api_secret' => 's', 'merchant_id' => 'm', 'production' => false]]);

        $this->assertInstanceOf(\App\Payments\PayPayGateway::class, $this->gatewayOf($this->resolvePayments()));
    }

    public function test_本番環境では疑似PayPayを使えない(): void
    {
        config(['payment.driver' => 'fake']);
        $this->app->detectEnvironment(fn () => 'production');

        $this->expectException(RuntimeException::class);

        $this->resolvePayments();
    }

    public function test_未知の設定はエラーになる(): void
    {
        config(['payment.driver' => 'unknown']);

        $this->expectException(RuntimeException::class);

        $this->resolvePayments();
    }

    public function test_支払い待ちでない注文は確定も取り消しもしない(): void
    {
        $order = Order::create([
            'shop_id' => Shop::create(['name' => 'S', 'slug' => 's', 'is_active' => true])->id,
            'user_id' => User::factory()->create()->id,
            'total_amount' => 100,
            'status' => 'placed',
            'payment_status' => Order::PAYMENT_PAID,
        ]);

        $this->assertTrue((new DepositPayments(new FakeDepositGateway))->settle($order));
        $this->assertSame('placed', $order->fresh()->status);
    }

    public function test_キャンセル扱いの状態がなければ未払いの注文を取り消せない(): void
    {
        $order = Order::create([
            'shop_id' => Shop::create(['name' => 'S', 'slug' => 's', 'is_active' => true])->id,
            'user_id' => User::factory()->create()->id,
            'total_amount' => 100,
            'status' => 'placed',
            'payment_status' => Order::PAYMENT_PENDING,
        ]);
        $order->forceFill(['payment_reference' => 'ref-1'])->save();
        FakeDepositGateway::put('ref-1', ['status' => FakeDepositGateway::STATUS_CANCELED, 'amount' => 10, 'order_id' => $order->id, 'return_url' => '/']);
        OrderStatus::query()->update(['is_void' => false]);

        $this->expectException(RuntimeException::class);

        (new DepositPayments(new FakeDepositGateway))->settle($order);
    }
}
