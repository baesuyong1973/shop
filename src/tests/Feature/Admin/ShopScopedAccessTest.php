<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Order;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopScopedAccessTest extends TestCase
{
    use RefreshDatabase;

    private function makeShop(string $slug): Shop
    {
        return Shop::create([
            'name' => "Shop {$slug}",
            'slug' => $slug,
            'is_active' => true,
        ]);
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

    private function makeProduct(Shop $shop): Product
    {
        return Product::create([
            'shop_id' => $shop->id,
            'name' => 'Test Product',
            'image_path' => 'products/test.jpg',
            'price' => 1000,
            'stock' => 10,
            'is_active' => true,
        ]);
    }

    private function makeOrder(Shop $shop): Order
    {
        $user = User::factory()->create();

        return Order::create([
            'shop_id' => $shop->id,
            'user_id' => $user->id,
            'total_amount' => 1000,
            'status' => 'placed',
        ]);
    }

    public function test_shop_admin_cannot_view_another_shops_product_list(): void
    {
        $ownShop = $this->makeShop('own-shop');
        $otherShop = $this->makeShop('other-shop');
        $admin = $this->makeShopAdmin($ownShop);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.shop.products.index', $otherShop));

        $response->assertForbidden();
    }

    public function test_shop_admin_cannot_edit_another_shops_product(): void
    {
        $ownShop = $this->makeShop('own-shop');
        $otherShop = $this->makeShop('other-shop');
        $admin = $this->makeShopAdmin($ownShop);
        $product = $this->makeProduct($otherShop);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.shop.products.edit', [$otherShop, $product]));

        $response->assertForbidden();
    }

    public function test_shop_admin_cannot_delete_another_shops_product(): void
    {
        $ownShop = $this->makeShop('own-shop');
        $otherShop = $this->makeShop('other-shop');
        $admin = $this->makeShopAdmin($ownShop);
        $product = $this->makeProduct($otherShop);

        $response = $this->actingAs($admin, 'admin')
            ->delete(route('admin.shop.products.destroy', [$otherShop, $product]));

        $response->assertForbidden();
        $this->assertModelExists($product);
    }

    public function test_shop_admin_cannot_view_another_shops_order(): void
    {
        $ownShop = $this->makeShop('own-shop');
        $otherShop = $this->makeShop('other-shop');
        $admin = $this->makeShopAdmin($ownShop);
        $order = $this->makeOrder($otherShop);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.shop.orders.show', [$otherShop, $order]));

        $response->assertForbidden();
    }

    public function test_shop_admin_cannot_update_another_shops_order_status(): void
    {
        $ownShop = $this->makeShop('own-shop');
        $otherShop = $this->makeShop('other-shop');
        $admin = $this->makeShopAdmin($ownShop);
        $order = $this->makeOrder($otherShop);

        $response = $this->actingAs($admin, 'admin')
            ->patch(route('admin.shop.orders.update-status', [$otherShop, $order]), [
                'status' => 'handed_over',
            ]);

        $response->assertForbidden();
        $this->assertSame('placed', $order->fresh()->status);
    }

    public function test_shop_admin_can_manage_their_own_shop(): void
    {
        $shop = $this->makeShop('own-shop');
        $admin = $this->makeShopAdmin($shop);
        $product = $this->makeProduct($shop);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.shop.products.index', $shop));

        $response->assertOk();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.shop.products.edit', [$shop, $product]));

        $response->assertOk();
    }

    public function test_shop_admin_cannot_access_super_admin_only_routes(): void
    {
        $shop = $this->makeShop('own-shop');
        $admin = $this->makeShopAdmin($shop);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.products.index'))
            ->assertForbidden();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.orders.index'))
            ->assertForbidden();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.users.index'))
            ->assertForbidden();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.shops.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_access_any_shop_and_global_routes(): void
    {
        $shop = $this->makeShop('some-shop');
        $superAdmin = $this->makeSuperAdmin();
        $product = $this->makeProduct($shop);

        $this->actingAs($superAdmin, 'admin')
            ->get(route('admin.shop.products.index', $shop))
            ->assertOk();

        $this->actingAs($superAdmin, 'admin')
            ->get(route('admin.shop.products.edit', [$shop, $product]))
            ->assertOk();

        $this->actingAs($superAdmin, 'admin')
            ->get(route('admin.products.index'))
            ->assertOk();

        $this->actingAs($superAdmin, 'admin')
            ->get(route('admin.shops.index'))
            ->assertOk();
    }

    public function test_shop_admin_cannot_toggle_active_status_of_user_from_another_shop(): void
    {
        $ownShop = $this->makeShop('own-shop');
        $otherShop = $this->makeShop('other-shop');
        $admin = $this->makeShopAdmin($ownShop);

        $user = User::factory()->create();
        Order::create([
            'shop_id' => $otherShop->id,
            'user_id' => $user->id,
            'total_amount' => 1000,
            'status' => 'placed',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->patch(route('admin.shop.users.toggle-active', [$ownShop, $user]));

        $response->assertNotFound();
    }
}
