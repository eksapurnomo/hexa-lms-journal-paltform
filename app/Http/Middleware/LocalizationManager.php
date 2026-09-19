<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LocalizationManager
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = ['en', 'id'];
        $defaultLocale = \Illuminate\Support\Facades\Config::get('app.fallback_locale', 'en');

        if (!in_array($defaultLocale, $supportedLocales)) {
            $defaultLocale = 'en';
        }

        if (session()->has('locale')) {
            $locale = session()->get('locale');
            if (in_array($locale, $supportedLocales)) {
                app()->setLocale($locale);
            } else {
                app()->setLocale($defaultLocale);
                session()->put('locale', $defaultLocale);
            }
        } else {
            app()->setLocale($defaultLocale);
        }

        return $next($request);
    }
}
