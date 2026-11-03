<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ملاحظات الوكالة (نوفمبر 2026):
 * - صفحة «اتصل بنا» ونموذج تواصل تُحفظ رسائله ويُنبَّه بها كل حسابات اللوحة (contact_messages).
 * - تقييم أداء الخدمة: راضٍ / غير راضٍ / غير راضٍ ويحتاج إلى تطوير مع الملاحظات (service_ratings).
 * - رابط إضافي لكل مسار بجوار «صفحة المسار على موقع وزارة التعليم» (المسار الصحي: التحقق من الجامعة والتخصص).
 * - البريد الإلكتروني ضمن وسائل الاتصال.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('phone', 30)->default('');
            $table->string('email', 160)->default('');
            $table->string('subject', 200);
            $table->text('message');
            $table->string('source', 20)->default('page'); // page | assistant
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index('created_at');
        });

        // آخر اطلاع لكل مستخدم على صندوق الرسائل — ما بعده «رسائل جديدة» له
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('messages_seen_at')->nullable();
        });

        Schema::create('service_ratings', function (Blueprint $table) {
            $table->id();
            $table->string('rating', 20); // satisfied | unsatisfied | needs_improvement
            $table->text('notes')->nullable();
            $table->string('source', 20)->default('widget'); // widget | assistant | search
            $table->string('locale', 5)->default('ar');
            $table->timestamps();
            $table->index(['rating', 'created_at']);
        });

        Schema::table('tracks', function (Blueprint $table) {
            $table->string('extra_link_label')->default('');
            $table->string('extra_link_url', 500)->default('');
        });

        DB::table('tracks')->where('slug', 'sahhi')->where('extra_link_url', '')->update([
            'extra_link_label' => 'للتحقق من الجامعة والتخصص اضغط هنا',
            'extra_link_url' => 'https://ru.moe.gov.sa/Search',
        ]);
        $sahhi = DB::table('tracks')->where('slug', 'sahhi')->first(['id', 'content_en']);
        if ($sahhi) {
            $en = json_decode($sahhi->content_en ?? '[]', true) ?: [];
            $en['extra_link_label'] ??= 'Verify a university and major';
            DB::table('tracks')->where('id', $sahhi->id)->update(['content_en' => json_encode($en, JSON_UNESCAPED_UNICODE)]);
        }

        if (DB::table('contact_methods')->exists() && ! DB::table('contact_methods')->where('value', 'info@scholarshipepath.com')->exists()) {
            DB::table('contact_methods')->insert([
                'label' => 'البريد الإلكتروني', 'value' => 'info@scholarshipepath.com', 'href' => 'mailto:info@scholarshipepath.com',
                'icon' => 'mail', 'sort' => (int) DB::table('contact_methods')->max('sort') + 1,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('service_ratings');
        Schema::dropIfExists('contact_messages');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('messages_seen_at'));
        Schema::table('tracks', fn (Blueprint $table) => $table->dropColumn(['extra_link_label', 'extra_link_url']));
    }
};
