<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        if ($user instanceof Admin) {
            $user->loadMissing('shop');
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
            ],
            'shop' => fn () => $request->route('shop'),
            'impersonating' => fn () => $request->session()->has('impersonator_admin_id'),
        ];
    }
}
