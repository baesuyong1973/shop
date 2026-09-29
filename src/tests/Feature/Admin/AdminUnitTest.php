<?php

namespace Tests\Feature\Admin;

use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminUnitTest extends TestCase
{
    use CreatesAdminTestData;
    use RefreshDatabase;

    public function test_単位の一覧に使用中の商品数が表示される(): void
    {
        $admin = $this->makeSuperAdmin();
        $piece = Unit::create(['name' => '個']);
        Unit::create(['name' => '箱']);
        $shop = $this->makeShop();
        $this->makeProduct($shop, ['unit_id' => $piece->id]);
        $this->makeProduct($shop, ['unit_id' => $piece->id]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.units.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Units/Index')
                ->has('units', 2)
                ->where('units.0.name', '個')
                ->where('units.0.products_count', 2)
                ->where('units.1.name', '箱')
                ->where('units.1.products_count', 0));
    }

    public function test_単位の追加画面と編集画面が表示される(): void
    {
        $admin = $this->makeSuperAdmin();
        $unit = Unit::create(['name' => '個']);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.units.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Units/Create'));

        $this->actingAs($admin, 'admin')
            ->get(route('admin.units.edit', $unit))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Units/Edit')
                ->where('unit.name', '個')
                ->where('unit.products_count', 0));
    }

    public function test_単位を追加すると商品の登録画面で選べるようになる(): void
    {
        $admin = $this->makeSuperAdmin();
        $shop = $this->makeShop();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.units.store'), ['name' => '袋'])
            ->assertRedirect(route('admin.units.index'))
            ->assertSessionHas('status', '「袋」を登録しました。');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.shop.products.create', $shop))
            ->assertInertia(fn (Assert $page) => $page->where('units.0.name', '袋'));
    }

    public function test_単位名を変更すると商品の単位表示も変わる(): void
    {
        $admin = $this->makeSuperAdmin();
        $unit = Unit::create(['name' => 'こ']);
        $product = $this->makeProduct($this->makeShop(), ['unit_id' => $unit->id]);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.units.update', $unit), ['name' => '個'])
            ->assertRedirect(route('admin.units.index'))
            ->assertSessionHas('status', '「個」を更新しました。');

        $this->assertSame('個', $product->fresh()->unit->name);
    }

    public function test_単位名は必須で重複できない(): void
    {
        $admin = $this->makeSuperAdmin();
        Unit::create(['name' => '個']);
        $box = Unit::create(['name' => '箱']);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.units.store'), ['name' => ''])
            ->assertSessionHasErrors('name');

        $this->actingAs($admin, 'admin')
            ->post(route('admin.units.store'), ['name' => '個'])
            ->assertSessionHasErrors('name');

        $this->actingAs($admin, 'admin')
            ->post(route('admin.units.store'), ['name' => str_repeat('あ', 51)])
            ->assertSessionHasErrors('name');

        $this->actingAs($admin, 'admin')
            ->put(route('admin.units.update', $box), ['name' => '個'])
            ->assertSessionHasErrors('name');

        $this->assertSame(2, Unit::count());
        $this->assertSame('箱', $box->fresh()->name);
    }

    public function test_自分と同じ単位名のままなら更新できる(): void
    {
        $admin = $this->makeSuperAdmin();
        $unit = Unit::create(['name' => '個']);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.units.update', $unit), ['name' => '個'])
            ->assertSessionHasNoErrors();
    }

    public function test_使われていない単位は削除できる(): void
    {
        $admin = $this->makeSuperAdmin();
        $unit = Unit::create(['name' => '袋']);

        $this->actingAs($admin, 'admin')
            ->delete(route('admin.units.destroy', $unit))
            ->assertRedirect(route('admin.units.index'))
            ->assertSessionHas('status', '「袋」を削除しました。');

        $this->assertModelMissing($unit);
    }

    public function test_商品で使用中の単位は削除できない(): void
    {
        $admin = $this->makeSuperAdmin();
        $unit = Unit::create(['name' => '個']);
        $product = $this->makeProduct($this->makeShop(), ['unit_id' => $unit->id]);

        $this->actingAs($admin, 'admin')
            ->from(route('admin.units.index'))
            ->delete(route('admin.units.destroy', $unit))
            ->assertRedirect(route('admin.units.index'))
            ->assertSessionHas('error', '「個」は商品で使用中のため削除できません。');

        $this->assertModelExists($unit);
        $this->assertSame($unit->id, $product->fresh()->unit_id);
    }

    public function test_店舗管理者は単位を管理できない(): void
    {
        $admin = $this->makeShopAdmin($this->makeShop());
        $unit = Unit::create(['name' => '個']);

        $this->actingAs($admin, 'admin')->get(route('admin.units.index'))->assertForbidden();
        $this->actingAs($admin, 'admin')->post(route('admin.units.store'), ['name' => '袋'])->assertForbidden();
        $this->actingAs($admin, 'admin')->put(route('admin.units.update', $unit), ['name' => '箱'])->assertForbidden();
        $this->actingAs($admin, 'admin')->delete(route('admin.units.destroy', $unit))->assertForbidden();

        $this->assertSame(['個'], Unit::pluck('name')->all());
    }

    public function test_未ログインでは単位管理に入れない(): void
    {
        $this->get(route('admin.units.index'))->assertRedirect(route('admin.login'));
    }

    public function test_共通の単位管理には店舗独自の単位は表示されず編集もできない(): void
    {
        $admin = $this->makeSuperAdmin();
        Unit::create(['name' => '個']);
        $shopUnit = $this->makeShop()->units()->create(['name' => '束']);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.units.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->has('units', 1)
                ->where('units.0.name', '個'));

        $this->actingAs($admin, 'admin')->get(route('admin.units.edit', $shopUnit))->assertNotFound();
        $this->actingAs($admin, 'admin')->put(route('admin.units.update', $shopUnit), ['name' => 'たば'])->assertNotFound();
        $this->actingAs($admin, 'admin')->delete(route('admin.units.destroy', $shopUnit))->assertNotFound();

        $this->assertSame('束', $shopUnit->fresh()->name);
    }

    public function test_店舗独自の単位と同じ名前の共通単位は登録できない(): void
    {
        $admin = $this->makeSuperAdmin();
        $this->makeShop()->units()->create(['name' => '束']);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.units.store'), ['name' => '束'])
            ->assertSessionHasErrors('name');
    }

    public function test_店舗管理者の単位管理には共通の単位と自店舗の単位だけが表示される(): void
    {
        $shop = $this->makeShop();
        $admin = $this->makeShopAdmin($shop);
        Unit::create(['name' => '個']);
        $own = $shop->units()->create(['name' => '束']);
        $this->makeShop('other-shop')->units()->create(['name' => '房']);
        $this->makeProduct($shop, ['unit_id' => $own->id]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.shop.units.index', $shop))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Units/Index')
                ->where('shop.id', $shop->id)
                ->has('sharedUnits', 1)
                ->where('sharedUnits.0.name', '個')
                ->has('units', 1)
                ->where('units.0.name', '束')
                ->where('units.0.products_count', 1));
    }

    public function test_店舗管理者は自店舗の単位を追加_編集_削除できる(): void
    {
        $shop = $this->makeShop();
        $admin = $this->makeShopAdmin($shop);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.shop.units.create', $shop))
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Units/Create')->where('shop.id', $shop->id));

        $this->actingAs($admin, 'admin')
            ->post(route('admin.shop.units.store', $shop), ['name' => '束'])
            ->assertRedirect(route('admin.shop.units.index', $shop))
            ->assertSessionHas('status', '「束」を登録しました。');

        $unit = Unit::where('name', '束')->firstOrFail();
        $this->assertTrue($unit->shop->is($shop));

        $this->actingAs($admin, 'admin')
            ->get(route('admin.shop.units.edit', [$shop, $unit]))
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Units/Edit')->where('unit.name', '束'));

        $this->actingAs($admin, 'admin')
            ->put(route('admin.shop.units.update', [$shop, $unit]), ['name' => 'たば'])
            ->assertRedirect(route('admin.shop.units.index', $shop));
        $this->assertSame('たば', $unit->fresh()->name);

        $this->actingAs($admin, 'admin')
            ->delete(route('admin.shop.units.destroy', [$shop, $unit]))
            ->assertRedirect(route('admin.shop.units.index', $shop));
        $this->assertModelMissing($unit);
    }

    public function test_店舗独自の単位は共通の単位や自店舗の単位と同じ名前にできない(): void
    {
        $shop = $this->makeShop();
        $admin = $this->makeShopAdmin($shop);
        Unit::create(['name' => '個']);
        $shop->units()->create(['name' => '束']);

        foreach (['個', '束', ''] as $name) {
            $this->actingAs($admin, 'admin')
                ->post(route('admin.shop.units.store', $shop), ['name' => $name])
                ->assertSessionHasErrors('name');
        }

        $this->actingAs($admin, 'admin')
            ->post(route('admin.shop.units.store', $shop), ['name' => '個'])
            ->assertSessionHasErrors(['name' => '単位名はすでに使用されています。']);

        $this->assertSame(1, $shop->units()->count());
    }

    public function test_ほかの店舗と同じ名前の独自単位は登録できる(): void
    {
        $shop = $this->makeShop();
        $admin = $this->makeShopAdmin($shop);
        $this->makeShop('other-shop')->units()->create(['name' => '束']);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.shop.units.store', $shop), ['name' => '束'])
            ->assertSessionHasNoErrors();

        $this->assertSame(['束'], $shop->units()->pluck('name')->all());
    }

    public function test_商品で使用中の店舗独自の単位は削除できない(): void
    {
        $shop = $this->makeShop();
        $admin = $this->makeShopAdmin($shop);
        $unit = $shop->units()->create(['name' => '束']);
        $this->makeProduct($shop, ['unit_id' => $unit->id]);

        $this->actingAs($admin, 'admin')
            ->delete(route('admin.shop.units.destroy', [$shop, $unit]))
            ->assertSessionHas('error', '「束」は商品で使用中のため削除できません。');

        $this->assertModelExists($unit);
    }

    public function test_店舗管理者は共通の単位やほかの店舗の単位を変更できない(): void
    {
        $shop = $this->makeShop();
        $admin = $this->makeShopAdmin($shop);
        $shared = Unit::create(['name' => '個']);
        $otherShop = $this->makeShop('other-shop');
        $othersUnit = $otherShop->units()->create(['name' => '房']);

        foreach ([$shared, $othersUnit] as $unit) {
            $this->actingAs($admin, 'admin')->get(route('admin.shop.units.edit', [$shop, $unit]))->assertNotFound();
            $this->actingAs($admin, 'admin')->put(route('admin.shop.units.update', [$shop, $unit]), ['name' => '変更'])->assertNotFound();
            $this->actingAs($admin, 'admin')->delete(route('admin.shop.units.destroy', [$shop, $unit]))->assertNotFound();
        }
        $this->actingAs($admin, 'admin')->get(route('admin.shop.units.index', $otherShop))->assertForbidden();

        $this->assertSame('個', $shared->fresh()->name);
        $this->assertSame('房', $othersUnit->fresh()->name);
    }

    public function test_商品の登録画面では共通の単位と自店舗の単位だけを選べる(): void
    {
        $shop = $this->makeShop();
        $admin = $this->makeShopAdmin($shop);
        $own = $shop->units()->create(['name' => '束']);
        Unit::create(['name' => '個']);
        $this->makeShop('other-shop')->units()->create(['name' => '房']);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.shop.products.create', $shop))
            ->assertInertia(fn (Assert $page) => $page
                ->has('units', 2)
                ->where('units.0.name', '個')
                ->where('units.1.name', '束'));

        $this->actingAs($admin, 'admin')
            ->get(route('admin.shop.products.edit', [$shop, $this->makeProduct($shop, ['unit_id' => $own->id])]))
            ->assertInertia(fn (Assert $page) => $page->has('units', 2));
    }

    public function test_ほかの店舗の単位を指定して商品を登録することはできない(): void
    {
        $shop = $this->makeShop();
        $admin = $this->makeShopAdmin($shop);
        $othersUnit = $this->makeShop('other-shop')->units()->create(['name' => '房']);
        $product = $this->makeProduct($shop);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.shop.products.update', [$shop, $product]), [
                'name' => $product->name,
                'price' => 100,
                'stock' => 1,
                'unit_id' => $othersUnit->id,
            ])
            ->assertSessionHasErrors('unit_id');

        $this->assertNull($product->fresh()->unit_id);
    }

    public function test_店舗独自の単位の商品をほかの店舗にコピーするとコピー先の同名の単位になる(): void
    {
        $admin = $this->makeSuperAdmin();
        $from = $this->makeShop('from-shop');
        $to = $this->makeShop('to-shop');
        $bunch = $from->units()->create(['name' => '束']);
        $product = $this->makeProduct($from, ['unit_id' => $bunch->id]);

        $this->actingAs($admin, 'admin')->post(route('admin.products.copy', $product), ['shop_id' => $to->id]);
        $this->actingAs($admin, 'admin')->post(route('admin.products.copy', $product), ['shop_id' => $to->id]);

        $copies = $to->products()->with('unit')->get();
        $this->assertCount(2, $copies);
        $this->assertSame(['束', '束'], $copies->pluck('unit.name')->all());
        $this->assertSame([$to->id, $to->id], $copies->pluck('unit.shop_id')->all());
        $this->assertSame(1, $to->units()->count());
    }

    public function test_共通の単位の商品や同じ店舗へのコピーでは単位はそのまま(): void
    {
        $admin = $this->makeSuperAdmin();
        $shop = $this->makeShop('from-shop');
        $other = $this->makeShop('to-shop');
        $shared = Unit::create(['name' => '個']);
        $own = $shop->units()->create(['name' => '束']);
        $sharedProduct = $this->makeProduct($shop, ['unit_id' => $shared->id]);
        $ownProduct = $this->makeProduct($shop, ['unit_id' => $own->id]);
        $noUnitProduct = $this->makeProduct($shop);

        $this->actingAs($admin, 'admin')->post(route('admin.products.copy', $sharedProduct), ['shop_id' => $other->id]);
        $this->actingAs($admin, 'admin')->post(route('admin.products.copy', $ownProduct), ['shop_id' => $shop->id]);
        $this->actingAs($admin, 'admin')->post(route('admin.products.copy', $noUnitProduct), ['shop_id' => $other->id]);

        $this->assertSame([$shared->id, null], $other->products()->orderBy('id')->pluck('unit_id')->all());
        $this->assertSame(2, $shop->products()->where('unit_id', $own->id)->count());
        $this->assertSame(0, $other->units()->count());
    }

    public function test_コピー先に同名の独自単位があればそれを使う(): void
    {
        $admin = $this->makeSuperAdmin();
        $from = $this->makeShop('from-shop');
        $to = $this->makeShop('to-shop');
        $own = $from->units()->create(['name' => 'パック']);
        $product = $this->makeProduct($from, ['unit_id' => $own->id]);
        $to->units()->create(['name' => 'パック']);

        $this->actingAs($admin, 'admin')->post(route('admin.products.copy', $product), ['shop_id' => $to->id]);

        $copy = $to->products()->firstOrFail();
        $this->assertSame($to->units()->value('id'), $copy->unit_id);
        $this->assertSame(1, $to->units()->count());
    }
}
