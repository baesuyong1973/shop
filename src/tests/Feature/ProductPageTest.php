<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Shop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProductPageTest extends TestCase
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

    public function test_公開中の商品ページが表示される(): void
    {
        $shop = $this->makeShop();
        $product = $this->makeProduct($shop, ['name' => 'Apple']);

        $this->get(route('products.show', [$shop, $product]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Products/Show')
                ->where('shop.id', $shop->id)
                ->where('product.id', $product->id)
                ->where('product.name', 'Apple'));
    }

    public function test_在庫切れの商品ページも表示される(): void
    {
        $shop = $this->makeShop();
        $product = $this->makeProduct($shop, ['stock' => 0]);

        $this->get(route('products.show', [$shop, $product]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('product.stock', 0));
    }

    public function test_非公開の商品ページは404になる(): void
    {
        $shop = $this->makeShop();
        $product = $this->makeProduct($shop, ['is_active' => false]);

        $this->get(route('products.show', [$shop, $product]))->assertNotFound();
    }

    public function test_他店舗のURLでは商品が表示されない(): void
    {
        $shop = $this->makeShop('shop-a');
        $otherProduct = $this->makeProduct($this->makeShop('shop-b'));

        $this->get(route('products.show', [$shop, $otherProduct]))->assertNotFound();
    }

    public function test_存在しない商品は404になる(): void
    {
        $shop = $this->makeShop();

        $this->get("/shops/{$shop->slug}/products/999999")->assertNotFound();
    }
}
