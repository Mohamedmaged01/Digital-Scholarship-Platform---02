<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** إعدادات المنصة القابلة للتعديل من اللوحة (مفتاح ← قيمة)، مع القيم الافتراضية في config/kasp.php. */
class SiteSetting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    public static function get(string $key): string
    {
        // تُقرأ مرة واحدة لكل طلب
        if (! app()->bound('site.settings')) {
            app()->instance('site.settings', self::pluck('value', 'key')->all());
        }
        $value = app('site.settings')[$key] ?? null;

        return filled($value) ? $value : (string) config("kasp.home_defaults.{$key}", '');
    }

    /** النص حسب لغة الواجهة: hero_title → hero_title_ar أو hero_title_en */
    public static function localized(string $key): string
    {
        return self::get($key.'_'.(app()->getLocale() === 'en' ? 'en' : 'ar'));
    }

    /** @param array<string, string|null> $values */
    public static function put(array $values): void
    {
        foreach ($values as $key => $value) {
            self::updateOrCreate(['key' => $key], ['value' => $value ?? '']);
        }
        app()->forgetInstance('site.settings');
    }
}
