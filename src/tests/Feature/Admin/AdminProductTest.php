<?php

namespace Tests\Feature\Admin;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminProductTest extends TestCase
{
    use CreatesAdminTestData;
    use RefreshDatabase;

    public function test_店舗の商品一覧が並び順どおりに表示される(): void
    {
        $shop = $this->makeShop();
        $admin = $this->makeShopAdmin($shop);
        $second = $this->makeProduct($shop, ['name' => '2番目', 'sort_order' => 2]);
        $first = $this->makeProduct($shop, ['name' => '1番目', 'sort_order' => 1]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.shop.products.index', $shop))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Products/Index')
                ->where('products.0.id', $first->id)
                ->where('products.1.id', $second->id));
    }

    public function test_商品登録画面と編集画面が表示される(): void
    {
        $shop = $this->makeShop();
        $admin = $this->makeShopAdmin($shop);
        $product = $this->makeProduct($shop);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.shop.products.create', $shop))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Products/Create')
                ->has('countries')
                ->has('prefectures')
                ->has('units'));

        $this->actingAs($admin, 'admin')
            ->get(route('admin.shop.products.edit', [$shop, $product]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Products/Edit')
                ->where('product.id', $product->id));
    }

    public function test_商品を登録すると画像が縮小保存され並び順の最後に追加される(): void
    {
        Storage::fake('public');
        $shop = $this->makeShop();
        $admin = $this->makeShopAdmin($shop);
        $this->makeProduct($shop, ['sort_order' => 5]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.shop.products.store', $shop), [
                'name' => 'りんご',
                'image' => UploadedFile::fake()->image('apple.png', 2400, 1800),
                'price' => 150,
                'stock' => 20,
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.shop.products.index', $shop));

        $product = Product::where('name', 'りんご')->firstOrFail();
        $this->assertSame($shop->id, $product->shop_id);
        $this->assertSame(6, $product->sort_order);
        $this->assertStringEndsWith('.jpg', $product->image_path);

        $size = getimagesizefromstring(Storage::disk('public')->get($product->image_path));
        $this->assertSame([1200, 900], [$size[0], $size[1]]);
    }

    public function test_商品登録時は画像が必須(): void
    {
        $shop = $this->makeShop();
        $admin = $this->makeShopAdmin($shop);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.shop.products.store', $shop), [
                'name' => 'りんご',
                'price' => 150,
                'stock' => 20,
            ])
            ->assertSessionHasErrors('image');

        $this->assertSame(0, Product::count());
    }

    public function test_商品登録時の入力チェック(): void
    {
        $shop = $this->makeShop();
        $admin = $this->makeShopAdmin($shop);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.shop.products.store', $shop), [
                'price' => -1,
                'stock' => 'abc',
                'image' => UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf'),
                'unit_quantity' => 0,
            ])
            ->assertSessionHasErrors(['name', 'price', 'stock', 'image', 'unit_quantity']);
    }

    public function test_画像を変えずに商品情報を更新できる(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/test.jpg', 'image');
        $shop = $this->makeShop();
        $admin = $this->makeShopAdmin($shop);
        $product = $this->makeProduct($shop);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.shop.products.update', [$shop, $product]), [
                'name' => '名前変更',
                'price' => 500,
                'stock' => 3,
                'is_active' => false,
            ])
            ->assertRedirect(route('admin.shop.products.index', $shop));

        $product->refresh();
        $this->assertSame('名前変更', $product->name);
        $this->assertSame(500, $product->price);
        $this->assertFalse($product->is_active);
        $this->assertSame('products/test.jpg', $product->image_path);
        Storage::disk('public')->assertExists('products/test.jpg');
    }

    public function test_商品を削除すると画像も削除される(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/test.jpg', 'image');
        $shop = $this->makeShop();
        $admin = $this->makeShopAdmin($shop);
        $product = $this->makeProduct($shop);

        $this->actingAs($admin, 'admin')
            ->delete(route('admin.shop.products.destroy', [$shop, $product]))
            ->assertRedirect(route('admin.shop.products.index', $shop));

        $this->assertModelMissing($product);
        Storage::disk('public')->assertMissing('products/test.jpg');
    }

    public function test_商品の並び順を変更できる(): void
    {
        $shop = $this->makeShop();
        $admin = $this->makeShopAdmin($shop);
        $a = $this->makeProduct($shop, ['sort_order' => 0]);
        $b = $this->makeProduct($shop, ['sort_order' => 1]);
        $c = $this->makeProduct($shop, ['sort_order' => 2]);

        $this->actingAs($admin, 'admin')
            ->from(route('admin.shop.products.index', $shop))
            ->patch(route('admin.shop.products.reorder', $shop), ['order' => [$c->id, $a->id, $b->id]])
            ->assertRedirect(route('admin.shop.products.index', $shop));

        $this->assertSame(0, $c->fresh()->sort_order);
        $this->assertSame(1, $a->fresh()->sort_order);
        $this->assertSame(2, $b->fresh()->sort_order);
    }

    public function test_他店舗の商品を含む並び替えは拒否される(): void
    {
        $shop = $this->makeShop();
        $admin = $this->makeShopAdmin($shop);
        $own = $this->makeProduct($shop, ['sort_order' => 0]);
        $other = $this->makeProduct($this->makeShop('other-shop'), ['sort_order' => 7]);

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.shop.products.reorder', $shop), ['order' => [$other->id, $own->id]])
            ->assertStatus(422);

        $this->assertSame(7, $other->fresh()->sort_order);
        $this->assertSame(0, $own->fresh()->sort_order);
    }

    public function test_並び替えの入力チェック(): void
    {
        $shop = $this->makeShop();
        $admin = $this->makeShopAdmin($shop);
        $product = $this->makeProduct($shop);

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.shop.products.reorder', $shop), [])
            ->assertSessionHasErrors('order');

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.shop.products.reorder', $shop), ['order' => [$product->id, $product->id, 999999]])
            ->assertSessionHasErrors(['order.0', 'order.2']);
    }

    public function test_全店舗の商品一覧は新しい順にページ分割して表示される(): void
    {
        $admin = $this->makeSuperAdmin();
        $shopA = $this->makeShop('shop-a');
        $shopB = $this->makeShop('shop-b');
        $this->makeProduct($shopA);
        $this->makeProduct($shopB);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Products/Index')
                ->has('products.data', 2)
                ->has('shops', 2)
                ->where('filters.shop_id', null));
    }

    public function test_全店舗の商品一覧を店舗で絞り込むと並び順どおりの全件が表示される(): void
    {
        $admin = $this->makeSuperAdmin();
        $shopA = $this->makeShop('shop-a');
        $second = $this->makeProduct($shopA, ['sort_order' => 2]);
        $first = $this->makeProduct($shopA, ['sort_order' => 1]);
        $this->makeProduct($this->makeShop('shop-b'));

        $this->actingAs($admin, 'admin')
            ->get(route('admin.products.index', ['shop_id' => $shopA->id]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('products', 2)
                ->where('products.0.id', $first->id)
                ->where('products.1.id', $second->id)
                ->where('filters.shop_id', $shopA->id));
    }

    public function test_商品を別店舗にコピーすると非公開で登録される(): void
    {
        $admin = $this->makeSuperAdmin();
        $shopA = $this->makeShop('shop-a');
        $shopB = $this->makeShop('shop-b');
        $product = $this->makeProduct($shopA, ['name' => 'りんご']);

        $this->actingAs($admin, 'admin')
            ->from(route('admin.products.index'))
            ->post(route('admin.products.copy', $product), ['shop_id' => $shopB->id])
            ->assertRedirect(route('admin.products.index'))
            ->assertSessionHas('status');

        $copy = Product::where('shop_id', $shopB->id)->firstOrFail();
        $this->assertSame('りんご', $copy->name);
        $this->assertFalse($copy->is_active);
        $this->assertSame(now()->toDateString(), $copy->arrival_date->toDateString());
        $this->assertTrue($product->fresh()->is_active);
    }

    public function test_存在しない店舗へのコピーはエラーになる(): void
    {
        $admin = $this->makeSuperAdmin();
        $product = $this->makeProduct($this->makeShop());

        $this->actingAs($admin, 'admin')
            ->post(route('admin.products.copy', $product), ['shop_id' => 999999])
            ->assertSessionHasErrors('shop_id');

        $this->assertSame(1, Product::count());
    }
}
