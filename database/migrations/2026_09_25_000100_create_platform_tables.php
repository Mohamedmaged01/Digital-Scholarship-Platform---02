<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * جداول منصّة برنامج خادم الحرمين الشريفين للابتعاث.
 * المصفوفات (الدرجات، التخصصات، الكلمات المفتاحية…) تُخزَّن كـ JSON
 * لتعمل المخططات على SQLite وPostgreSQL وMySQL دون تعديل.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ---------- الأخبار والإعلانات (المركز الإعلامي) ----------
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('excerpt');
            $table->text('body');
            $table->string('category', 20)->index(); // announcement | admissions | meetings | partnerships
            $table->boolean('pinned')->default(false);
            $table->date('published_on');
            $table->string('author')->default('');
            $table->unsignedSmallInteger('read_minutes')->default(3);
            $table->timestamps();

            $table->index(['pinned', 'published_on']);
        });

        // ---------- قاعدة معرفة المساعد الذكي ----------
        Schema::create('kb_entries', function (Blueprint $table) {
            $table->id();
            $table->text('question');
            $table->text('answer');
            $table->json('keywords');
            $table->boolean('is_custom')->default(true)->index();
            $table->timestamps();
        });

        // ---------- الأسئلة بلا إجابة (تلتقطها المحادثة) ----------
        Schema::create('unanswered_questions', function (Blueprint $table) {
            $table->id();
            $table->string('question', 500)->unique();
            $table->unsignedInteger('asked_count')->default(1);
            $table->timestamp('first_asked_at')->useCurrent();
            $table->timestamp('last_asked_at')->useCurrent();
        });

        // ---------- مسارات الابتعاث ----------
        Schema::create('tracks', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 40)->unique(); // يستخدمه محرّك المطابقة (rowad, emdad, …)
            $table->string('code', 10);
            $table->string('name');
            $table->string('en_subtitle')->default('');
            $table->string('badge')->default('');
            $table->text('description');
            $table->string('icon', 30)->default('rocket');
            $table->string('color', 20)->default('#177252');
            $table->json('degrees');
            $table->string('gpa')->default('');
            $table->string('ranking')->default('');
            $table->json('fields');
            $table->unsignedSmallInteger('extra_fields')->default(0);
            $table->json('perks');
            $table->string('image')->default('');
            $table->unsignedSmallInteger('sort')->default(0)->index();
            $table->timestamps();
        });

        // ---------- الجامعات العالمية المعتمدة ----------
        Schema::create('universities', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('rank')->index();
            $table->string('name_en');
            $table->string('name_ar');
            $table->string('city')->default('');
            $table->string('country')->default('');
            $table->string('region', 20)->index(); // na | europe | asia | oceania
            $table->json('fields');
            $table->string('acceptance', 20)->default('');
            $table->timestamps();
        });

        // ---------- محطات خارطة الطريق ----------
        Schema::create('stations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('title');
            $table->text('description');
            $table->text('detail');
            $table->string('icon', 30)->default('compass');
            $table->string('duration')->default('');
            $table->json('points');
            $table->unsignedSmallInteger('sort')->default(0)->index();
            $table->timestamps();
        });

        // ---------- مركز الملفات المرفوعة ----------
        Schema::create('managed_files', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('path');
            $table->string('mime')->default('application/octet-stream');
            $table->unsignedBigInteger('size')->default(0);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // ---------- مشتركو النشرة البريدية ----------
        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_subscribers');
        Schema::dropIfExists('managed_files');
        Schema::dropIfExists('stations');
        Schema::dropIfExists('universities');
        Schema::dropIfExists('tracks');
        Schema::dropIfExists('unanswered_questions');
        Schema::dropIfExists('kb_entries');
        Schema::dropIfExists('news');
    }
};
