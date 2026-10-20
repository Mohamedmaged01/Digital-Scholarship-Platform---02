<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * محتوى الدليل الاسترشادي لكل مسار (ملاحظات الوكالة — أكتوبر 2026):
 * الشروط الإضافية، الأسئلة الشائعة، بطاقة بيانات المسار، نطاق التخصصات،
 * العدد المحدد للمؤسسات التعليمية، والفصل بين المسارات الأساسية والمسارات
 * المعروضة بروابطها فقط (التميز والصحي).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tracks', function (Blueprint $table) {
            $table->string('tier', 20)->default('main');
            $table->string('majors_scope', 20)->default('listed');
            $table->json('match_fields')->nullable();
            $table->unsignedInteger('institutions_count')->nullable();
            $table->string('external_url', 500)->default('');
            $table->json('additional_conditions')->nullable();
            $table->json('faqs')->nullable();
            $table->json('details')->nullable();
            // النسخة الإنجليزية للمحتوى المعروض (الواجهة ثنائية اللغة)
            $table->json('content_en')->nullable();
        });

        Schema::table('stations', function (Blueprint $table) {
            $table->json('content_en')->nullable();
        });

        DB::table('academic_degrees')->insertOrIgnore([
            ['id' => 'subspecialty', 'name_ar' => 'تخصص دقيق', 'name_en' => 'Subspecialty', 'sort' => 7],
        ]);
    }

    public function down(): void
    {
        Schema::table('tracks', function (Blueprint $table) {
            $table->dropColumn([
                'tier', 'majors_scope', 'match_fields', 'institutions_count',
                'external_url', 'additional_conditions', 'faqs', 'details', 'content_en',
            ]);
        });

        Schema::table('stations', function (Blueprint $table) {
            $table->dropColumn('content_en');
        });

        DB::table('academic_degrees')->where('id', 'subspecialty')->delete();
    }
};
