<?php

namespace App\Support;

/**
 * توحيد النص العربي للمطابقة: إزالة التشكيل وتوحيد الهمزات والتاء المربوطة.
 */
class ArabicText
{
    public static function normalize(string $s): string
    {
        $s = mb_strtolower($s);
        $s = preg_replace('/[\x{064B}-\x{065F}\x{0670}]/u', '', $s);
        $s = preg_replace('/[أإآٱ]/u', 'ا', $s);
        $s = strtr($s, ['ى' => 'ي', 'ؤ' => 'و', 'ئ' => 'ي', 'ة' => 'ه']);
        // علامات الترقيم العربية (؟ ، ؛ ـ) تُعدّ ضمن \p{Arabic} في PCRE فتُحذف صراحةً
        $s = preg_replace('/[\x{060C}\x{061B}\x{061F}\x{0640}\x{066B}\x{066C}\x{06D4}]/u', ' ', $s);
        $s = preg_replace('/[^\p{Arabic}a-z0-9\s%]/u', ' ', $s);
        $s = preg_replace('/\s+/u', ' ', $s);

        return trim($s);
    }

    /** @return list<string> */
    public static function words(string $s): array
    {
        $n = self::normalize($s);

        return $n === '' ? [] : explode(' ', $n);
    }
}
