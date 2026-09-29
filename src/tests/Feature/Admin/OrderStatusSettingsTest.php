<?php

namespace Tests\Feature\Admin;

use App\Models\OrderStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class OrderStatusSettingsTest extends TestCase
{
    use CreatesAdminTestData;
    use RefreshDatabase;

    public function test_ステータスの一覧_登録_編集画面が表示される(): void
    {
        $admin = $this->makeSuperAdmin();
        $placed = OrderStatus::where('key', 'placed')->firstOrFail();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.order-statuses.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/OrderStatuses/Index')
                ->has('statuses', 3));

        $this->actingAs($admin, 'admin')
            ->get(route('admin.order-statuses.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/OrderStatuses/Create')
                ->has('statuses', 3));

        $this->actingAs($admin, 'admin')
            ->get(route('admin.order-statuses.edit', $placed))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/OrderStatuses/Edit')
                ->where('orderStatus.key', 'placed')
                ->has('orderStatus.next_statuses', 2));
    }

    public function test_初期ステータスとして登録すると既存の初期ステータスは解除される(): void
    {
        $admin = $this->makeSuperAdmin();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.order-statuses.store'), [
                'key' => 'reserved',
                'label' => '予約',
                'sort_order' => 0,
                'is_initial' => true,
            ])
            ->assertRedirect(route('admin.order-statuses.index'));

        $this->assertTrue(OrderStatus::where('key', 'reserved')->firstOrFail()->is_initial);
        $this->assertFalse(OrderStatus::where('key', 'placed')->firstOrFail()->is_initial);
        $this->assertSame(1, OrderStatus::where('is_initial', true)->count());
    }

    public function test_ステータス登録時の入力チェック(): void
    {
        $admin = $this->makeSuperAdmin();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.order-statuses.store'), [])
            ->assertSessionHasErrors(['key', 'label', 'sort_order']);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.order-statuses.store'), [
                'key' => 'placed',
                'label' => '重複',
                'sort_order' => 0,
            ])
            ->assertSessionHasErrors('key');

        $this->actingAs($admin, 'admin')
            ->post(route('admin.order-statuses.store'), [
                'key' => 'Invalid-Key',
                'label' => '不正',
                'sort_order' => -1,
                'next_status_ids' => [999999],
            ])
            ->assertSessionHasErrors(['key', 'sort_order', 'next_status_ids.0']);

        $this->assertSame(3, OrderStatus::count());
    }

    public function test_自分自身を遷移先に設定することはできない(): void
    {
        $admin = $this->makeSuperAdmin();
        $placed = OrderStatus::where('key', 'placed')->firstOrFail();

        $this->actingAs($admin, 'admin')
            ->put(route('admin.order-statuses.update', $placed), [
                'label' => $placed->label,
                'sort_order' => $placed->sort_order,
                'is_initial' => true,
                'next_status_ids' => [$placed->id],
            ])
            ->assertSessionHasErrors('next_status_ids.0');
    }

    public function test_使われていないステータスは削除できる(): void
    {
        $admin = $this->makeSuperAdmin();
        $status = OrderStatus::create(['key' => 'unused', 'label' => '未使用', 'sort_order' => 9]);

        $this->actingAs($admin, 'admin')
            ->delete(route('admin.order-statuses.destroy', $status))
            ->assertRedirect(route('admin.order-statuses.index'))
            ->assertSessionHas('status', '「未使用」を削除しました。');

        $this->assertModelMissing($status);
    }

    public function test_最後のステータスは削除できない(): void
    {
        $admin = $this->makeSuperAdmin();
        OrderStatus::whereNot('key', 'handed_over')->delete();
        $last = OrderStatus::where('key', 'handed_over')->firstOrFail();

        $this->actingAs($admin, 'admin')
            ->delete(route('admin.order-statuses.destroy', $last))
            ->assertSessionHas('error', '最後の状態は削除できません。');

        $this->assertModelExists($last);
    }
}
