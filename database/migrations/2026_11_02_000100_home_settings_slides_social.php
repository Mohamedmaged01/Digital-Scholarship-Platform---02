<?php

use App\Support\DefaultContent;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ملاحظات الوكالة (نوفمبر 2026):
 * - عنوان الواجهة ونصها (عربي/إنجليزي) ورابط «ابدأ التقديم» تُعدَّل من اللوحة (site_settings).
 * - معرض الواجهة بصور مستقلة عن صور بطاقات المسارات (hero_slides).
 * - أيقونات مواقع التواصل الاجتماعي في التذييل (social_links).
 * - النص الإنجليزي للشروط الخاصة بالمسار الصحي.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->string('key', 80)->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('hero_slides', function (Blueprint $table) {
            $table->id();
            $table->string('image', 500);
            $table->string('title_ar')->default('');
            $table->string('title_en')->default('');
            $table->string('subtitle_ar')->default('');
            $table->string('subtitle_en')->default('');
            $table->string('link', 500)->default('');
            // الصور الفوتوغرافية تحت تدرّج داكن؛ صورة الشعار بلا تدرّج
            $table->boolean('overlay')->default(true);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('social_links', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->string('platform', 20);
            $table->string('url', 500);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        DefaultContent::resetHeroSlides();
        DefaultContent::resetSocialLinks();

        // الشروط الخاصة بالمسار الصحي بالإنجليزية (تُضاف فقط إن لم تكن موجودة)
        $sahhi = collect(json_decode(file_get_contents(database_path('data/tracks.json')), true))->firstWhere('id', 'sahhi');
        $row = DB::table('tracks')->where('slug', 'sahhi')->first(['id', 'content_en']);
        if ($sahhi && $row) {
            $en = json_decode($row->content_en ?? '[]', true) ?: [];
            if (empty($en['special_conditions'])) {
                $en['special_conditions'] = $sahhi['contentEn']['special_conditions'];
                DB::table('tracks')->where('id', $row->id)->update(['content_en' => json_encode($en, JSON_UNESCAPED_UNICODE)]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('social_links');
        Schema::dropIfExists('hero_slides');
        Schema::dropIfExists('site_settings');
    }
};
