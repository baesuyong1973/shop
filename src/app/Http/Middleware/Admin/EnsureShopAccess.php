<?php

namespace App\Http\Middleware\Admin;

use App\Models\Shop;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureShopAccess
{
    /**
     * Ensure the authenticated admin may access the shop bound to the current route.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $shop = $request->route('shop');

        abort_unless($shop instanceof Shop, 404);

        abort_unless(Auth::guard('admin')->user()->canAccessShop($shop), 403);

        return $next($request);
    }
}
