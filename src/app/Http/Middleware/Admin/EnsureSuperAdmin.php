<?php

namespace App\Http\Middleware\Admin;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    /**
     * Ensure the authenticated admin is a super admin.
     */
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(Auth::guard('admin')->user()->isSuperAdmin(), 403);

        return $next($request);
    }
}
