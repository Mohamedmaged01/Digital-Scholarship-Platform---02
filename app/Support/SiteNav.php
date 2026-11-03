<?php

namespace App\Support;

/**
 * روابط القائمة: أقسام الصفحة الرئيسية (#tracks…) وصفحات مستقلة (/contact).
 * `home` للاستخدام داخل الصفحة الرئيسية، و`url` للصفحات الأخرى.
 */
class SiteNav
{
    /** @return list<array{href: string, label: string, home: string, url: string}> */
    public static function links(): array
    {
        return once(fn () => array_map(function (array $l) {
            $anchor = str_starts_with($l['href'], '#');
            $page = url(ltrim($l['href'], '/'));

            return [
                ...$l,
                'label' => __($l['label']),
                'home' => $anchor ? $l['href'] : $page,
                'url' => $anchor ? url('/').$l['href'] : $page,
            ];
        }, config('kasp.nav')));
    }
}
