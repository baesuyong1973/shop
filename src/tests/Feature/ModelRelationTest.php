<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Order;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class ModelRelationTest extends TestCase
{
    use RefreshDatabase;

    private function makeShop(): Shop
    {
        return Shop::create(['name' => 'Test Shop', 'slug' => 'test-shop', 'is_active' => true]);
    }

    public function test_店舗が紐付かない店舗管理者は保存できない(): void
    {
        $admin = new Admin(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'password']);
        $admin->role = Admin::ROLE_SHOP_ADMIN;
        $admin->shop_id = null;

        $this->expectException(RuntimeException::class);

        $admin->save();
    }

    public function test_店舗が紐付いたスーパー管理者は保存できない(): void
    {
        $admin = new Admin(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'password']);
        $admin->role = Admin::ROLE_SUPER_ADMIN;
        $admin->shop_id = $this->makeShop()->id;

        $this->expectException(RuntimeException::class);

        $admin->save();
    }

    public function test_注文と注文明細と店舗言語の関連を取得できる(): void
    {
        $shop = $this->makeShop();
        $locale = $shop->locales()->create(['locale' => 'ja']);
        $order = Order::create([
            'shop_id' => $shop->id,
            'user_id' => User::factory()->create()->id,
            'total_amount' => 1000,
            'status' => 'placed',
        ]);
        $item = $order->items()->create([
            'product_name' => 'りんご',
            'unit_price' => 1000,
            'quantity' => 1,
            'subtotal' => 1000,
        ]);

        $this->assertSame('placed', $order->orderStatus->key);
        $this->assertTrue($item->order->is($order));
        $this->assertTrue($locale->shop->is($shop));
    }
}
