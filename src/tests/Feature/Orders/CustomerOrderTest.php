<?php

namespace Tests\Feature\Orders;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use RuntimeException;
use Tests\TestCase;

class CustomerOrderTest extends TestCase
{
    use RefreshDatabase;

    private function makeShop(): Shop
    {
        return Shop::create([
            'name' => 'Test Shop',
            'slug' => 'test-shop',
            'is_active' => true,
        ]);
    }

    private function makeProduct(Shop $shop, array $attributes = []): Product
    {
        return Product::create(array_merge([
            'shop_id' => $shop->id,
            'name' => 'Test Product',
            'image_path' => 'products/test.jpg',
            'price' => 1000,
            'stock' => 10,
            'is_active' => true,
        ], $attributes));
    }

    private function checkout(User $user, Shop $shop, array $cart)
    {
        return $this->actingAs($user)
            ->withSession(["cart.{$shop->id}" => $cart])
            ->post(route('orders.store', $shop));
    }

    public function test_注文すると合計金額が計算され在庫が減りカートが空になる(): void
    {
        Mail::fake();
        $shop = $this->makeShop();
        $apple = $this->makeProduct($shop, ['price' => 150, 'stock' => 10]);
        $banana = $this->makeProduct($shop, ['price' => 300, 'stock' => 5]);
        $user = User::factory()->create();

        $this->checkout($user, $shop, [$apple->id => 2, $banana->id => 1])
            ->assertRedirect(route('shops.show', $shop))
            ->assertSessionMissing("cart.{$shop->id}");

        $order = Order::where('user_id', $user->id)->firstOrFail();
        $this->assertSame(600, $order->total_amount);
        $this->assertCount(2, $order->items);
        $this->assertSame(8, $apple->fresh()->stock);
        $this->assertSame(4, $banana->fresh()->stock);
    }

    public function test_カートが空の場合は注文できない(): void
    {
        $shop = $this->makeShop();
        $user = User::factory()->create();

        $this->checkout($user, $shop, [])
            ->assertRedirect(route('cart.index', $shop))
            ->assertSessionHas('error', 'カートに商品がありません。');

        $this->assertSame(0, Order::count());
    }

    public function test_在庫が足りない商品があると注文できず在庫も変わらない(): void
    {
        $shop = $this->makeShop();
        $product = $this->makeProduct($shop, ['stock' => 1]);
        $user = User::factory()->create();

        $this->checkout($user, $shop, [$product->id => 2])
            ->assertRedirect(route('cart.index', $shop))
            ->assertSessionHas('error');

        $this->assertSame(0, Order::count());
        $this->assertSame(1, $product->fresh()->stock);
    }

    public function test_カートに入れた後で非公開になった商品は注文できない(): void
    {
        $shop = $this->makeShop();
        $product = $this->makeProduct($shop, ['is_active' => false]);
        $user = User::factory()->create();

        $this->checkout($user, $shop, [$product->id => 1])
            ->assertRedirect(route('cart.index', $shop))
            ->assertSessionHas('error');

        $this->assertSame(0, Order::count());
    }

    public function test_カートに入れた後で削除された商品は注文できない(): void
    {
        $shop = $this->makeShop();
        $user = User::factory()->create();

        $this->checkout($user, $shop, [999999 => 1])
            ->assertRedirect(route('cart.index', $shop))
            ->assertSessionHas('error');

        $this->assertSame(0, Order::count());
    }

    public function test_初期ステータスが設定されていないと注文は作成されない(): void
    {
        $shop = $this->makeShop();
        $product = $this->makeProduct($shop);
        $user = User::factory()->create();
        OrderStatus::query()->update(['is_initial' => false]);

        $this->withoutExceptionHandling();
        $this->expectException(RuntimeException::class);

        try {
            $this->checkout($user, $shop, [$product->id => 1]);
        } finally {
            $this->assertSame(0, Order::count());
            $this->assertSame(10, $product->fresh()->stock);
        }
    }

    public function test_未ログインでは注文できない(): void
    {
        $shop = $this->makeShop();
        $product = $this->makeProduct($shop);

        $this->withSession(["cart.{$shop->id}" => [$product->id => 1]])
            ->post(route('orders.store', $shop))
            ->assertRedirect(route('login'));

        $this->assertSame(0, Order::count());
    }

    public function test_自分の注文詳細を表示できる(): void
    {
        Mail::fake();
        $shop = $this->makeShop();
        $product = $this->makeProduct($shop);
        $user = User::factory()->create();
        $this->checkout($user, $shop, [$product->id => 1]);
        $order = Order::firstOrFail();

        $this->actingAs($user)
            ->get(route('orders.show', [$shop, $order]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Orders/Show')
                ->where('order.id', $order->id)
                ->has('order.items', 1)
                ->where('order.shop.id', $shop->id));
    }

    public function test_他人の注文詳細は表示できない(): void
    {
        Mail::fake();
        $shop = $this->makeShop();
        $product = $this->makeProduct($shop);
        $owner = User::factory()->create();
        $this->checkout($owner, $shop, [$product->id => 1]);
        $order = Order::firstOrFail();

        $this->actingAs(User::factory()->create())
            ->get(route('orders.show', [$shop, $order]))
            ->assertNotFound();
    }
}
