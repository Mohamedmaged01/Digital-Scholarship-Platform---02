<?php

namespace App\Support;

use App\Models\News;

/** روابط أقسام الصفحة الرئيسية — يُخفى قسم الأخبار ما دام لا توجد أخبار منشورة. */
class SiteNav
{
    /** @return list<array{href: string, label: string}> */
    public static function links(): array
    {
        return once(fn () => array_values(array_map(
            fn (array $l) => [...$l, 'label' => __($l['label'])],
            array_filter(config('kasp.nav'), fn (array $l) => $l['href'] !== '#news' || News::exists()),
        )));
    }
}
