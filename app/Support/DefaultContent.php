<?php

namespace App\Support;

use App\Models\KbEntry;
use App\Models\News;
use App\Models\Station;
use App\Models\Track;
use App\Models\University;
use Illuminate\Support\Facades\DB;

/**
 * المحتوى الافتراضي للمنصّة (database/data/*.json).
 * يستخدمه مُعبّئ قاعدة البيانات وأزرار «استعادة الافتراضي» في لوحة التحكم.
 */
class DefaultContent
{
    /** أسماء الأيقونات القديمة ← أسماء Lucide */
    private const ICONS = [
        'flask' => 'flask-conical',
        'shield' => 'shield-check',
        'pen' => 'file-pen-line',
        'upload' => 'file-up',
        'plane' => 'plane-takeoff',
    ];

    private static function load(string $name): array
    {
        return json_decode(file_get_contents(database_path("data/{$name}.json")), true);
    }

    private static function icon(string $icon): string
    {
        return self::ICONS[$icon] ?? $icon;
    }

    public static function resetTracks(): void
    {
        DB::transaction(function () {
            Track::query()->delete();
            foreach (self::load('tracks') as $i => $t) {
                Track::create([
                    'slug' => $t['id'],
                    'code' => $t['code'],
                    'name' => $t['name'],
                    'en_subtitle' => $t['enSubtitle'],
                    'badge' => $t['badge'],
                    'description' => $t['desc'],
                    'icon' => self::icon($t['icon']),
                    'color' => $t['color'],
                    'degrees' => $t['degrees'],
                    'gpa' => $t['gpa'],
                    'ranking' => $t['ranking'],
                    'fields' => $t['fields'],
                    'extra_fields' => $t['extraFields'],
                    'perks' => $t['perks'],
                    'image' => $t['image'],
                    'sort' => $i + 1,
                ]);
            }
        });
    }

    public static function resetUniversities(): void
    {
        DB::transaction(function () {
            University::query()->delete();
            foreach (self::load('universities') as $u) {
                University::create([
                    'rank' => $u['rank'],
                    'name_en' => $u['nameEn'],
                    'name_ar' => $u['nameAr'],
                    'city' => $u['city'],
                    'country' => $u['country'],
                    'region' => $u['region'],
                    'fields' => $u['fields'],
                    'acceptance' => $u['acceptance'],
                ]);
            }
        });
    }

    public static function resetStations(): void
    {
        DB::transaction(function () {
            Station::query()->delete();
            foreach (self::load('stations') as $i => $s) {
                Station::create([
                    'code' => $s['n'],
                    'title' => $s['title'],
                    'description' => $s['desc'],
                    'detail' => $s['detail'],
                    'icon' => self::icon($s['icon']),
                    'duration' => $s['duration'],
                    'points' => $s['points'],
                    'sort' => $i + 1,
                ]);
            }
        });
    }

    public static function resetNews(): void
    {
        DB::transaction(function () {
            News::query()->delete();
            foreach (self::load('news') as $n) {
                News::create([
                    'title' => $n['title'],
                    'excerpt' => $n['excerpt'],
                    'body' => $n['body'],
                    'category' => $n['category'],
                    'pinned' => $n['pinned'],
                    'published_on' => $n['date'],
                    'author' => $n['author'],
                    'read_minutes' => $n['readMinutes'],
                ]);
            }
        });
    }

    /** الإجابات الأساسية فقط — لا تمس الإجابات المضافة من الإدارة */
    public static function resetCoreKnowledge(): void
    {
        DB::transaction(function () {
            KbEntry::where('is_custom', false)->delete();
            foreach (self::load('kb') as $k) {
                KbEntry::create([
                    'question' => $k['question'],
                    'answer' => $k['answer'],
                    'keywords' => $k['keywords'],
                    'is_custom' => false,
                ]);
            }
        });
    }
}
