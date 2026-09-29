<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Shop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    private function makeShop(array $attributes = []): Shop
    {
        return Shop::create(array_merge([
            'name' => 'Test Shop',
            'slug' => 'test-shop',
            'is_active' => true,
        ], $attributes));
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

    public function test_トップページには公開中の店舗だけが名前順に表示される(): void
    {
        $b = $this->makeShop(['name' => 'B店', 'slug' => 'b-shop']);
        $a = $this->makeShop(['name' => 'A店', 'slug' => 'a-shop']);
        $this->makeShop(['name' => '非公開店', 'slug' => 'hidden', 'is_active' => false]);

        $this->get(route('shops.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Shops/Index')
                ->has('shops', 2)
                ->where('shops.0.id', $a->id)
                ->where('shops.1.id', $b->id));
    }

    public function test_店舗ページには公開中の商品だけが並び順どおりに表示される(): void
    {
        $shop = $this->makeShop();
        $shop->locales()->create(['locale' => 'ja']);
        $second = $this->makeProduct($shop, ['sort_order' => 2]);
        $first = $this->makeProduct($shop, ['sort_order' => 1]);
        $this->makeProduct($shop, ['is_active' => false, 'sort_order' => 0]);

        $this->get(route('shops.show', $shop))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Shops/Show')
                ->where('shop.id', $shop->id)
                ->has('shop.locales', 1)
                ->has('products.data', 2)
                ->where('products.data.0.id', $first->id)
                ->where('products.data.1.id', $second->id));
    }

    public function test_店舗ページで商品名を検索できる(): void
    {
        $shop = $this->makeShop();
        $apple = $this->makeProduct($shop, ['name' => '青森りんご']);
        $this->makeProduct($shop, ['name' => 'バナナ']);

        $this->get(route('shops.show', [$shop, 'name' => 'りんご']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('products.data', 1)
                ->where('products.data.0.id', $apple->id)
                ->where('filters.name', 'りんご'));
    }

    public function test_店舗ページに注文完了などのお知らせが表示される(): void
    {
        $shop = $this->makeShop();

        $this->withSession(['status' => 'ご注文ありがとうございます。（注文番号：1）'])
            ->get(route('shops.show', $shop))
            ->assertInertia(fn (Assert $page) => $page
                ->where('status', 'ご注文ありがとうございます。（注文番号：1）'));
    }

    public function test_非公開の店舗ページは404になる(): void
    {
        $shop = $this->makeShop(['is_active' => false]);

        $this->get(route('shops.show', $shop))->assertNotFound();
    }

    public function test_フッターの静的ページが表示される(): void
    {
        foreach (['how-to-use', 'privacy', 'company'] as $slug) {
            $this->get(route('pages.show', $slug))
                ->assertOk()
                ->assertInertia(fn (Assert $page) => $page
                    ->component('StaticPages/Show')
                    ->where('slug', $slug));
        }
    }

    public function test_存在しない静的ページは404になる(): void
    {
        $this->get('/pages/unknown')->assertNotFound();
    }

    public function test_表示言語を切り替えるとクッキーに保存される(): void
    {
        $this->from('/')
            ->post(route('locale.update'), ['locale' => 'en'])
            ->assertRedirect('/')
            ->assertCookie('locale', 'en');
    }

    public function test_未対応の言語には切り替えられない(): void
    {
        $this->post(route('locale.update'), ['locale' => 'fr'])
            ->assertSessionHasErrors('locale')
            ->assertCookieMissing('locale');
    }
}
