<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\GuidelineInstitution;
use App\Models\User;
use App\Support\DatabaseUpdates;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Once;
use Tests\TestCase;

/** الاستضافة لا تتيح الأوامر: اللوحة تعمل قبل تطبيق التحديثات، والمدير يطبّقها بزر. */
class DatabaseUpdatesTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function user(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    public function test_admin_applies_pending_updates_from_the_dashboard(): void
    {
        $this->assertSame([], DatabaseUpdates::pending());
        $this->actingAs($this->user('admin@kasp.gov.sa'))->get('/admin')->assertOk()->assertDontSee('قاعدة البيانات تحتاج إلى تحديث');

        // كما على الخادم: الكود الجديد منشور وآخر تحديثين لم يُطبَّقا
        Artisan::call('migrate:rollback', ['--step' => 2]);
        $this->assertFalse(Schema::hasTable('contact_messages'));
        $this->assertSame([
            '2026_11_03_000100_contact_messages_ratings_track_links',
            '2026_11_04_000100_tamayoz_majors_universities',
        ], DatabaseUpdates::pending());

        // اللوحة لا تتعطل؛ المطّلع يرى التنبيه بلا زر ولا يملك التطبيق
        Once::flush();
        $this->actingAs($this->user('viewer@kasp.gov.sa'))->get('/admin')->assertOk()
            ->assertSee('قاعدة البيانات تحتاج إلى تحديث (2 تحديث معلّقة)')
            ->assertSee('تواصل مع مدير النظام لتطبيقها')
            ->assertDontSee(route('admin.system.migrate'));
        $this->post('/admin/system/migrate')->assertForbidden();

        Once::flush();
        $this->actingAs($this->user('admin@kasp.gov.sa'))->get('/admin')->assertOk()
            ->assertSee('تطبيق التحديثات الآن')
            ->assertSee(route('admin.system.migrate'));

        $this->from('/admin')->post('/admin/system/migrate')->assertRedirect('/admin')
            ->assertSessionHas('status', 'تم تطبيق 2 من تحديثات قاعدة البيانات بنجاح.');
        $this->assertSame([], DatabaseUpdates::pending());
        $this->assertTrue(Schema::hasTable('contact_messages'));
        $this->assertGreaterThan(1000, GuidelineInstitution::where('extra', false)->count());
        $this->assertDatabaseHas('audit_log', ['action' => 'migrate', 'admin_email' => 'admin@kasp.gov.sa']);

        // كل شيء يعمل بعد التطبيق
        $this->post('/contact', ['name' => 'سارة', 'phone' => '0551234567', 'subject' => 'استفسار', 'message' => 'أرغب بمعرفة مواعيد التقديم.'])->assertRedirect();
        $this->assertSame(1, ContactMessage::count());
        $this->get('/tracks/tamayoz')->assertOk()->assertSee('guidelineLists(', false);

        // لا شيء معلّق: الزر لا يفعل شيئًا
        $this->from('/admin')->post('/admin/system/migrate')->assertSessionHas('status', 'قاعدة البيانات محدّثة — لا توجد تحديثات معلّقة.');
    }
}
