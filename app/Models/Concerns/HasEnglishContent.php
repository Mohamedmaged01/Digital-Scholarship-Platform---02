<?php

namespace App\Models\Concerns;

/**
 * محتوى ثنائي اللغة: العمود الأصلي عربي، والنسخة الإنجليزية في عمود JSON `content_en`
 * بالمفاتيح نفسها. إن غابت الترجمة يُعرض النص العربي.
 */
trait HasEnglishContent
{
    public function initializeHasEnglishContent(): void
    {
        $this->mergeFillable(['content_en']);
        $this->mergeCasts(['content_en' => 'array']);
    }

    /** قيمة الحقل بلغة الواجهة الحالية. */
    public function tr(string $field): mixed
    {
        if (app()->getLocale() === 'en') {
            $value = $this->content_en[$field] ?? null;
            if ($value !== null && $value !== '' && $value !== []) {
                return $value;
            }
        }

        return $this->getAttribute($field);
    }

    /** هل للحقل ترجمة إنجليزية؟ (للحقول التي تبقى بالعربية مع تنبيه) */
    public function hasEnglish(string $field): bool
    {
        return ! empty($this->content_en[$field] ?? null);
    }
}
