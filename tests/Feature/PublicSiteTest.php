<?php

namespace Tests\Feature;

use App\Models\News;
use App\Models\NewsletterSubscriber;
use App\Models\UnansweredQuestion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_home_page_renders_database_content(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('مسار الروّاد')
            ->assertSee('التقديم متاح حاليًا على')
            // التميز والصحي ببطاقات كاملة مثل بقية المسارات
            ->assertDontSee('مسارات أخرى — رابط المسار والدليل الاسترشادي')
            ->assertSee(route('tracks.show', 'tamayoz'), false)
            ->assertSee(route('tracks.show', 'sahhi'), false)
            ->assertSee('مركز الاتصال الموحد')
            ->assertSee('استكشف')
            // ملاحظات الوكالة: لا عبارات تسويقية ولا أخبار تجريبية ولا نسب مطابقة وهمية
            ->assertDontSee('المقاعد محدودة')
            ->assertDontSee('الجامعات الشريكة')
            ->assertDontSee('98.2%')
            // قسم الأخبار ظاهر دائمًا (رابط «المركز الإعلامي» في القائمة)، وزر الإضافة للإدارة فقط
            ->assertSee('id="news"', false)
            ->assertSee('لا توجد أخبار منشورة حاليًا')
            ->assertDontSee('رفع وإضافة خبر جديد')
            // زر «ابدأ التقديم» يفتح الموقع التعريفي الرسمي، ومعرض الواجهة: الشعار ثم صور المسارات الست
            ->assertSee('href="https://uap.sa/"', false)
            ->assertSee('href="https://sites.moe.gov.sa/scholarship-program/"', false)
            ->assertSee('images/hero-kasp-banner.jpg', false)
            ->assertSee('images/tracks/rowad.jpg', false)
            ->assertSee('images/tracks/sahhi.jpg', false)
            ->assertSee('aria-label="7 / 7"', false)
            ->assertSee('حدد مسارك')
            ->assertDontSee('hero-students-group.jpg', false);
    }

    public function test_published_news_appears_on_home_page(): void
    {
        News::create([
            'title' => 'فتح التقديم على مسار الروّاد', 'excerpt' => 'موجز الإعلان الرسمي',
            'body' => 'نص الإعلان الرسمي كاملًا', 'category' => 'announcement',
            'published_on' => '2026-11-01', 'author' => 'وزارة التعليم', 'read_minutes' => 2,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('فتح التقديم على مسار الروّاد')
            ->assertDontSee('لا توجد أخبار منشورة حاليًا');
    }

    public function test_site_switches_between_arabic_and_english(): void
    {
        $this->get('/')->assertSee('dir="rtl"', false)->assertSee('English');

        $this->get('/lang/en')->assertRedirect();
        $this->get('/')
            ->assertSee('dir="ltr"', false)
            ->assertSee('lang="en"', false)
            ->assertSee('Rowad Track')
            ->assertSee('Scholarship tracks')
            ->assertSee('العربية');
        $this->get('/tracks/rowad')->assertOk()->assertSee('Application policies')->assertSee('All majors available');
        // المسار الصحي مترجم بالكامل — لا تنبيه «يُعرض بالعربية»
        $this->get('/tracks/sahhi')->assertOk()
            ->assertSee('Nursing — General scholarship conditions for the Health track')
            ->assertSee('Group One medical certificates')
            ->assertDontSee('shown in Arabic');

        $this->get('/lang/ar');
        $this->get('/')->assertSee('dir="rtl"', false)->assertSee('مسار الروّاد');
        $this->get('/lang/fr')->assertNotFound();
    }

    public function test_assistant_answers_known_questions(): void
    {
        $this->postJson('/assistant/ask', ['q' => 'ما شروط التقديم؟'])
            ->assertOk()
            ->assertJsonPath('answer', fn ($a) => str_contains($a, 'سعودي الجنسية'));

        $this->assertSame(0, UnansweredQuestion::count());
    }

    public function test_assistant_logs_unknown_questions_once_with_counter(): void
    {
        $this->postJson('/assistant/ask', ['q' => 'كم سعر القهوة في المريخ'])->assertOk()->assertJsonPath('answer', null);
        $this->postJson('/assistant/ask', ['q' => 'كم سعر القهوة في المريخ'])->assertOk();

        $this->assertSame(1, UnansweredQuestion::count());
        $this->assertSame(2, UnansweredQuestion::first()->asked_count);
    }

    public function test_assistant_validates_input(): void
    {
        $this->postJson('/assistant/ask', ['q' => ''])->assertUnprocessable();
    }

    public function test_site_search_returns_grouped_results(): void
    {
        $this->getJson('/search?q='.urlencode('أكسفورد'))
            ->assertOk()
            ->assertJsonPath('results.0.groupId', 'universities')
            ->assertJsonPath('results.0.href', route('tracks.show', 'tamayoz'));

        $this->getJson('/search?q=a')->assertOk()->assertJsonCount(0, 'results');
    }

    public function test_newsletter_subscription_is_stored_once(): void
    {
        $this->post('/newsletter', ['email' => 'Student@Example.com'])->assertRedirect()->assertSessionHas('subscribed');
        $this->post('/newsletter', ['email' => 'student@example.com'])->assertRedirect();

        $this->assertSame(1, NewsletterSubscriber::count());
        $this->assertDatabaseHas('newsletter_subscribers', ['email' => 'student@example.com']);
    }
}
