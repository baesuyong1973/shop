<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminUserTest extends TestCase
{
    use CreatesAdminTestData;
    use RefreshDatabase;

    public function test_全ユーザー一覧に利用店舗名が表示される(): void
    {
        $admin = $this->makeSuperAdmin();
        $shopA = $this->makeShop('shop-a');
        $shopB = $this->makeShop('shop-b');
        $user = User::factory()->create();
        $this->makeOrder($shopA, $user);
        $this->makeOrder($shopA, $user);
        $this->makeOrder($shopB, $user);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Users/Index')
                ->has('users.data', 1)
                ->where('users.data.0.shop_names', ['Shop shop-a', 'Shop shop-b'])
                ->missing('users.data.0.orders'));
    }

    public function test_全ユーザー一覧は店舗で絞り込める(): void
    {
        $admin = $this->makeSuperAdmin();
        $shopA = $this->makeShop('shop-a');
        $shopB = $this->makeShop('shop-b');
        $customerA = User::factory()->create();
        $this->makeOrder($shopA, $customerA);
        $this->makeOrder($shopB);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.users.index', ['shop_id' => $shopA->id]))
            ->assertInertia(fn (Assert $page) => $page
                ->has('users.data', 1)
                ->where('users.data.0.id', $customerA->id)
                ->where('filters.shop_id', $shopA->id));
    }

    public function test_ユーザー詳細に全店舗の注文が表示される(): void
    {
        $admin = $this->makeSuperAdmin();
        $user = User::factory()->create();
        $this->makeOrder($this->makeShop('shop-a'), $user);
        $this->makeOrder($this->makeShop('shop-b'), $user);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.users.show', $user))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Users/Show')
                ->where('user.id', $user->id)
                ->has('orders.data', 2));
    }

    public function test_注文のないユーザーを削除できる(): void
    {
        $admin = $this->makeSuperAdmin();
        $user = User::factory()->create();

        $this->actingAs($admin, 'admin')
            ->delete(route('admin.users.destroy', $user))
            ->assertRedirect(route('admin.users.index'));

        $this->assertModelMissing($user);
    }

    public function test_注文履歴のあるユーザーは削除できずメッセージが表示される(): void
    {
        $admin = $this->makeSuperAdmin();
        $user = User::factory()->create();
        $order = $this->makeOrder($this->makeShop(), $user);

        $this->actingAs($admin, 'admin')
            ->from(route('admin.users.show', $user))
            ->delete(route('admin.users.destroy', $user))
            ->assertRedirect(route('admin.users.show', $user))
            ->assertSessionHas('error');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.users.show', $user))
            ->assertInertia(fn (Assert $page) => $page
                ->where('error', '注文履歴のあるユーザーは削除できません。無効化をご利用ください。'));

        $this->assertModelExists($user);
        $this->assertModelExists($order);
    }

    public function test_スーパー管理者はユーザーの有効と無効を切り替えられる(): void
    {
        $admin = $this->makeSuperAdmin();
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.users.toggle-active', $user))
            ->assertSessionHas('status', 'ユーザーを無効化しました。');
        $this->assertFalse($user->fresh()->is_active);

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.users.toggle-active', $user))
            ->assertSessionHas('status', 'ユーザーを有効化しました。');
        $this->assertTrue($user->fresh()->is_active);
    }

    public function test_店舗のユーザー一覧には自店舗で注文したユーザーだけが表示される(): void
    {
        $shop = $this->makeShop();
        $admin = $this->makeShopAdmin($shop);
        $customer = User::factory()->create();
        $this->makeOrder($shop, $customer);
        $this->makeOrder($this->makeShop('other-shop'));
        User::factory()->create();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.shop.users.index', $shop))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Users/Index')
                ->has('users.data', 1)
                ->where('users.data.0.id', $customer->id));
    }

    public function test_店舗のユーザー詳細には自店舗の注文だけが表示される(): void
    {
        $shop = $this->makeShop();
        $admin = $this->makeShopAdmin($shop);
        $user = User::factory()->create();
        $ownOrder = $this->makeOrder($shop, $user);
        $this->makeOrder($this->makeShop('other-shop'), $user);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.shop.users.show', [$shop, $user]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('user.id', $user->id)
                ->has('orders.data', 1)
                ->where('orders.data.0.id', $ownOrder->id));
    }

    public function test_店舗管理者は自店舗のユーザーの有効と無効を切り替えられる(): void
    {
        $shop = $this->makeShop();
        $admin = $this->makeShopAdmin($shop);
        $user = User::factory()->create(['is_active' => false]);
        $this->makeOrder($shop, $user);

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.shop.users.toggle-active', [$shop, $user]))
            ->assertSessionHas('status', 'ユーザーを有効化しました。');

        $this->assertTrue($user->fresh()->is_active);
    }

    public function test_店舗管理者は自店舗で注文していないユーザーを切り替えられない(): void
    {
        $shop = $this->makeShop();
        $admin = $this->makeShopAdmin($shop);
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.shop.users.toggle-active', [$shop, $user]))
            ->assertNotFound();

        $this->assertTrue($user->fresh()->is_active);
    }

    public function test_店舗管理者は全ユーザー管理を使えない(): void
    {
        $shop = $this->makeShop();
        $admin = $this->makeShopAdmin($shop);
        $user = User::factory()->create();

        $this->actingAs($admin, 'admin')->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($admin, 'admin')->get(route('admin.users.show', $user))->assertForbidden();
        $this->actingAs($admin, 'admin')->delete(route('admin.users.destroy', $user))->assertForbidden();

        $this->assertModelExists($user);
    }

    public function test_会員の二次元コードを読み取るとその会員の詳細画面に移動する(): void
    {
        $shop = $this->makeShop();
        $admin = $this->makeShopAdmin($shop);
        $user = User::factory()->create();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.shop.scan', [$shop, 'token' => $user->qr_token]))
            ->assertRedirect(route('admin.shop.users.show', [$shop, $user]));
    }

    public function test_一致する会員がいない二次元コードはエラーメッセージを表示する(): void
    {
        $shop = $this->makeShop();
        $admin = $this->makeShopAdmin($shop);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.shop.scan', [$shop, 'token' => 'unknown-token']))
            ->assertRedirect(route('admin.dashboard'))
            ->assertSessionHas('error', '一致する会員が見つかりませんでした。');
    }

    public function test_トークンなしの読み取りはエラーになる(): void
    {
        $shop = $this->makeShop();
        $admin = $this->makeShopAdmin($shop);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.shop.scan', $shop))
            ->assertSessionHasErrors('token');
    }
}
