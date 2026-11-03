<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\ServiceRating;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Once;
use Tests\TestCase;

/** «اتصل بنا» وتنبيه الرسائل الجديدة لكل الحسابات، وتقييم أداء الخدمة، وقائمة الأقسام الجديدة. */
class ContactAndRatingTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function user(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    private function message(array $override = []): array
    {
        return [
            'name' => 'سارة أحمد', 'phone' => '0551234567', 'email' => 'sara@example.com',
            'subject' => 'استفسار عن مسار الروّاد', 'message' => 'أرغب بمعرفة مواعيد التقديم على مسار الروّاد.', ...$override,
        ];
    }

    public function test_nav_order_and_contact_page(): void
    {
        $home = $this->get('/')->assertOk();
        $home->assertSeeInOrder(['الرئيسية', 'المسارات', 'المسار الأنسب لك', 'رحلة المستفيد', 'قنوات الدعم', 'الأخبار', 'الأسئلة الشائعة', 'اتصل بنا']);
        $home->assertSee('href="'.url('contact').'"', false);

        $this->get('/contact')->assertOk()
            ->assertSee('نموذج تواصل معنا')
            ->assertSee('19996')
            ->assertSee('info@scholarshipepath.com')
            ->assertSee('href="https://x.com/moe_gov_sa"', false);

        $this->get('/lang/en');
        $this->get('/contact')->assertOk()->assertSee('Contact form')->assertSee('Contact us');
    }

    public function test_visitor_sends_a_message_and_every_account_is_notified(): void
    {
        $this->post('/contact', $this->message(['source' => 'assistant']))->assertRedirect('/contact')->assertSessionHas('sent');
        $this->get('/contact')->assertSee('تم إرسال رسالتك بنجاح');
        $this->assertDatabaseHas('contact_messages', ['subject' => 'استفسار عن مسار الروّاد', 'source' => 'assistant', 'read_at' => null]);

        foreach (['admin@kasp.gov.sa', 'editor@kasp.gov.sa', 'viewer@kasp.gov.sa'] as $email) {
            Once::flush(); // عدّادات اللوحة محفوظة لكل طلب
            $this->actingAs($this->user($email))->get('/admin')->assertOk()
                ->assertSee('لديك رسالة جديدة')
                ->assertSee('رسائل التواصل — رسالة جديدة');
        }

        // فتح الصندوق يزيل التنبيه لذلك المستخدم فقط، وفتح الرسالة يعلّمها مقروءة
        $message = ContactMessage::firstOrFail();
        $this->actingAs($this->user('viewer@kasp.gov.sa'))->get("/admin/messages?open={$message->id}")->assertOk()
            ->assertSee('أرغب بمعرفة مواعيد التقديم')->assertSee('sara@example.com');
        $this->assertNotNull($message->fresh()->read_at);
        Once::flush();
        $this->get('/admin')->assertDontSee('رسالة جديدة');
        Once::flush();
        $this->actingAs($this->user('editor@kasp.gov.sa'))->get('/admin')->assertSee('لديك رسالة جديدة');
        $this->assertSame('رسالتان جديدتان', ContactMessage::countLabel(2));
        $this->assertSame('5 رسائل جديدة', ContactMessage::countLabel(5));

        // الحذف للمدير فقط
        $this->delete("/admin/messages/{$message->id}")->assertForbidden();
        $this->actingAs($this->user('admin@kasp.gov.sa'))->delete("/admin/messages/{$message->id}")->assertRedirect();
        $this->assertSame(0, ContactMessage::count());
    }

    public function test_contact_form_validation_and_spam_trap(): void
    {
        $this->post('/contact', $this->message(['phone' => '', 'email' => '']))->assertSessionHasErrors(['phone', 'email']);
        $this->post('/contact', $this->message(['email' => 'not-an-email']))->assertSessionHasErrors('email');
        $this->post('/contact', $this->message(['name' => '', 'subject' => '', 'message' => 'قصير']))->assertSessionHasErrors(['name', 'subject', 'message']);
        $this->post('/contact', $this->message(['email' => '']))->assertSessionDoesntHaveErrors();

        // حقل الاصطياد المخفي: يُعرض نجاح ولا يُحفظ شيء
        $this->post('/contact', $this->message(['website' => 'http://spam.example']))->assertRedirect('/contact');
        $this->assertSame(1, ContactMessage::count());
    }

    public function test_service_rating_is_stored_and_summarised(): void
    {
        $this->postJson('/feedback', ['rating' => 'satisfied', 'source' => 'assistant'])->assertOk();
        $this->postJson('/feedback', ['rating' => 'unsatisfied', 'notes' => 'النتائج قليلة'])->assertOk();
        // الملاحظات إلزامية عند «غير راضٍ ويحتاج إلى تطوير»
        $this->postJson('/feedback', ['rating' => 'needs_improvement'])->assertUnprocessable()->assertJsonValidationErrors('notes');
        $this->postJson('/feedback', ['rating' => 'needs_improvement', 'notes' => 'البحث بطيء', 'source' => 'search'])->assertOk();
        $this->postJson('/feedback', ['rating' => 'excellent'])->assertUnprocessable();

        $this->assertSame(3, ServiceRating::count());
        $this->assertDatabaseHas('service_ratings', ['rating' => 'needs_improvement', 'notes' => 'البحث بطيء', 'source' => 'search']);

        $this->actingAs($this->user('viewer@kasp.gov.sa'))->get('/admin/ratings')->assertOk()
            ->assertSee('غير راضٍ ويحتاج إلى تطوير')->assertSee('البحث بطيء')->assertSee('33%');
        $this->get('/admin/ratings?rating=unsatisfied')->assertOk()->assertSee('النتائج قليلة')->assertDontSee('البحث بطيء');

        // نموذج التقييم والزر العائم في الواجهة
        $this->get('/')->assertSee('نموذج تقييم مستوى الخدمة')->assertSee('قيّم الخدمة');
    }

    public function test_assistant_offers_a_staff_contact_link_and_sahhi_has_verify_button(): void
    {
        $this->get('/')->assertSee('تواصل مع الموظف المسؤول')->assertSee(route('contact.show', ['from' => 'assistant']), false);
        $this->get('/contact?from=assistant')->assertSee('name="source" value="assistant"', false);

        $this->get('/tracks/sahhi')->assertOk()
            ->assertSee('href="https://ru.moe.gov.sa/Search"', false)
            ->assertSee('للتحقق من الجامعة والتخصص اضغط هنا');
        $this->get('/tracks/rowad')->assertDontSee('ru.moe.gov.sa');
        $this->get('/lang/en');
        $this->get('/tracks/sahhi')->assertSee('Verify a university and major');
    }
}
