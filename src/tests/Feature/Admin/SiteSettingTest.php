<?php

namespace Tests\Feature\Admin;

use App\Models\SiteLocale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SiteSettingTest extends TestCase
{
    use CreatesAdminTestData;
    use RefreshDatabase;

    public function test_言語設定画面が表示される(): void
    {
        $admin = $this->makeSuperAdmin();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.settings.locales.edit'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Settings/Locales')
                ->has('supportedLocales', 12)
                ->has('locales'));
    }

    public function test_サイト全体の対応言語を更新できる(): void
    {
        $admin = $this->makeSuperAdmin();

        $this->actingAs($admin, 'admin')
            ->from(route('admin.settings.locales.edit'))
            ->put(route('admin.settings.locales.update'), ['locales' => ['ja', 'vi']])
            ->assertRedirect(route('admin.settings.locales.edit'))
            ->assertSessionHas('status');

        $this->assertEqualsCanonicalizing(['ja', 'vi'], SiteLocale::pluck('locale')->all());
    }

    public function test_対応言語を0件にはできない(): void
    {
        $admin = $this->makeSuperAdmin();
        $before = SiteLocale::pluck('locale')->all();

        $this->actingAs($admin, 'admin')
            ->put(route('admin.settings.locales.update'), ['locales' => []])
            ->assertSessionHasErrors('locales');

        $this->assertEqualsCanonicalizing($before, SiteLocale::pluck('locale')->all());
    }

    public function test_未対応の言語は登録できない(): void
    {
        $admin = $this->makeSuperAdmin();

        $this->actingAs($admin, 'admin')
            ->put(route('admin.settings.locales.update'), ['locales' => ['ja', 'fr']])
            ->assertSessionHasErrors('locales.1');
    }

    public function test_店舗管理者は言語設定を使えない(): void
    {
        $admin = $this->makeShopAdmin($this->makeShop());

        $this->actingAs($admin, 'admin')->get(route('admin.settings.locales.edit'))->assertForbidden();
        $this->actingAs($admin, 'admin')
            ->put(route('admin.settings.locales.update'), ['locales' => ['ja']])
            ->assertForbidden();
    }
}
