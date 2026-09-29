<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminAccountTest extends TestCase
{
    use CreatesAdminTestData;
    use RefreshDatabase;

    public function test_管理者の一覧_詳細_登録_編集画面が表示される(): void
    {
        $super = $this->makeSuperAdmin();
        $shopAdmin = $this->makeShopAdmin($this->makeShop());

        $this->actingAs($super, 'admin')
            ->get(route('admin.admins.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Admins/Index')
                ->has('admins.data', 2));

        $this->actingAs($super, 'admin')
            ->get(route('admin.admins.show', $shopAdmin))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Admins/Show')
                ->where('admin.id', $shopAdmin->id)
                ->where('admin.shop.slug', 'test-shop'));

        $this->actingAs($super, 'admin')
            ->get(route('admin.admins.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Admins/Create')
                ->has('shops', 1));

        $this->actingAs($super, 'admin')
            ->get(route('admin.admins.edit', $shopAdmin))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Admins/Edit')
                ->where('admin.id', $shopAdmin->id));
    }

    public function test_店舗管理者を登録できる(): void
    {
        $super = $this->makeSuperAdmin();
        $shop = $this->makeShop();

        $this->actingAs($super, 'admin')
            ->post(route('admin.admins.store'), [
                'name' => '店長',
                'email' => 'manager@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => Admin::ROLE_SHOP_ADMIN,
                'shop_id' => $shop->id,
            ])
            ->assertRedirect(route('admin.admins.index'));

        $admin = Admin::where('email', 'manager@example.com')->firstOrFail();
        $this->assertSame(Admin::ROLE_SHOP_ADMIN, $admin->role);
        $this->assertSame($shop->id, $admin->shop_id);
        $this->assertTrue(Hash::check('password123', $admin->password));
    }

    public function test_スーパー管理者を登録すると店舗は紐付かない(): void
    {
        $super = $this->makeSuperAdmin();
        $shop = $this->makeShop();

        $this->actingAs($super, 'admin')
            ->post(route('admin.admins.store'), [
                'name' => '本部',
                'email' => 'hq@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => Admin::ROLE_SUPER_ADMIN,
                'shop_id' => $shop->id,
            ])
            ->assertRedirect(route('admin.admins.index'));

        $this->assertNull(Admin::where('email', 'hq@example.com')->firstOrFail()->shop_id);
    }

    public function test_管理者登録時の入力チェック(): void
    {
        $super = $this->makeSuperAdmin();

        $this->actingAs($super, 'admin')
            ->post(route('admin.admins.store'), [])
            ->assertSessionHasErrors(['name', 'email', 'password', 'role']);

        $this->actingAs($super, 'admin')
            ->post(route('admin.admins.store'), [
                'name' => '店長',
                'email' => 'super@example.com',
                'password' => 'password123',
                'password_confirmation' => 'different',
                'role' => Admin::ROLE_SHOP_ADMIN,
            ])
            ->assertSessionHasErrors(['email', 'password', 'shop_id']);

        $this->assertSame(1, Admin::count());
    }

    public function test_パスワード未入力で更新するとパスワードは変わらない(): void
    {
        $super = $this->makeSuperAdmin();
        $shopAdmin = $this->makeShopAdmin($this->makeShop());

        $this->actingAs($super, 'admin')
            ->put(route('admin.admins.update', $shopAdmin), [
                'name' => '名前変更',
                'email' => $shopAdmin->email,
                'password' => '',
                'role' => Admin::ROLE_SHOP_ADMIN,
                'shop_id' => $shopAdmin->shop_id,
            ])
            ->assertRedirect(route('admin.admins.index'));

        $shopAdmin->refresh();
        $this->assertSame('名前変更', $shopAdmin->name);
        $this->assertTrue(Hash::check('password', $shopAdmin->password));
    }

    public function test_パスワードと役割を更新できる(): void
    {
        $super = $this->makeSuperAdmin();
        $shopAdmin = $this->makeShopAdmin($this->makeShop());

        $this->actingAs($super, 'admin')
            ->put(route('admin.admins.update', $shopAdmin), [
                'name' => $shopAdmin->name,
                'email' => $shopAdmin->email,
                'password' => 'newpassword1',
                'password_confirmation' => 'newpassword1',
                'role' => Admin::ROLE_SUPER_ADMIN,
            ])
            ->assertRedirect(route('admin.admins.index'));

        $shopAdmin->refresh();
        $this->assertSame(Admin::ROLE_SUPER_ADMIN, $shopAdmin->role);
        $this->assertNull($shopAdmin->shop_id);
        $this->assertTrue(Hash::check('newpassword1', $shopAdmin->password));
    }

    public function test_自分と同じメールアドレスのままなら更新できる(): void
    {
        $super = $this->makeSuperAdmin();

        $this->actingAs($super, 'admin')
            ->put(route('admin.admins.update', $super), [
                'name' => '名前だけ変更',
                'email' => $super->email,
                'role' => Admin::ROLE_SUPER_ADMIN,
            ])
            ->assertSessionHasNoErrors();
    }

    public function test_ほかの管理者を削除できる(): void
    {
        $super = $this->makeSuperAdmin();
        $shopAdmin = $this->makeShopAdmin($this->makeShop());

        $this->actingAs($super, 'admin')
            ->delete(route('admin.admins.destroy', $shopAdmin))
            ->assertRedirect(route('admin.admins.index'));

        $this->assertModelMissing($shopAdmin);
    }

    public function test_自分自身は削除できない(): void
    {
        $super = $this->makeSuperAdmin();
        $this->makeShopAdmin($this->makeShop());

        $this->actingAs($super, 'admin')
            ->delete(route('admin.admins.destroy', $super))
            ->assertSessionHas('error', '自分自身のアカウントは削除できません。');

        $this->assertModelExists($super);
    }

    public function test_最後の管理者は削除できない(): void
    {
        $super = $this->makeSuperAdmin();
        $other = $this->makeSuperAdmin('other@example.com');
        $other->delete();

        // Simulate a session belonging to an admin that no longer exists, so
        // the "self" check doesn't apply and only the last-admin guard remains.
        $this->actingAs($other, 'admin')
            ->delete(route('admin.admins.destroy', $super))
            ->assertSessionHas('error', '最後の管理者アカウントは削除できません。');

        $this->assertModelExists($super);
    }

    public function test_スーパー管理者は店舗管理者として代理ログインし元に戻れる(): void
    {
        $super = $this->makeSuperAdmin();
        $shopAdmin = $this->makeShopAdmin($this->makeShop());

        $this->actingAs($super, 'admin')
            ->post(route('admin.admins.impersonate', $shopAdmin))
            ->assertRedirect(route('admin.dashboard'))
            ->assertSessionHas('impersonator_admin_id', $super->id);
        $this->assertAuthenticatedAs($shopAdmin, 'admin');

        $this->post(route('admin.stop-impersonating'))
            ->assertRedirect(route('admin.dashboard'))
            ->assertSessionMissing('impersonator_admin_id');
        $this->assertAuthenticatedAs($super, 'admin');
    }

    public function test_スーパー管理者への代理ログインはできない(): void
    {
        $super = $this->makeSuperAdmin();
        $otherSuper = $this->makeSuperAdmin('other@example.com');

        $this->actingAs($super, 'admin')
            ->post(route('admin.admins.impersonate', $otherSuper))
            ->assertForbidden();

        $this->assertAuthenticatedAs($super, 'admin');
    }

    public function test_代理ログイン中でなければ元に戻る操作はできない(): void
    {
        $shopAdmin = $this->makeShopAdmin($this->makeShop());

        $this->actingAs($shopAdmin, 'admin')
            ->post(route('admin.stop-impersonating'))
            ->assertForbidden();
    }

    public function test_店舗管理者は管理者アカウント管理を使えない(): void
    {
        $super = $this->makeSuperAdmin();
        $shopAdmin = $this->makeShopAdmin($this->makeShop());

        $this->actingAs($shopAdmin, 'admin')->get(route('admin.admins.index'))->assertForbidden();
        $this->actingAs($shopAdmin, 'admin')->delete(route('admin.admins.destroy', $super))->assertForbidden();
        $this->actingAs($shopAdmin, 'admin')->post(route('admin.admins.impersonate', $shopAdmin))->assertForbidden();

        $this->assertModelExists($super);
    }
}
