<?php

namespace Tests\Feature\Orders;

use App\Mail\AdminOrderNotification;
use App\Mail\OrderConfirmed;
use App\Models\Admin;
use App\Models\Order;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use App\Payments\DepositGateway;
use App\Payments\DepositPayments;
use App\Payments\FakeDepositGateway;
use App\Payments\PaymentException;
use App\Payments\PaymentResult;
use App\Payments\PaymentSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DepositPaymentTest extends TestCase
{
    use RefreshDatabase;

    private Shop $shop;

    private Product $product;

    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        $this->useGateway(new FakeDepositGateway);

        $this->shop = Shop::create(['name' => 'Test Shop', 'slug' => 'test-shop', 'is_active' => true]);
        $this->product = Product::create([
            'shop_id' => $this->shop->id,
            'name' => 'りんご',
            'image_path' => 'products/test.jpg',
            'price' => 617,
            'stock' => 10,
            'is_active' => true,
        ]);
        $this->customer = User::factory()->create();
    }

    private function useGateway(DepositGateway $gateway): void
    {
        config(['payment.driver' => 'fake']);
        $this->app->instance(DepositPayments::class, new DepositPayments($gateway));
    }

    /**
     * Check out 2 apples (¥1,234, deposit ¥124) and return the pending order.
     */
    private function checkout(): Order
    {
        $this->actingAs($this->customer)
            ->withSession(["cart.{$this->shop->id}" => [$this->product->id => 2]])
            ->post(route('orders.store', $this->shop))
            ->assertRedirect(route('fake-paypay.show', Order::latest('id')->firstOrFail()->payment_reference));

        return Order::latest('id')->firstOrFail();
    }

    private function payFake(Order $order, bool $pay): void
    {
        $this->post(route('fake-paypay.update', $order->payment_reference), ['pay' => $pay ? '1' : '0'])
            ->assertRedirect(route('orders.payment.return', [$this->shop, $order]));
    }

    public function test_前払い額は合計の10パーセントを1円単位で切り上げる(): void
    {
        $this->assertSame(124, Order::depositFor(1234));
        $this->assertSame(100, Order::depositFor(1000));
        $this->assertSame(1, Order::depositFor(1));
        $this->assertSame(0, Order::depositFor(0));
    }

    public function test_カートに前払い額と店頭での残額が表示される(): void
    {
        $this->withSession(["cart.{$this->shop->id}" => [$this->product->id => 2]])
            ->get(route('cart.index', $this->shop))
            ->assertInertia(fn (Assert $page) => $page
                ->where('total', 1234)
                ->where('deposit.rate', 10)
                ->where('deposit.amount', 124)
                ->where('deposit.remaining', 1110));
    }

    public function test_前払いが無効ならカートに前払いは表示されない(): void
    {
        $this->app->instance(DepositPayments::class, new DepositPayments(null));

        $this->withSession(["cart.{$this->shop->id}" => [$this->product->id => 2]])
            ->get(route('cart.index', $this->shop))
            ->assertInertia(fn (Assert $page) => $page->where('deposit', null));
    }

    public function test_注文すると支払い待ちの注文が作られPayPayの支払い画面に移動する(): void
    {
        $order = $this->checkout();

        $this->assertSame(Order::PAYMENT_PENDING, $order->payment_status);
        $this->assertSame(1234, $order->total_amount);
        $this->assertSame(124, $order->deposit_amount);
        $this->assertSame(8, $this->product->fresh()->stock);
        $this->assertNotNull($order->payment_code_id);
        $this->assertSame([], $order->available_transitions);
        Mail::assertNothingSent();
    }

    public function test_Inertiaからの注文では外部の支払い画面への移動を指示する(): void
    {
        $this->actingAs($this->customer)
            ->withSession(["cart.{$this->shop->id}" => [$this->product->id => 1]])
            ->post(route('orders.store', $this->shop), [], ['X-Inertia' => 'true'])
            ->assertStatus(409)
            ->assertHeader('X-Inertia-Location', route('fake-paypay.show', Order::firstOrFail()->payment_reference));
    }

    public function test_支払うと注文が確定しメールが送られカートが空になる(): void
    {
        $order = $this->checkout();
        $this->payFake($order, true);

        $this->actingAs($this->customer)
            ->withSession(["cart.{$this->shop->id}" => [$this->product->id => 2]])
            ->get(route('orders.payment.return', [$this->shop, $order]))
            ->assertRedirect(route('shops.show', $this->shop))
            ->assertSessionHas('status', __('messages.orders.confirmed_with_deposit', ['id' => $order->id, 'deposit' => '124', 'remaining' => '1,110']))
            ->assertSessionMissing("cart.{$this->shop->id}");

        $order->refresh();
        $this->assertSame(Order::PAYMENT_PAID, $order->payment_status);
        $this->assertSame("fake-{$order->payment_reference}", $order->payment_id);
        $this->assertNotNull($order->paid_at);
        $this->assertSame(1110, $order->remaining_amount);
        $this->assertSame(8, $this->product->fresh()->stock);
        Mail::assertSent(OrderConfirmed::class, 1);
    }

    public function test_支払い後に戻り画面を再度開いても二重に確定しない(): void
    {
        $order = $this->checkout();
        $this->payFake($order, true);
        $this->actingAs($this->customer)->get(route('orders.payment.return', [$this->shop, $order]));
        $this->actingAs($this->customer)
            ->get(route('orders.payment.return', [$this->shop, $order]))
            ->assertRedirect(route('shops.show', $this->shop));

        Mail::assertSent(OrderConfirmed::class, 1);
    }

    public function test_支払わずに戻ると注文は取り消され在庫が戻りカートは残る(): void
    {
        $order = $this->checkout();
        $this->payFake($order, false);

        $this->actingAs($this->customer)
            ->withSession(["cart.{$this->shop->id}" => [$this->product->id => 2]])
            ->get(route('orders.payment.return', [$this->shop, $order]))
            ->assertRedirect(route('cart.index', $this->shop))
            ->assertSessionHas('error', __('messages.orders.payment_incomplete'))
            ->assertSessionHas("cart.{$this->shop->id}", [$this->product->id => 2]);

        $order->refresh();
        $this->assertSame(Order::PAYMENT_EXPIRED, $order->payment_status);
        $this->assertSame('cancelled', $order->status);
        $this->assertSame(10, $this->product->fresh()->stock);
        Mail::assertNothingSent();

        $this->actingAs($this->customer)
            ->get(route('orders.payment.return', [$this->shop, $order]))
            ->assertRedirect(route('cart.index', $this->shop));
        $this->assertSame(10, $this->product->fresh()->stock);
    }

    public function test_支払いを開始できないときは注文を取り消してカートに戻す(): void
    {
        $this->useGateway($this->failingGateway(onCreate: true));

        $this->actingAs($this->customer)
            ->withSession(["cart.{$this->shop->id}" => [$this->product->id => 2]])
            ->post(route('orders.store', $this->shop))
            ->assertRedirect(route('cart.index', $this->shop))
            ->assertSessionHas('error', __('messages.orders.payment_unavailable'))
            ->assertSessionHas("cart.{$this->shop->id}");

        $order = Order::firstOrFail();
        $this->assertSame(Order::PAYMENT_EXPIRED, $order->payment_status);
        $this->assertSame(10, $this->product->fresh()->stock);
    }

    public function test_支払い状況を確認できないときは注文を保留のままにする(): void
    {
        $order = $this->checkout();
        $this->useGateway($this->failingGateway(onFetch: true));

        $this->actingAs($this->customer)
            ->get(route('orders.payment.return', [$this->shop, $order]))
            ->assertRedirect(route('cart.index', $this->shop))
            ->assertSessionHas('error', __('messages.orders.payment_unconfirmed'));

        $this->assertSame(Order::PAYMENT_PENDING, $order->fresh()->payment_status);
        $this->assertSame(8, $this->product->fresh()->stock);
    }

    public function test_ほかの会員の注文の戻り画面は開けない(): void
    {
        $order = $this->checkout();

        $this->actingAs(User::factory()->create())
            ->get(route('orders.payment.return', [$this->shop, $order]))
            ->assertNotFound();
    }

    public function test_時間が過ぎた支払い待ちの注文は定期処理で確定または取り消される(): void
    {
        $paid = $this->checkout();
        $this->payFake($paid, true);
        $this->travel(31)->minutes();
        $unpaidOld = $this->checkout();
        $this->travel(31)->minutes();
        $recent = $this->checkout();

        $this->artisan('orders:settle-pending')
            ->expectsOutput("注文 {$paid->id}: 支払い済みのため確定")
            ->expectsOutput("注文 {$unpaidOld->id}: 未払いのため取り消し")
            ->assertSuccessful();

        $this->assertSame(Order::PAYMENT_PAID, $paid->fresh()->payment_status);
        $this->assertSame(Order::PAYMENT_EXPIRED, $unpaidOld->fresh()->payment_status);
        $this->assertSame(Order::PAYMENT_PENDING, $recent->fresh()->payment_status);
        $this->assertSame(6, $this->product->fresh()->stock);
        Mail::assertSent(OrderConfirmed::class, 1);
    }

    public function test_定期処理は支払い状況を確認できない注文を次回に回す(): void
    {
        $order = $this->checkout();
        $this->travel(31)->minutes();
        $this->useGateway($this->failingGateway(onFetch: true));

        $this->artisan('orders:settle-pending')
            ->expectsOutput("注文 {$order->id}: 支払い状況を確認できませんでした（次回再試行）")
            ->assertSuccessful();

        $this->assertSame(Order::PAYMENT_PENDING, $order->fresh()->payment_status);
    }

    public function test_前払いが無効なら定期処理は何もしない(): void
    {
        $this->app->instance(DepositPayments::class, new DepositPayments(null));

        $order = Order::create(['shop_id' => $this->shop->id, 'user_id' => $this->customer->id, 'total_amount' => 100, 'status' => 'placed', 'payment_status' => Order::PAYMENT_PENDING]);
        $this->travel(31)->minutes();

        $this->artisan('orders:settle-pending')->assertSuccessful();

        $this->assertSame(Order::PAYMENT_PENDING, $order->fresh()->payment_status);
    }

    public function test_支払い待ちや未払いの注文は店舗の注文一覧と集計に出ない(): void
    {
        $admin = $this->makeShopAdmin();
        $paid = $this->checkout();
        $this->payFake($paid, true);
        $this->actingAs($this->customer)->get(route('orders.payment.return', [$this->shop, $paid]));
        $pending = $this->checkout();
        $expired = $this->checkout();
        $this->payFake($expired, false);
        $this->actingAs($this->customer)->get(route('orders.payment.return', [$this->shop, $expired]));

        $this->actingAs($admin, 'admin')
            ->get(route('admin.shop.orders.index', $this->shop))
            ->assertInertia(fn (Assert $page) => $page
                ->has('orders.data', 1)
                ->where('orders.data.0.id', $paid->id)
                ->where('orders.data.0.deposit_amount', 124)
                ->where('orders.data.0.remaining_amount', 1110));

        $this->actingAs($admin, 'admin')
            ->get(route('admin.shop.orders.summary', $this->shop))
            ->assertInertia(fn (Assert $page) => $page
                ->where('summary.0.total_quantity', 2)
                ->where('userSummary.0.total_amount', 1234));

        $this->actingAs($admin, 'admin')
            ->get(route('admin.shop.users.show', [$this->shop, $this->customer]))
            ->assertInertia(fn (Assert $page) => $page->has('orders.data', 1));

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.shop.orders.update-status', [$this->shop, $pending]), ['status' => 'cancelled'])
            ->assertStatus(422);
    }

    public function test_前払い済みの注文をキャンセルすると返金され在庫が戻る(): void
    {
        $admin = $this->makeShopAdmin();
        $order = $this->paidOrder();

        $this->actingAs($admin, 'admin')
            ->from(route('admin.shop.orders.show', [$this->shop, $order]))
            ->patch(route('admin.shop.orders.update-status', [$this->shop, $order]), ['status' => 'cancelled'])
            ->assertRedirect(route('admin.shop.orders.show', [$this->shop, $order]))
            ->assertSessionHas('status', '注文のステータスを更新し、前払い金 ¥124 を返金しました。');

        $order->refresh();
        $this->assertSame('cancelled', $order->status);
        $this->assertSame(Order::PAYMENT_REFUNDED, $order->payment_status);
        $this->assertNotNull($order->refunded_at);
        $this->assertSame(FakeDepositGateway::STATUS_REFUNDED, FakeDepositGateway::get($order->payment_reference)['status']);
        $this->assertSame(10, $this->product->fresh()->stock);
    }

    public function test_返金に失敗したらキャンセルしない(): void
    {
        $admin = $this->makeShopAdmin();
        $order = $this->paidOrder();
        $this->useGateway($this->failingGateway(onRefund: true));

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.shop.orders.update-status', [$this->shop, $order]), ['status' => 'cancelled'])
            ->assertSessionHas('error', '前払い金の返金に失敗したため、キャンセルできませんでした。時間をおいて再度お試しください。');

        $order->refresh();
        $this->assertSame('placed', $order->status);
        $this->assertSame(Order::PAYMENT_PAID, $order->payment_status);
        $this->assertSame(8, $this->product->fresh()->stock);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.shop.orders.show', [$this->shop, $order]))
            ->assertInertia(fn (Assert $page) => $page
                ->where('error', '前払い金の返金に失敗したため、キャンセルできませんでした。時間をおいて再度お試しください。')
                ->where('order.payment_status', 'paid'));
    }

    public function test_前払い済みの注文を受け渡し済みにしても返金しない(): void
    {
        $admin = $this->makeShopAdmin();
        $order = $this->paidOrder();

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.shop.orders.update-status', [$this->shop, $order]), ['status' => 'handed_over'])
            ->assertSessionHas('status', '注文のステータスを更新しました。');

        $this->assertSame(Order::PAYMENT_PAID, $order->fresh()->payment_status);
    }

    public function test_前払いのない注文のキャンセルでは返金処理をしない(): void
    {
        $admin = $this->makeShopAdmin();
        $order = Order::create([
            'shop_id' => $this->shop->id,
            'user_id' => $this->customer->id,
            'total_amount' => 617,
            'status' => 'placed',
        ]);

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.shop.orders.update-status', [$this->shop, $order]), ['status' => 'cancelled'])
            ->assertSessionHas('status', '注文のステータスを更新しました。');

        $this->assertSame(Order::PAYMENT_NOT_REQUIRED, $order->fresh()->payment_status);
    }

    public function test_お客様の注文詳細に前払い額と店頭での残額が含まれる(): void
    {
        $order = $this->paidOrder();

        $this->actingAs($this->customer)
            ->get(route('orders.show', [$this->shop, $order]))
            ->assertInertia(fn (Assert $page) => $page
                ->where('order.deposit_amount', 124)
                ->where('order.remaining_amount', 1110)
                ->where('order.payment_status', 'paid'));
    }

    public function test_確認メールに前払い額と店頭での残額が記載される(): void
    {
        $order = $this->paidOrder()->load('items', 'shop', 'user');

        (new OrderConfirmed($order))->assertSeeInHtml('¥124')->assertSeeInHtml('¥1,110');
        (new AdminOrderNotification($order))->assertSeeInHtml('¥124')->assertSeeInHtml('¥1,110');
    }

    public function test_疑似PayPayの支払い画面が表示される(): void
    {
        $order = $this->checkout();

        $this->get(route('fake-paypay.show', $order->payment_reference))
            ->assertOk()
            ->assertSee('¥124')
            ->assertSee('支払う');

        $this->payFake($order, true);
        $this->get(route('fake-paypay.show', $order->payment_reference))->assertSee('手続き済み');
        $this->post(route('fake-paypay.update', $order->payment_reference), ['pay' => '1'])->assertStatus(409);
    }

    public function test_疑似PayPayは偽の設定以外では使えない(): void
    {
        $order = $this->checkout();
        config(['payment.driver' => 'paypay']);

        $this->get(route('fake-paypay.show', $order->payment_reference))->assertNotFound();
        $this->post(route('fake-paypay.update', $order->payment_reference), ['pay' => '1'])->assertNotFound();
        config(['payment.driver' => 'fake']);
        $this->get(route('fake-paypay.show', 'unknown'))->assertNotFound();
    }

    public function test_疑似PayPayは未完了の支払いを返金できない(): void
    {
        $order = $this->checkout();

        $this->expectException(PaymentException::class);
        (new FakeDepositGateway)->refund($order);
    }

    public function test_疑似PayPayで存在しない支払いの状況は取得できない(): void
    {
        $order = $this->checkout();
        $order->payment_reference = 'unknown';

        $this->expectException(PaymentException::class);
        (new FakeDepositGateway)->fetchPayment($order);
    }

    private function paidOrder(): Order
    {
        $order = $this->checkout();
        $this->payFake($order, true);
        $this->actingAs($this->customer)->get(route('orders.payment.return', [$this->shop, $order]));

        return $order->fresh();
    }

    private function makeShopAdmin(): Admin
    {
        $admin = new Admin(['name' => 'Shop Admin', 'email' => 'shop-admin@example.com', 'password' => 'password']);
        $admin->role = Admin::ROLE_SHOP_ADMIN;
        $admin->shop_id = $this->shop->id;
        $admin->save();

        return $admin;
    }

    /**
     * A fake gateway that throws at the given step and behaves normally otherwise.
     */
    private function failingGateway(bool $onCreate = false, bool $onFetch = false, bool $onRefund = false): DepositGateway
    {
        return new class($onCreate, $onFetch, $onRefund) extends FakeDepositGateway
        {
            public function __construct(private bool $onCreate, private bool $onFetch, private bool $onRefund) {}

            public function createPayment(Order $order, string $returnUrl): PaymentSession
            {
                return $this->onCreate ? throw new PaymentException('create failed') : parent::createPayment($order, $returnUrl);
            }

            public function fetchPayment(Order $order): PaymentResult
            {
                return $this->onFetch ? throw new PaymentException('fetch failed') : parent::fetchPayment($order);
            }

            public function refund(Order $order): void
            {
                $this->onRefund ? throw new PaymentException('refund failed') : parent::refund($order);
            }
        };
    }
}
