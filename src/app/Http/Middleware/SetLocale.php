<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public const SUPPORTED_LOCALES = [
        'ja', 'en', 'zh', 'ko', 'th', 'my', 'vi', 'tl', 'pt', 'ne',
        'id', 'zh-TW', 'hi', 'es', 'si', 'km', 'bn', 'fr', 'mn', 'ur',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('admin*')) {
            App::setLocale('ja');

            return $next($request);
        }

        $locale = $request->cookie('locale');

        if (! in_array($locale, self::SUPPORTED_LOCALES, true)) {
            $locale = config('app.locale');
        }

        App::setLocale($locale);

        return $next($request);
    }
}
