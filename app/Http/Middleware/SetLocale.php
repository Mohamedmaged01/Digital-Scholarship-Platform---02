<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** لغة الواجهة العامة (العربية افتراضيًا، والإنجليزية اختياريًا) — تُحفظ في الجلسة. */
class SetLocale
{
    public const LOCALES = ['ar', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale', config('app.locale'));
        app()->setLocale(in_array($locale, self::LOCALES, true) ? $locale : 'ar');

        return $next($request);
    }
}
