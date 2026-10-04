<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * الإصدار 3.0 — ما تبقّى بعد إعادة الهيكلة العلائقية:
 * صفحة المسار التفصيلية، برامج واعد، الأدلة، الصفحات القانونية،
 * قيود التخصصات، الإحصاءات ووسائل الاتصال المُدارة من اللوحة.
 */
return new class extends Migration
{
    public function up(): void
    {
        // معرّف نصي ثابت يربط بيانات v3 (mit, lse, …) بالسجلات
        Schema::table('universities', function (Blueprint $table) {
            $table->string('slug', 60)->nullable()->unique()->after('id');
        });

        Schema::table('news', function (Blueprint $table) {
            $table->string('image')->default('')->after('read_minutes');
        });

        Schema::table('guides', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('source_id');
        });

        // تفاصيل برامج واعد: كل برنامج مستقل بشروطه ومتطلباته
        Schema::table('programs', function (Blueprint $table) {
            $table->string('slug', 60)->nullable()->unique()->after('id');
            $table->string('institution_name')->default('')->after('institution_id');
            $table->string('country_name')->default('')->after('country_id');
            $table->string('city')->default('')->after('country_name');
            $table->string('sector')->default('')->after('city');
            $table->string('major_name')->default('')->after('major_id');
            $table->json('required_majors')->nullable();
            $table->json('gpa')->nullable();
            $table->json('languages')->nullable();
            $table->json('tests')->nullable();
            $table->json('conditions')->nullable();
            $table->string('website')->default('');
            $table->boolean('is_featured')->default(false);
            $table->text('notes')->nullable();
        });

        // قيود واستثناءات خاصة بتخصص داخل مسار (مثل: القانون للدكتوراه المهنية فقط)
        Schema::create('major_constraints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('path_id')->constrained('tracks')->cascadeOnDelete();
            $table->string('major_id', 80)->index();
            $table->string('constraint_type', 30)->default('note');
            $table->string('severity', 20)->default('note'); // required | preferred | note
            $table->string('title_ar');
            $table->string('title_en')->default('');
            $table->text('description_ar')->nullable();
            $table->json('allowed_degrees')->nullable();
            $table->json('excluded_degrees')->nullable();
            $table->string('value')->default('');
            $table->string('source_ref')->default('');
            $table->timestamps();
        });

        Schema::create('legal_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 60)->unique();
            $table->string('title_ar');
            $table->longText('content');
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        // أرقام تُدار من اللوحة؛ عند غيابها تُعرض الأرقام الهيكلية المحسوبة
        Schema::create('statistics', function (Blueprint $table) {
            $table->id();
            $table->string('label_ar');
            $table->string('label_en')->default('');
            $table->unsignedBigInteger('value')->default(0);
            $table->string('suffix', 20)->default('');
            $table->string('note_ar')->default('');
            $table->string('note_en')->default('');
            $table->string('location', 20)->default('general')->index(); // general | hero
            $table->string('source_id', 60)->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamp('last_verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('contact_methods', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('value');
            $table->string('href')->default('');
            $table->string('icon', 20)->default('phone'); // phone | mail | chat | map-pin
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_methods');
        Schema::dropIfExists('statistics');
        Schema::dropIfExists('legal_pages');
        Schema::dropIfExists('major_constraints');

        Schema::table('programs', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn([
                'slug', 'institution_name', 'country_name', 'city', 'sector', 'major_name', 'required_majors',
                'gpa', 'languages', 'tests', 'conditions', 'website', 'is_featured', 'notes',
            ]);
        });
        Schema::table('guides', fn (Blueprint $table) => $table->dropColumn('notes'));
        Schema::table('news', fn (Blueprint $table) => $table->dropColumn('image'));
        Schema::table('universities', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
