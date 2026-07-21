<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Cross-shop customer listing for super admins.
     */
    public function index(): Response
    {
        $users = User::with('orders.shop:id,name')->latest()->paginate(20)->withQueryString();

        $users->getCollection()->each(function (User $user) {
            $user->shop_names = $user->orders->pluck('shop.name')->filter()->unique()->values();
            $user->unsetRelation('orders');
        });

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'status' => session('status'),
        ]);
    }

    public function show(User $user): Response
    {
        return Inertia::render('Admin/Users/Show', [
            'user' => $user,
            'orders' => $user->orders()->with('shop')->latest()->paginate(20)->withQueryString(),
        ]);
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()->route('admin.users.index')->with('status', 'ユーザーを削除しました。');
    }

    /**
     * Enable or disable a customer's account (blocks/allows login). Super admin only, global.
     */
    public function toggleActive(User $user): RedirectResponse
    {
        $user->is_active = ! $user->is_active;
        $user->save();

        return back()->with('status', $user->is_active ? 'ユーザーを有効化しました。' : 'ユーザーを無効化しました。');
    }

    /**
     * Customers who have ordered at this shop, for shop-scoped admins.
     */
    public function shopIndex(Shop $shop): Response
    {
        return Inertia::render('Admin/Users/Index', [
            'shop' => $shop,
            'users' => User::orderedAtShop($shop)->latest()->paginate(20)->withQueryString(),
            'status' => session('status'),
        ]);
    }

    /**
     * A customer's detail page scoped to this shop's orders.
     *
     * Not restricted to customers who've ordered here, since staff may look
     * a member up by their QR code before their first order at this shop.
     */
    public function shopShow(Shop $shop, User $user): Response
    {
        return Inertia::render('Admin/Users/Show', [
            'shop' => $shop,
            'user' => $user,
            'orders' => $user->orders()
                ->where('shop_id', $shop->id)
                ->with('items.product.unit')
                ->latest()
                ->paginate(20)
                ->withQueryString(),
        ]);
    }

    /**
     * Enable or disable a customer's account, restricted to customers who've
     * ordered at this shop so a shop admin can't act on an arbitrary user.
     */
    public function shopToggleActive(Shop $shop, User $user): RedirectResponse
    {
        abort_unless(User::orderedAtShop($shop)->whereKey($user->id)->exists(), 404);

        $user->is_active = ! $user->is_active;
        $user->save();

        return back()->with('status', $user->is_active ? 'ユーザーを有効化しました。' : 'ユーザーを無効化しました。');
    }
}
