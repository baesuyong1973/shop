<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Shop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    private function makeShop(string $slug = 'test-shop'): Shop
    {
        return Shop::create([
            'name' => "Shop {$slug}",
            'slug' => $slug,
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

    public function test_空のカートが表示される(): void
    {
        $shop = $this->makeShop();

        $this->get(route('cart.index', $shop))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Cart/Index')
                ->has('items', 0)
                ->where('total', 0));
    }

    public function test_商品をカートに追加できる(): void
    {
        $shop = $this->makeShop();
        $product = $this->makeProduct($shop);

        $this->post(route('cart.store', [$shop, $product]), ['quantity' => 2])
            ->assertRedirect(route('cart.index', $shop))
            ->assertSessionHas("cart.{$shop->id}", [$product->id => 2]);
    }

    public function test_カートに商品ごとの小計と合計が表示される(): void
    {
        $shop = $this->makeShop();
        $apple = $this->makeProduct($shop, ['name' => 'Apple', 'price' => 150]);
        $banana = $this->makeProduct($shop, ['name' => 'Banana', 'price' => 300]);

        $this->withSession(["cart.{$shop->id}" => [$apple->id => 2, $banana->id => 1]])
            ->get(route('cart.index', $shop))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Cart/Index')
                ->has('items', 2)
                ->where('total', 600));
    }

    public function test_同じ商品を再度追加すると数量が加算される(): void
    {
        $shop = $this->makeShop();
        $product = $this->makeProduct($shop);

        $this->post(route('cart.store', [$shop, $product]), ['quantity' => 2]);
        $this->post(route('cart.store', [$shop, $product]), ['quantity' => 3])
            ->assertSessionHas("cart.{$shop->id}", [$product->id => 5]);
    }

    public function test_加算後の数量は在庫数を上限とする(): void
    {
        $shop = $this->makeShop();
        $product = $this->makeProduct($shop, ['stock' => 5]);

        $this->post(route('cart.store', [$shop, $product]), ['quantity' => 4]);
        $this->post(route('cart.store', [$shop, $product]), ['quantity' => 4])
            ->assertSessionHas("cart.{$shop->id}", [$product->id => 5]);
    }

    public function test_在庫数を超える数量は拒否される(): void
    {
        $shop = $this->makeShop();
        $product = $this->makeProduct($shop, ['stock' => 3]);

        $this->post(route('cart.store', [$shop, $product]), ['quantity' => 4])
            ->assertSessionHasErrors('quantity')
            ->assertSessionMissing("cart.{$shop->id}");
    }

    public function test_数量が0または未入力の場合は拒否される(): void
    {
        $shop = $this->makeShop();
        $product = $this->makeProduct($shop);

        $this->post(route('cart.store', [$shop, $product]), ['quantity' => 0])
            ->assertSessionHasErrors('quantity');
        $this->post(route('cart.store', [$shop, $product]), [])
            ->assertSessionHasErrors('quantity');
    }

    public function test_在庫切れの商品は追加できない(): void
    {
        $shop = $this->makeShop();
        $product = $this->makeProduct($shop, ['stock' => 0]);

        $this->post(route('cart.store', [$shop, $product]), ['quantity' => 1])
            ->assertSessionHasErrors('quantity')
            ->assertSessionMissing("cart.{$shop->id}");
    }

    public function test_非公開の商品は追加できない(): void
    {
        $shop = $this->makeShop();
        $product = $this->makeProduct($shop, ['is_active' => false]);

        $this->post(route('cart.store', [$shop, $product]), ['quantity' => 1])
            ->assertNotFound();
    }

    public function test_他店舗の商品は追加できない(): void
    {
        $shop = $this->makeShop('shop-a');
        $otherProduct = $this->makeProduct($this->makeShop('shop-b'));

        $this->post(route('cart.store', [$shop, $otherProduct]), ['quantity' => 1])
            ->assertNotFound();
    }

    public function test_カートは店舗ごとに分かれている(): void
    {
        $shopA = $this->makeShop('shop-a');
        $shopB = $this->makeShop('shop-b');
        $productA = $this->makeProduct($shopA);
        $productB = $this->makeProduct($shopB);

        $this->post(route('cart.store', [$shopA, $productA]), ['quantity' => 1]);
        $this->post(route('cart.store', [$shopB, $productB]), ['quantity' => 2])
            ->assertSessionHas("cart.{$shopA->id}", [$productA->id => 1])
            ->assertSessionHas("cart.{$shopB->id}", [$productB->id => 2]);
    }

    public function test_カートから商品を削除できる(): void
    {
        $shop = $this->makeShop();
        $keep = $this->makeProduct($shop);
        $remove = $this->makeProduct($shop);

        $this->withSession(["cart.{$shop->id}" => [$keep->id => 1, $remove->id => 2]])
            ->delete(route('cart.destroy', [$shop, $remove]))
            ->assertRedirect(route('cart.index', $shop))
            ->assertSessionHas("cart.{$shop->id}", [$keep->id => 1]);
    }
}
