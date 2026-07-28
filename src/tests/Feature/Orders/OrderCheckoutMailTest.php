<?php

namespace Tests\Feature\Orders;

use App\Mail\AdminOrderNotification;
use App\Mail\OrderConfirmed;
use App\Models\Admin;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrderCheckoutMailTest extends TestCase
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

    public function test_checkout_sends_confirmation_to_customer_and_notification_to_shop_admins(): void
    {
        Mail::fake();

        $shop = $this->makeShop();
        $shopAdmin = $this->makeShopAdmin($shop);
        $product = $this->makeProduct($shop);
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withSession(["cart.{$shop->id}" => [$product->id => 2]])
            ->post(route('orders.store', $shop));

        $response->assertRedirect(route('shops.show', $shop));

        Mail::assertSent(OrderConfirmed::class, function (OrderConfirmed $mail) use ($user) {
            return $mail->hasTo($user->email) && $mail->order->user_id === $user->id;
        });

        Mail::assertSent(AdminOrderNotification::class, function (AdminOrderNotification $mail) use ($shopAdmin) {
            return $mail->hasTo($shopAdmin->email);
        });
    }
}
