<?php

use App\Models\KbEntry;
use App\Support\DefaultContent;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * مسار التميز وفق ملف «تخصصات وجامعات التميز — محدث 2026م»:
 * التخصصات الـ23 المعتمدة، ولكل تخصص قائمة مؤسساته التعليمية (الجامعات العامة المعتمدة
 * مع الإضافية الخاصة به، أو قائمته الخاصة، أو جامعات وبرامج محددة)، بدل روابط المستكشف القديمة.
 * تُستورد البيانات هنا مباشرة فلا حاجة لتشغيل db:seed على الخادم.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guideline_institutions', function (Blueprint $table) {
            // مؤسسة إضافية خاصة بالتخصص فوق قائمة الجامعات العامة
            $table->boolean('extra')->default(false);
            // البرامج المحددة في المؤسسة (الإعلانات الرقمية والألعاب الرقمية)
            $table->json('programs')->nullable();
        });

        $track = DB::table('tracks')->where('slug', 'tamayoz')->first();
        if ($track) {
            // القانون (الدكتوراه المهنية) لم يعد ضمن التخصصات المعتمدة للمسار
            DB::table('path_degrees')->where('path_id', $track->id)->where('degree_id', 'professional_doctorate')->delete();
            $degrees = array_values(array_diff(json_decode($track->degrees, true) ?: [], ['professional_doctorate']));
            $update = ['degrees' => json_encode($degrees)];

            $fields = json_decode($track->fields, true) ?: [];
            if (in_array('القانون', $fields, true)) {
                $update['fields'] = json_encode(['إدارة أعمال', 'علوم سياسية وعلاقات دولية', 'عدالة جنائية', 'الألعاب الرقمية'], JSON_UNESCAPED_UNICODE);
                $en = json_decode($track->content_en ?? '', true) ?: [];
                $en['fields'] = ['Business Administration', 'Political Science and International Relations', 'Criminal Justice', 'Game Design'];
                $update['content_en'] = json_encode($en, JSON_UNESCAPED_UNICODE);
            }
            DB::table('tracks')->where('id', $track->id)->update($update);
        }

        DefaultContent::resetTamayoz();

        // إجابة المساعد الذكي عن تخصصات المسار
        $kb = collect(json_decode(file_get_contents(database_path('data/kb.json')), true))->firstWhere('id', 'q-tamayoz-majors');
        if ($kb && ! KbEntry::where('question', $kb['question'])->exists()) {
            KbEntry::create(['question' => $kb['question'], 'answer' => $kb['answer'], 'keywords' => $kb['keywords'], 'is_custom' => false]);
        }
    }

    public function down(): void
    {
        Schema::table('guideline_institutions', function (Blueprint $table) {
            $table->dropColumn(['extra', 'programs']);
        });
    }
};
