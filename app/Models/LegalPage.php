<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

/** الصفحات القانونية (سياسة الخصوصية، شروط الاستخدام…) — تُدار من اللوحة. */
class LegalPage extends Model
{
    protected $fillable = ['slug', 'title_ar', 'content', 'sort'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * يحوّل **عريض** و*مائل* إلى وسوم، والفقرات المفصولة بسطر فارغ إلى <p>.
     * يُهرَّب النص أولًا فلا يمكن حقن HTML من المحتوى.
     */
    public function renderedContent(): HtmlString
    {
        $html = collect(preg_split('/\R\s*\R/u', trim($this->content)))
            ->map(function (string $para) {
                $p = nl2br(e(trim($para)));
                $p = preg_replace('/\*\*(.+?)\*\*/u', '<strong class="text-ink">$1</strong>', $p);
                $p = preg_replace('/\*(.+?)\*/u', '<em class="text-slate-500">$1</em>', $p);

                return '<p>'.$p.'</p>';
            })
            ->implode('');

        return new HtmlString($html);
    }
}
