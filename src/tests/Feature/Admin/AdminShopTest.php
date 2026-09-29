<?php

namespace Tests\Feature\Admin;

use App\Models\Shop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminShopTest extends TestCase
{
    use CreatesAdminTestData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_店舗一覧が名前順で表示される(): void
    {
        $admin = $this->makeSuperAdmin();
        $this->makeShop('b-shop');
        $this->makeShop('a-shop');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.shops.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Shops/Index')
                ->has('shops.data', 2)
                ->where('shops.data.0.slug', 'a-shop'));
    }

    public function test_店舗の登録画面と編集画面が表示される(): void
    {
        $admin = $this->makeSuperAdmin();
        $shop = $this->makeShop();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.shops.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Shops/Create')
                ->has('supportedLocales'));

        $this->actingAs($admin, 'admin')
            ->get(route('admin.shops.edit', $shop))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Shops/Edit')
                ->where('shop.id', $shop->id));
    }

    public function test_ロゴと対応言語を指定して店舗を登録できる(): void
    {
        $admin = $this->makeSuperAdmin();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.shops.store'), [
                'name' => '新しい店舗',
                'slug' => 'new-shop',
                'address' => '埼玉県戸田市',
                'phone' => '048-000-0000',
                'is_active' => true,
                'logo' => UploadedFile::fake()->image('logo.jpg', 1200, 800),
                'locales' => ['ja', 'en'],
            ])
            ->assertRedirect(route('admin.shops.index'));

        $shop = Shop::where('slug', 'new-shop')->firstOrFail();
        $this->assertSame('新しい店舗', $shop->name);
        $this->assertEqualsCanonicalizing(['ja', 'en'], $shop->available_locales);
        $this->assertStringEndsWith('.png', $shop->logo_path);
        Storage::disk('public')->assertExists($shop->logo_path);
    }

    public function test_対応言語を指定しない場合は日本語と中国語になる(): void
    {
        $admin = $this->makeSuperAdmin();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.shops.store'), ['name' => '店舗', 'slug' => 'shop'])
            ->assertRedirect(route('admin.shops.index'));

        $shop = Shop::where('slug', 'shop')->firstOrFail();
        $this->assertEqualsCanonicalizing(['ja', 'zh'], $shop->available_locales);
        $this->assertNull($shop->logo_path);
    }

    public function test_店舗登録時の入力チェック(): void
    {
        $admin = $this->makeSuperAdmin();
        $this->makeShop('taken');

        $this->actingAs($admin, 'admin')
            ->post(route('admin.shops.store'), [])
            ->assertSessionHasErrors(['name', 'slug']);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.shops.store'), [
                'name' => '店舗',
                'slug' => 'taken',
                'locales' => ['xx'],
            ])
            ->assertSessionHasErrors(['slug', 'locales.0']);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.shops.store'), ['name' => '店舗', 'slug' => 'スペース あり'])
            ->assertSessionHasErrors('slug');

        $this->assertSame(1, Shop::count());
    }

    public function test_店舗情報と対応言語を更新できる(): void
    {
        $admin = $this->makeSuperAdmin();
        $shop = $this->makeShop();
        $shop->locales()->createMany([['locale' => 'ja'], ['locale' => 'zh']]);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.shops.update', $shop), [
                'name' => '名前変更',
                'slug' => $shop->slug,
                'is_active' => false,
                'locales' => ['ko'],
            ])
            ->assertRedirect(route('admin.shops.index'));

        $shop->refresh();
        $this->assertSame('名前変更', $shop->name);
        $this->assertFalse($shop->is_active);
        $this->assertSame(['ko'], $shop->available_locales);
    }

    public function test_ロゴを差し替えると古いロゴは削除される(): void
    {
        $admin = $this->makeSuperAdmin();
        $shop = $this->makeShop();
        Storage::disk('public')->put('shops/old.png', 'old');
        $shop->update(['logo_path' => 'shops/old.png']);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.shops.update', $shop), [
                'name' => $shop->name,
                'slug' => $shop->slug,
                'logo' => UploadedFile::fake()->image('new.png'),
            ])
            ->assertRedirect(route('admin.shops.index'));

        $shop->refresh();
        $this->assertNotSame('shops/old.png', $shop->logo_path);
        Storage::disk('public')->assertMissing('shops/old.png');
        Storage::disk('public')->assertExists($shop->logo_path);
    }

    public function test_ロゴのない店舗にもロゴを追加できる(): void
    {
        $admin = $this->makeSuperAdmin();
        $shop = $this->makeShop();

        $this->actingAs($admin, 'admin')
            ->put(route('admin.shops.update', $shop), [
                'name' => $shop->name,
                'slug' => $shop->slug,
                'logo' => UploadedFile::fake()->image('new.png'),
            ])
            ->assertRedirect(route('admin.shops.index'));

        Storage::disk('public')->assertExists($shop->fresh()->logo_path);
    }

    public function test_紐付くデータのない店舗は削除でき_ロゴも削除される(): void
    {
        $admin = $this->makeSuperAdmin();
        $shop = $this->makeShop();
        Storage::disk('public')->put('shops/logo.png', 'logo');
        $shop->update(['logo_path' => 'shops/logo.png']);

        $this->actingAs($admin, 'admin')
            ->delete(route('admin.shops.destroy', $shop))
            ->assertRedirect(route('admin.shops.index'));

        $this->assertModelMissing($shop);
        Storage::disk('public')->assertMissing('shops/logo.png');
    }

    public function test_ロゴのない店舗も削除できる(): void
    {
        $admin = $this->makeSuperAdmin();
        $shop = $this->makeShop();

        $this->actingAs($admin, 'admin')
            ->delete(route('admin.shops.destroy', $shop))
            ->assertRedirect(route('admin.shops.index'));

        $this->assertModelMissing($shop);
    }

    public function test_商品のある店舗は削除できない(): void
    {
        $admin = $this->makeSuperAdmin();
        $shop = $this->makeShop();
        $this->makeProduct($shop);

        $this->actingAs($admin, 'admin')
            ->from(route('admin.shops.index'))
            ->delete(route('admin.shops.destroy', $shop))
            ->assertRedirect(route('admin.shops.index'))
            ->assertSessionHas('error');

        $this->assertModelExists($shop);
    }

    public function test_管理者のいる店舗は削除できない(): void
    {
        $admin = $this->makeSuperAdmin();
        $shop = $this->makeShop();
        $this->makeShopAdmin($shop);

        $this->actingAs($admin, 'admin')
            ->delete(route('admin.shops.destroy', $shop))
            ->assertSessionHas('error');

        $this->assertModelExists($shop);
    }

    public function test_店舗管理者は店舗管理を使えない(): void
    {
        $shop = $this->makeShop();
        $admin = $this->makeShopAdmin($shop);

        $this->actingAs($admin, 'admin')->get(route('admin.shops.index'))->assertForbidden();
        $this->actingAs($admin, 'admin')
            ->put(route('admin.shops.update', $shop), ['name' => '変更', 'slug' => $shop->slug])
            ->assertForbidden();

        $this->assertSame('Shop test-shop', $shop->fresh()->name);
    }
}
