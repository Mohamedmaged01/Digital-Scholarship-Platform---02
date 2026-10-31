<?php

use App\Support\DefaultContent;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * قوائم «الدليل الاسترشادي لترتيب قوائم الجامعات حسب المجالات 2026-2027»
 * (ملاحظتا الوكالة 32 و33): لكل مسار من الروّاد والبحث والتطوير وإمداد
 * المجالات وتخصصاتها، والمؤسسات التعليمية بترتيبها في الدليل.
 * تُستورد البيانات هنا مباشرة فلا حاجة لتشغيل db:seed على الخادم.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tracks', function (Blueprint $table) {
            // [{key, nameAr, nameEn, majors: [{ar, en, degree?}]}]
            $table->json('guideline_lists')->nullable();
        });

        Schema::create('guideline_institutions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('path_id')->constrained('tracks')->cascadeOnDelete();
            $table->string('field_key', 20);
            $table->foreignId('institution_id')->constrained('universities')->cascadeOnDelete();
            $table->unsignedSmallInteger('position');
            $table->string('note', 40)->nullable();
            $table->index(['path_id', 'field_key', 'position']);
        });

        DefaultContent::resetGuideline();
    }

    public function down(): void
    {
        Schema::dropIfExists('guideline_institutions');

        Schema::table('tracks', function (Blueprint $table) {
            $table->dropColumn('guideline_lists');
        });
    }
};
