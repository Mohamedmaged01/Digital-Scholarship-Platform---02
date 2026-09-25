<?php

namespace App\Support;

class Lists
{
    /** «هندسة، حاسب, طب» ← ['هندسة', 'حاسب', 'طب'] */
    public static function split(?string $value, string $pattern = '/[،,]/u'): array
    {
        return array_values(array_filter(array_map('trim', preg_split($pattern, (string) $value))));
    }

    public static function lines(?string $value): array
    {
        return self::split($value, '/\R/u');
    }

    public static function join(array $items, string $sep = '، '): string
    {
        return implode($sep, $items);
    }

    /** مطابقة كل كلمات البحث (بعد توحيد النص العربي) */
    public static function matches(?string $query, string $haystack): bool
    {
        $tokens = ArabicText::words((string) $query);
        if ($tokens === []) {
            return true;
        }
        $hay = ArabicText::normalize($haystack);

        foreach ($tokens as $t) {
            if (! str_contains($hay, $t)) {
                return false;
            }
        }

        return true;
    }
}
