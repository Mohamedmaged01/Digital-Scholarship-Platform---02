<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * إعادة هيكلة نموذج البيانات من مسطّح إلى علائقي (الإصدار 3.0).
 *
 *   مسار → درجة علمية → مجال → تخصص → مؤسسة → برنامج → شروط → مصدر + إصدار
 *
 * أسماء الجداول والأعمدة منقولة حرفيًا من مواصفة الإصدار الثالث
 * (server/migrations/001-restructure.sql) حتى تبقى المطابقة مع التقرير مباشرة.
 * لذلك `path_id` هنا يشير إلى `tracks.id` — "المسار" و"المسلك" الشيء نفسه.
 *
 * لا يُحذف أي عمود قديم: `tracks.gpa` و`universities.rank` و`universities.acceptance`
 * تصبح اختيارية فقط وتخرج من النماذج والواجهات، تمامًا كما فعل الإصدار الثالث.
 */
return new class extends Migration
{
    public function up(): void
    {
        /* ── الجداول المرجعية ──────────────────────────────────────────── */

        Schema::create('data_sources', function (Blueprint $table) {
            $table->string('id', 60)->primary();
            $table->string('name_ar');
            $table->string('name_en')->default('');
            $table->string('source_type', 20)->default('official'); // official | website | guideline
            $table->string('url')->default('');
            $table->string('authority')->default('');
            $table->date('publication_date')->nullable();
            $table->string('version', 20)->default('');
            $table->string('status', 20)->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('academic_years', function (Blueprint $table) {
            $table->string('id', 20)->primary();
            $table->string('name_ar');
            $table->string('name_en')->default('');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false)->index();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('countries', function (Blueprint $table) {
            $table->string('id', 60)->primary();
            $table->string('name_ar');
            $table->string('name_en')->default('');
            $table->string('region', 20)->default('other')->index();
            $table->string('iso_code', 3)->default('');
            $table->unsignedSmallInteger('sort')->default(0);
            $table->string('status', 20)->default('active');
        });

        Schema::create('academic_degrees', function (Blueprint $table) {
            $table->string('id', 40)->primary();
            $table->string('name_ar');
            $table->string('name_en')->default('');
            $table->unsignedSmallInteger('sort')->default(0)->index();
            $table->string('status', 20)->default('active');
        });

        // ملاحظة: هذا جدول "المجالات المعرفية". لا يُخلط مع عمود JSON باسم
        // `fields` الموجود أصلًا في `tracks` و`universities`.
        Schema::create('fields', function (Blueprint $table) {
            $table->string('id', 60)->primary();
            $table->string('name_ar');
            $table->string('name_en')->default('');
            $table->unsignedSmallInteger('sort')->default(0)->index();
            $table->string('status', 20)->default('active');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('majors', function (Blueprint $table) {
            $table->string('id', 80)->primary();
            $table->string('name_ar');
            $table->string('name_en')->default('');
            $table->string('field_id', 60)->nullable()->index();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->string('status', 20)->default('active');
            $table->timestamp('created_at')->nullable();
        });

        /* ── جداول الربط ──────────────────────────────────────────────── */

        Schema::create('path_degrees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('path_id')->constrained('tracks')->cascadeOnDelete();
            $table->string('degree_id', 40)->index();
            $table->string('status', 24)->default('active');
            $table->string('source_id', 60)->nullable();
            $table->string('version', 20)->default('2026-2027');
            $table->timestamp('last_verified_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->unique(['path_id', 'degree_id']);
        });

        Schema::create('path_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('path_id')->constrained('tracks')->cascadeOnDelete();
            $table->string('field_id', 60)->index();
            $table->string('status', 24)->default('active');
            $table->string('source_id', 60)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->unique(['path_id', 'field_id']);
        });

        Schema::create('path_majors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('path_id')->constrained('tracks')->cascadeOnDelete();
            $table->string('major_id', 80)->index();
            $table->string('degree_id', 40)->nullable()->index();
            $table->string('status', 24)->default('active');
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->string('source_id', 60)->nullable();
            $table->string('version', 20)->default('2026-2027');
            $table->timestamp('last_verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('path_major_institutions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('path_id')->constrained('tracks')->cascadeOnDelete();
            $table->string('degree_id', 40)->nullable();
            $table->string('field_id', 60)->nullable();
            $table->string('major_id', 80)->nullable();
            $table->foreignId('institution_id')->constrained('universities')->cascadeOnDelete();
            $table->string('country_id', 60)->nullable();
            // القاعدة الذهبية: لا تخمين — أي ربط بلا مصدر يبقى بحاجة تحقّق.
            $table->string('status', 24)->default('needs_verification')->index();
            $table->string('source_id', 60)->nullable();
            $table->string('version', 20)->default('2026-2027');
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->timestamp('last_verified_at')->nullable();
            $table->timestamps();
        });

        /* ── الشروط والبرامج والأدلة ─────────────────────────────────── */

        Schema::create('requirements', function (Blueprint $table) {
            $table->id();
            $table->string('scope_type', 20)->default('global'); // global | path | program | major
            $table->string('scope_id', 80)->default('');
            $table->string('requirement_type', 20)->default('text');
            $table->string('title_ar');
            $table->string('title_en')->default('');
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->string('value')->default('');
            $table->string('unit', 40)->default('');
            $table->string('operator', 10)->default('');
            $table->boolean('is_required')->default(true);
            $table->string('status', 24)->default('active');
            $table->string('source_id', 60)->nullable();
            $table->string('version', 20)->default('2026-2027');
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->timestamp('last_verified_at')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
            $table->index(['scope_type', 'scope_id']);
        });

        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('path_id')->constrained('tracks')->cascadeOnDelete();
            $table->string('name_ar');
            $table->string('name_en')->default('');
            $table->string('company_ar')->default('');
            $table->foreignId('institution_id')->nullable()->constrained('universities')->nullOnDelete();
            $table->string('country_id', 60)->nullable();
            $table->string('degree_id', 40)->nullable();
            $table->string('major_id', 80)->nullable();
            $table->string('program_type', 24)->default('scholarship');
            $table->string('duration', 60)->default('');
            $table->date('study_start_date')->nullable();
            $table->date('application_start')->nullable();
            $table->date('application_end')->nullable();
            $table->string('application_status', 24)->default('not_started')->index();
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->string('status', 24)->default('active');
            $table->string('source_id', 60)->nullable();
            $table->string('version', 20)->default('2026-2027');
            $table->timestamp('last_verified_at')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('guides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('path_id')->nullable()->constrained('tracks')->nullOnDelete();
            $table->string('title_ar');
            $table->string('title_en')->default('');
            $table->string('guide_type', 20)->default('pdf'); // pdf | link
            $table->string('url')->default('');
            $table->foreignId('file_id')->nullable()->constrained('managed_files')->nullOnDelete();
            $table->string('version', 20)->default('2026-2027');
            $table->date('publication_date')->nullable();
            $table->date('effective_date')->nullable();
            $table->boolean('is_current')->default(true)->index();
            $table->string('status', 24)->default('active');
            $table->string('source_id', 60)->nullable();
            $table->timestamps();
        });

        Schema::create('audit_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('admin_email')->default('');
            $table->string('action', 40);
            $table->string('entity_type', 40);
            $table->string('entity_id', 80)->default('');
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();
            $table->string('ip', 45)->default('');
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->index(['entity_type', 'entity_id']);
        });

        /* ── تطوير الجداول القائمة ───────────────────────────────────── */

        Schema::table('tracks', function (Blueprint $table) {
            $table->string('application_status', 24)->default('not_started');
            $table->date('application_start')->nullable();
            $table->date('application_end')->nullable();
            $table->text('overview_ar')->nullable();
            $table->json('general_conditions')->nullable();
            $table->json('special_conditions')->nullable();
            $table->json('policies')->nullable();
            $table->json('differentiation_criteria')->nullable();
            $table->string('content_status', 24)->default('published');
            $table->string('source_id', 60)->nullable();
            $table->string('academic_year_id', 20)->nullable();
            $table->timestamp('last_verified_at')->nullable();
            $table->string('version', 20)->default('2026-2027');
        });

        Schema::table('universities', function (Blueprint $table) {
            $table->string('country_id', 60)->nullable();
            $table->string('website')->default('');
            // §J: لم يُتحقق من ربط أي مؤسسة بمسار عبر مصدر رسمي بعد.
            $table->string('institution_status', 24)->default('needs_verification');
            $table->string('source_id', 60)->nullable();
            $table->timestamp('last_verified_at')->nullable();
        });

        Schema::table('kb_entries', function (Blueprint $table) {
            $table->string('scope_type', 20)->default('global');
            $table->string('scope_id', 80)->default('');
            $table->string('source_id', 60)->nullable();
            $table->string('version', 20)->default('');
            $table->timestamp('last_verified_at')->nullable();
            $table->string('review_status', 24)->default('verified');
            $table->string('category', 40)->default('general');
        });

        // الأعمدة المهجورة تبقى في القاعدة حفاظًا على البيانات، لكنها تصبح
        // اختيارية حتى يتمكّن التطبيق من الكتابة بدونها بعد إزالتها من النماذج.
        Schema::table('tracks', function (Blueprint $table) {
            $table->string('gpa')->nullable()->default('')->change();
        });

        Schema::table('universities', function (Blueprint $table) {
            $table->unsignedInteger('rank')->nullable()->change();
            $table->string('acceptance', 20)->nullable()->default('')->change();
        });

        $this->seedReferenceData();
    }

    /** البذور المرجعية من §I في تقرير إعادة الهيكلة. */
    private function seedReferenceData(): void
    {
        $now = now();

        DB::table('academic_degrees')->insertOrIgnore([
            ['id' => 'bachelor', 'name_ar' => 'بكالوريوس', 'name_en' => 'Bachelor', 'sort' => 1],
            ['id' => 'master', 'name_ar' => 'ماجستير', 'name_en' => 'Master', 'sort' => 2],
            ['id' => 'phd', 'name_ar' => 'دكتوراه', 'name_en' => 'PhD', 'sort' => 3],
            ['id' => 'fellowship', 'name_ar' => 'زمالة', 'name_en' => 'Fellowship', 'sort' => 4],
            ['id' => 'diploma', 'name_ar' => 'دبلوم', 'name_en' => 'Diploma', 'sort' => 5],
            ['id' => 'professional_doctorate', 'name_ar' => 'دكتوراه مهنية', 'name_en' => 'Professional Doctorate', 'sort' => 6],
        ]);

        DB::table('academic_years')->insertOrIgnore([
            ['id' => '2025-2026', 'name_ar' => '2025-2026', 'name_en' => '2025-2026', 'is_current' => false, 'created_at' => $now],
            ['id' => '2026-2027', 'name_ar' => '2026-2027', 'name_en' => '2026-2027', 'is_current' => true, 'created_at' => $now],
        ]);

        DB::table('data_sources')->insertOrIgnore([
            ['id' => 'src-moe', 'name_ar' => 'وزارة التعليم', 'name_en' => 'Ministry of Education', 'source_type' => 'official', 'authority' => 'وزارة التعليم', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 'src-kasp', 'name_ar' => 'برنامج خادم الحرمين الشريفين للابتعاث', 'name_en' => 'KASP', 'source_type' => 'official', 'authority' => 'وزارة التعليم', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 'src-safeer', 'name_ar' => 'منصة سفير', 'name_en' => 'Safeer Platform', 'source_type' => 'website', 'authority' => 'وزارة التعليم', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 'src-guide', 'name_ar' => 'الدليل الاسترشادي', 'name_en' => 'Guideline Document', 'source_type' => 'guideline', 'authority' => 'برنامج الابتعاث', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        foreach (['audit_log', 'guides', 'programs', 'requirements', 'path_major_institutions',
            'path_majors', 'path_fields', 'path_degrees', 'majors', 'fields',
            'academic_degrees', 'countries', 'academic_years', 'data_sources'] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::table('tracks', function (Blueprint $table) {
            $table->dropColumn([
                'application_status', 'application_start', 'application_end', 'overview_ar',
                'general_conditions', 'special_conditions', 'policies', 'differentiation_criteria',
                'content_status', 'source_id', 'academic_year_id', 'last_verified_at', 'version',
            ]);
        });

        Schema::table('universities', function (Blueprint $table) {
            $table->dropColumn(['country_id', 'website', 'institution_status', 'source_id', 'last_verified_at']);
        });

        Schema::table('kb_entries', function (Blueprint $table) {
            $table->dropColumn([
                'scope_type', 'scope_id', 'source_id', 'version', 'last_verified_at',
                'review_status', 'category',
            ]);
        });
    }
};
