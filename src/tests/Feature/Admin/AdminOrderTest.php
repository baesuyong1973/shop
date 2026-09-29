<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminOrderTest extends TestCase
{
    use CreatesAdminTestData;
    use RefreshDatabase;

    public function test_店舗の注文一覧にはキャンセル済みの注文が表示されない(): void
    {
        $shop = $this->makeShop();
        $admin = $this->makeShopAdmin($shop);
        $placed = $this->makeOrder($shop);
        $this->makeOrder($shop, status: 'cancelled');
        $this->makeOrder($this->makeShop('other-shop'));

        $this->actingAs($admin, 'admin')
            ->get(route('admin.shop.orders.index', $shop))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Orders/Index')
                ->has('orders.data', 1)
                ->where('orders.data.0.id', $placed->id));
    }

    public function test_店舗の注文詳細が表示される(): void
    {
        $shop = $this->makeShop();
        $admin = $this->makeShopAdmin($shop);
        $order = $this->makeOrder($shop, quantity: 2);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.shop.orders.show', [$shop, $order]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Orders/Show')
                ->where('order.id', $order->id)
                ->has('order.items', 1)
                ->where('order.items.0.quantity', 2));
    }

    public function test_注文を受け渡し済みに変更しても在庫は変わらない(): void
    {
        $shop = $this->makeShop();
        $admin = $this->makeShopAdmin($shop);
        $product = $this->makeProduct($shop, ['stock' => 5]);
        $order = $this->makeOrder($shop, product: $product, quantity: 2);

        $this->actingAs($admin, 'admin')
            ->from(route('admin.shop.orders.show', [$shop, $order]))
            ->patch(route('admin.shop.orders.update-status', [$shop, $order]), ['status' => 'handed_over'])
            ->assertRedirect(route('admin.shop.orders.show', [$shop, $order]))
            ->assertSessionHas('status');

        $this->assertSame('handed_over', $order->fresh()->status);
        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_キャンセル済みの注文は再度変更できず在庫も二重に戻らない(): void
    {
        $shop = $this->makeShop();
        $admin = $this->makeShopAdmin($shop);
        $product = $this->makeProduct($shop, ['stock' => 5]);
        $order = $this->makeOrder($shop, product: $product, quantity: 2);

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.shop.orders.update-status', [$shop, $order]), ['status' => 'cancelled']);
        $this->actingAs($admin, 'admin')
            ->patch(route('admin.shop.orders.update-status', [$shop, $order]), ['status' => 'cancelled'])
            ->assertStatus(422);

        $this->assertSame(7, $product->fresh()->stock);
    }

    public function test_存在しないステータスへの変更はエラーになる(): void
    {
        $shop = $this->makeShop();
        $admin = $this->makeShopAdmin($shop);
        $order = $this->makeOrder($shop);

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.shop.orders.update-status', [$shop, $order]), ['status' => 'no_such_status'])
            ->assertSessionHasErrors('status');

        $this->assertSame('placed', $order->fresh()->status);
    }

    public function test_削除済み商品を含む注文もキャンセルできる(): void
    {
        $shop = $this->makeShop();
        $admin = $this->makeShopAdmin($shop);
        $order = $this->makeOrder($shop);
        $order->items()->update(['product_id' => null]);

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.shop.orders.update-status', [$shop, $order]), ['status' => 'cancelled'])
            ->assertRedirect();

        $this->assertSame('cancelled', $order->fresh()->status);
    }

    public function test_集計画面で商品別とユーザー別の合計が表示される(): void
    {
        $shop = $this->makeShop();
        $admin = $this->makeShopAdmin($shop);
        $apple = $this->makeProduct($shop, ['name' => 'りんご', 'price' => 100]);
        $banana = $this->makeProduct($shop, ['name' => 'バナナ', 'price' => 200]);
        $taro = User::factory()->create(['name' => '太郎']);
        $hanako = User::factory()->create(['name' => '花子']);

        $this->makeOrder($shop, $taro, product: $apple, quantity: 3);
        $this->makeOrder($shop, $hanako, product: $apple, quantity: 2);
        $this->makeOrder($shop, $hanako, product: $banana, quantity: 1);
        $this->makeOrder($shop, $taro, 'cancelled', $banana, 10);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.shop.orders.summary', $shop))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Orders/Summary')
                ->has('summary', 2)
                ->where('summary.0.product_name', 'りんご')
                ->where('summary.0.total_quantity', 5)
                ->where('summary.1.product_name', 'バナナ')
                ->where('summary.1.total_quantity', 1)
                ->has('userSummary', 2)
                ->where('userSummary.0.user_name', '花子')
                ->where('userSummary.0.total_amount', 400)
                ->has('userSummary.0.items', 2)
                ->where('userSummary.1.user_name', '太郎')
                ->where('userSummary.1.total_amount', 300));
    }

    public function test_集計画面は期間で絞り込める(): void
    {
        $shop = $this->makeShop();
        $admin = $this->makeShopAdmin($shop);

        $this->travelTo('2026-09-01 12:00:00');
        $this->makeOrder($shop, quantity: 1);
        $this->travelTo('2026-09-15 12:00:00');
        $this->makeOrder($shop, quantity: 4);
        $this->travelBack();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.shop.orders.summary', [$shop, 'date_from' => '2026-09-10', 'date_to' => '2026-09-20']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('dateFrom', '2026-09-10')
                ->where('dateTo', '2026-09-20')
                ->has('summary', 1)
                ->where('summary.0.total_quantity', 4)
                ->has('userSummary', 1));
    }

    public function test_集計画面で終了日が開始日より前だとエラーになる(): void
    {
        $shop = $this->makeShop();
        $admin = $this->makeShopAdmin($shop);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.shop.orders.summary', [$shop, 'date_from' => '2026-09-20', 'date_to' => '2026-09-10']))
            ->assertSessionHasErrors('date_to');
    }

    public function test_全店舗の注文一覧が表示され店舗で絞り込める(): void
    {
        $admin = $this->makeSuperAdmin();
        $shopA = $this->makeShop('shop-a');
        $shopB = $this->makeShop('shop-b');
        $orderA = $this->makeOrder($shopA);
        $this->makeOrder($shopB);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Orders/Index')
                ->has('orders.data', 2)
                ->has('shops', 2)
                ->where('filters.shop_id', null));

        $this->actingAs($admin, 'admin')
            ->get(route('admin.orders.index', ['shop_id' => $shopA->id]))
            ->assertInertia(fn (Assert $page) => $page
                ->has('orders.data', 1)
                ->where('orders.data.0.id', $orderA->id)
                ->where('filters.shop_id', $shopA->id));
    }

    public function test_全店舗ビューで注文詳細の表示とステータス変更ができる(): void
    {
        $admin = $this->makeSuperAdmin();
        $shop = $this->makeShop();
        $product = $this->makeProduct($shop, ['stock' => 5]);
        $order = $this->makeOrder($shop, product: $product, quantity: 3);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Orders/Show')
                ->where('order.id', $order->id)
                ->where('order.shop.id', $shop->id));

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.orders.update-status', $order), ['status' => 'cancelled'])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame(8, $product->fresh()->stock);
    }

    public function test_店舗管理者は全店舗の注文画面を使えない(): void
    {
        $shop = $this->makeShop();
        $admin = $this->makeShopAdmin($shop);
        $order = $this->makeOrder($shop);

        $this->actingAs($admin, 'admin')->get(route('admin.orders.index'))->assertForbidden();
        $this->actingAs($admin, 'admin')->get(route('admin.orders.show', $order))->assertForbidden();
        $this->actingAs($admin, 'admin')
            ->patch(route('admin.orders.update-status', $order), ['status' => 'cancelled'])
            ->assertForbidden();

        $this->assertSame('placed', $order->fresh()->status);
    }
}
