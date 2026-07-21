<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ScanController extends Controller
{
    /**
     * Look up the user matching a scanned member QR token and redirect to their
     * shop-scoped detail page. The lookup itself is global (by unique token,
     * confirmed via physical presence), regardless of prior orders at this shop.
     */
    public function lookup(Request $request, Shop $shop): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
        ]);

        $user = User::where('qr_token', $request->string('token'))->first();

        if (! $user) {
            return redirect()->route('admin.dashboard')->with('error', '一致する会員が見つかりませんでした。');
        }

        return redirect()->route('admin.shop.users.show', [$shop, $user]);
    }
}
