<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** شريحة في معرض الواجهة الرئيسية: صورة وعنوان قصير ورابط، تُدار من «الواجهة الرئيسية». */
class HeroSlide extends Model
{
    /** مجلد الصور المرفوعة داخل public — لا يحتاج storage:link على الخادم */
    public const UPLOAD_DIR = 'uploads/hero';

    protected $fillable = [
        'image', 'title_ar', 'title_en', 'subtitle_ar', 'subtitle_en', 'link', 'overlay', 'is_active', 'sort',
    ];

    protected function casts(): array
    {
        return ['overlay' => 'boolean', 'is_active' => 'boolean', 'sort' => 'integer'];
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort')->orderBy('id');
    }

    public function imageUrl(): string
    {
        return asset(ltrim($this->image, '/'));
    }

    public function title(): string
    {
        return app()->getLocale() === 'en' && $this->title_en ? $this->title_en : $this->title_ar;
    }

    public function subtitle(): string
    {
        return app()->getLocale() === 'en' && $this->subtitle_en ? $this->subtitle_en : $this->subtitle_ar;
    }

    /** قسم في الصفحة (#tracks) أو صفحة في المنصة (tracks/rowad) أو رابط خارجي */
    public function href(): string
    {
        return match (true) {
            $this->link === '' => '#tracks',
            str_starts_with($this->link, '#'), str_starts_with($this->link, 'http') => $this->link,
            default => url(ltrim($this->link, '/')),
        };
    }

    /** صورة مرفوعة من اللوحة (تُحذف مع الشريحة) لا صورة من صور المنصة الثابتة */
    public function hasUploadedImage(): bool
    {
        return str_starts_with(ltrim($this->image, '/'), self::UPLOAD_DIR.'/');
    }
}
