<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OrderStatusManagementTest extends TestCase
{
    use RefreshDatabase;

    private function makeSuperAdmin(): Admin
    {
        $admin = new Admin([
            'name' => 'Super Admin',
            'email' => 'super-'.uniqid().'@example.com',
            'password' => 'password',
        ]);
        $admin->role = Admin::ROLE_SUPER_ADMIN;
        $admin->shop_id = null;
        $admin->save();

        return $admin;
    }

    private function makeShopAdmin(Shop $shop): Admin
    {
        $admin = new Admin([
            'name' => "Admin for {$shop->slug}",
            'email' => "admin-{$shop->slug}@example.com",
            'password' => 'password',
        ]);
        $admin->role = Admin::ROLE_SHOP_ADMIN;
        $admin->shop_id = $shop->id;
        $admin->save();

        return $admin;
    }

    private function makeShop(): Shop
    {
        return Shop::create([
            'name' => 'Test Shop',
            'slug' => 'test-shop',
            'is_active' => true,
        ]);
    }

    private function makeProduct(Shop $shop, int $stock = 10): Product
    {
        return Product::create([
            'shop_id' => $shop->id,
            'name' => 'Test Product',
            'image_path' => 'products/test.jpg',
            'price' => 1000,
            'stock' => $stock,
            'is_active' => true,
        ]);
    }

    private function makeOrder(Shop $shop, string $status = 'placed'): Order
    {
        $user = User::factory()->create();

        return Order::create([
            'shop_id' => $shop->id,
            'user_id' => $user->id,
            'total_amount' => 1000,
            'status' => $status,
        ]);
    }

    public function test_shop_admin_cannot_access_order_status_management(): void
    {
        $shop = $this->makeShop();
        $admin = $this->makeShopAdmin($shop);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.order-statuses.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_create_a_custom_status_with_transitions(): void
    {
        $shop = $this->makeShop();
        $superAdmin = $this->makeSuperAdmin();
        $placed = OrderStatus::where('key', 'placed')->firstOrFail();

        $response = $this->actingAs($superAdmin, 'admin')
            ->post(route('admin.order-statuses.store'), [
                'key' => 'preparing',
                'label' => '準備中',
                'sort_order' => 4,
                'is_initial' => false,
                'is_void' => false,
                'next_status_ids' => [],
            ]);

        $response->assertRedirect(route('admin.order-statuses.index'));
        $this->assertDatabaseHas('order_statuses', ['key' => 'preparing', 'label' => '準備中']);

        $preparing = OrderStatus::where('key', 'preparing')->firstOrFail();

        // Wire it into the graph: placed -> preparing.
        $this->actingAs($superAdmin, 'admin')
            ->put(route('admin.order-statuses.update', $placed), [
                'label' => $placed->label,
                'sort_order' => $placed->sort_order,
                'is_initial' => true,
                'is_void' => false,
                'next_status_ids' => [$preparing->id],
            ])
            ->assertRedirect(route('admin.order-statuses.index'));

        $order = $this->makeOrder($shop, 'placed');

        // The newly created, admin-defined status is now a real, usable transition.
        $this->actingAs($superAdmin, 'admin')
            ->patch(route('admin.shop.orders.update-status', [$shop, $order]), [
                'status' => 'preparing',
            ])
            ->assertRedirect();

        $this->assertSame('preparing', $order->fresh()->status);
    }

    public function test_transition_not_in_configured_graph_is_rejected(): void
    {
        $shop = $this->makeShop();
        $superAdmin = $this->makeSuperAdmin();
        $order = $this->makeOrder($shop, 'handed_over');

        // handed_over has no outgoing transitions by default.
        $response = $this->actingAs($superAdmin, 'admin')
            ->patch(route('admin.shop.orders.update-status', [$shop, $order]), [
                'status' => 'cancelled',
            ]);

        $response->assertStatus(422);
        $this->assertSame('handed_over', $order->fresh()->status);
    }

    public function test_transitioning_into_a_void_status_restores_stock(): void
    {
        $shop = $this->makeShop();
        $superAdmin = $this->makeSuperAdmin();
        $product = $this->makeProduct($shop, stock: 5);
        $order = $this->makeOrder($shop, 'placed');
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit_price' => $product->price,
            'quantity' => 3,
            'subtotal' => $product->price * 3,
        ]);

        $this->actingAs($superAdmin, 'admin')
            ->patch(route('admin.shop.orders.update-status', [$shop, $order]), [
                'status' => 'cancelled',
            ])
            ->assertRedirect();

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame(8, $product->fresh()->stock);
    }

    public function test_updating_a_status_does_not_touch_its_key(): void
    {
        $superAdmin = $this->makeSuperAdmin();
        $status = OrderStatus::where('key', 'handed_over')->firstOrFail();

        $this->actingAs($superAdmin, 'admin')
            ->put(route('admin.order-statuses.update', $status), [
                'label' => 'お渡し完了',
                'sort_order' => $status->sort_order,
                'is_initial' => false,
                'is_void' => false,
                'next_status_ids' => [],
            ])
            ->assertRedirect(route('admin.order-statuses.index'));

        $this->assertSame('handed_over', $status->fresh()->key);
        $this->assertSame('お渡し完了', $status->fresh()->label);
    }

    public function test_submitting_a_key_change_on_update_is_rejected(): void
    {
        $superAdmin = $this->makeSuperAdmin();
        $status = OrderStatus::where('key', 'handed_over')->firstOrFail();

        $this->actingAs($superAdmin, 'admin')
            ->put(route('admin.order-statuses.update', $status), [
                'key' => 'renamed-key',
                'label' => $status->label,
                'sort_order' => $status->sort_order,
                'is_initial' => false,
                'is_void' => false,
                'next_status_ids' => [],
            ])
            ->assertSessionHasErrors('key');

        $this->assertSame('handed_over', $status->fresh()->key);
    }

    public function test_cannot_delete_a_status_in_use_by_an_order(): void
    {
        $shop = $this->makeShop();
        $superAdmin = $this->makeSuperAdmin();
        $this->makeOrder($shop, 'handed_over');
        $status = OrderStatus::where('key', 'handed_over')->firstOrFail();

        $this->actingAs($superAdmin, 'admin')
            ->delete(route('admin.order-statuses.destroy', $status))
            ->assertRedirect();

        $this->assertModelExists($status);
    }

    public function test_database_rejects_deleting_a_referenced_status_even_bypassing_the_app(): void
    {
        $shop = $this->makeShop();
        $this->makeOrder($shop, 'handed_over');

        $this->expectException(QueryException::class);

        DB::table('order_statuses')->where('key', 'handed_over')->delete();
    }

    public function test_database_rejects_an_order_status_not_present_in_order_statuses(): void
    {
        $shop = $this->makeShop();
        $order = $this->makeOrder($shop, 'placed');

        $this->expectException(QueryException::class);

        DB::table('orders')->where('id', $order->id)->update(['status' => 'not_a_real_status']);
    }

    public function test_cannot_delete_the_initial_status(): void
    {
        $superAdmin = $this->makeSuperAdmin();
        $status = OrderStatus::where('key', 'placed')->firstOrFail();

        $this->actingAs($superAdmin, 'admin')
            ->delete(route('admin.order-statuses.destroy', $status))
            ->assertRedirect();

        $this->assertModelExists($status);
    }

    public function test_cannot_leave_zero_initial_statuses(): void
    {
        $superAdmin = $this->makeSuperAdmin();
        $status = OrderStatus::where('key', 'placed')->firstOrFail();

        $this->actingAs($superAdmin, 'admin')
            ->put(route('admin.order-statuses.update', $status), [
                'label' => $status->label,
                'sort_order' => $status->sort_order,
                'is_initial' => false,
                'is_void' => false,
                'next_status_ids' => [],
            ])
            ->assertSessionHasErrors('is_initial');

        $this->assertTrue($status->fresh()->is_initial);
    }

    public function test_checkout_places_new_order_in_the_configured_initial_status(): void
    {
        $shop = $this->makeShop();
        $product = $this->makeProduct($shop, stock: 10);
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withSession(["cart.{$shop->id}" => [$product->id => 2]])
            ->post(route('orders.store', $shop));

        $response->assertRedirect(route('shops.show', $shop));

        $order = Order::where('user_id', $user->id)->firstOrFail();
        $this->assertSame('placed', $order->status);
    }
}
