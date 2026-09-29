<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Order;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;

/**
 * Shared fixtures for admin feature tests.
 */
trait CreatesAdminTestData
{
    private function makeShop(string $slug = 'test-shop'): Shop
    {
        return Shop::create([
            'name' => "Shop {$slug}",
            'slug' => $slug,
            'is_active' => true,
        ]);
    }

    private function makeSuperAdmin(string $email = 'super@example.com'): Admin
    {
        $admin = new Admin([
            'name' => 'Super Admin',
            'email' => $email,
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

    /**
     * An order with a single line item for the given product.
     */
    private function makeOrder(Shop $shop, ?User $user = null, string $status = 'placed', ?Product $product = null, int $quantity = 1): Order
    {
        $product ??= $this->makeProduct($shop);

        $order = Order::create([
            'shop_id' => $shop->id,
            'user_id' => ($user ?? User::factory()->create())->id,
            'total_amount' => $product->price * $quantity,
            'status' => $status,
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit_price' => $product->price,
            'quantity' => $quantity,
            'subtotal' => $product->price * $quantity,
        ]);

        return $order;
    }
}
