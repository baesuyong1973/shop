<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Middleware\SetLocale;
use App\Models\SiteLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SiteSettingController extends Controller
{
    /**
     * Global settings: languages offered on shop-independent pages
     * (login, registration, password reset, etc.).
     */
    public function editLocales(): Response
    {
        return Inertia::render('Admin/Settings/Locales', [
            'supportedLocales' => SetLocale::SUPPORTED_LOCALES,
            'locales' => SiteLocale::pluck('locale'),
            'status' => session('status'),
        ]);
    }

    public function updateLocales(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'locales' => ['required', 'array', 'min:1'],
            'locales.*' => [Rule::in(SetLocale::SUPPORTED_LOCALES)],
        ]);

        SiteLocale::query()->delete();
        SiteLocale::insert(array_map(fn (string $locale) => ['locale' => $locale], $data['locales']));

        return back()->with('status', '言語設定を更新しました。');
    }
}
