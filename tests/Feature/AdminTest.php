<?php

namespace Tests\Feature;

use App\Models\KbEntry;
use App\Models\ManagedFile;
use App\Models\News;
use App\Models\Station;
use App\Models\Track;
use App\Models\UnansweredQuestion;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function admin(): User
    {
        return User::where('email', 'admin@kasp.gov.sa')->firstOrFail();
    }

    private function editor(): User
    {
        return User::where('email', 'editor@kasp.gov.sa')->firstOrFail();
    }

    private function viewer(): User
    {
        return User::where('email', 'viewer@kasp.gov.sa')->firstOrFail();
    }

    /* ---------------- المصادقة ---------------- */

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/login');
        $this->get('/login')->assertOk()->assertSee('الدخول إلى لوحة التحكم');
    }

    public function test_login_with_seeded_credentials(): void
    {
        $this->post('/login', ['email' => 'ADMIN@kasp.gov.sa', 'password' => 'Admin@2026'])->assertRedirect('/admin');
        $this->assertAuthenticatedAs($this->admin());
    }

    public function test_login_rejects_wrong_password_and_inactive_accounts(): void
    {
        $this->post('/login', ['email' => 'admin@kasp.gov.sa', 'password' => 'nope'])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->viewer()->update(['is_active' => false]);
        $this->post('/login', ['email' => 'viewer@kasp.gov.sa', 'password' => 'Viewer@2026'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_deactivated_user_is_logged_out_on_next_request(): void
    {
        $editor = $this->editor();
        $this->actingAs($editor)->get('/admin')->assertOk();

        $editor->update(['is_active' => false]);
        $this->actingAs($editor->fresh())->get('/admin')->assertRedirect('/login');
    }

    /* ---------------- صفحات اللوحة ---------------- */

    public function test_every_admin_page_renders_for_admin(): void
    {
        $this->actingAs($this->admin());
        foreach (['/admin', '/admin/news', '/admin/kb', '/admin/pending', '/admin/universities', '/admin/tracks', '/admin/stations', '/admin/files', '/admin/users'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get('/admin/news?edit='.News::first()->id)->assertOk()->assertSee('تعديل الخبر');
        $this->get('/admin/stations?edit='.Station::first()->id)->assertOk()->assertSee('حفظ المحطة');
        $this->getJson('/admin/search?q='.urlencode('أكسفورد'))->assertOk()->assertJsonPath('hits.0.section', 'الجامعات');
    }

    public function test_viewer_can_browse_but_not_modify(): void
    {
        $this->actingAs($this->viewer());
        $this->get('/admin/news')->assertOk()->assertSee('تتيح الاستعراض فقط');
        $this->get('/admin/users')->assertForbidden();

        $this->post('/admin/news', ['title' => 'خبر تجريبي جديد', 'excerpt' => 'موجز الخبر', 'body' => 'نص الخبر الكامل هنا', 'category' => 'announcement'])->assertForbidden();
        $this->delete('/admin/news/'.News::first()->id)->assertForbidden();
    }

    public function test_editor_can_create_and_edit_but_not_delete(): void
    {
        $this->actingAs($this->editor());

        $this->post('/admin/news', [
            'title' => 'خبر تجريبي جديد', 'excerpt' => 'موجز الخبر', 'body' => 'نص الخبر الكامل هنا',
            'category' => 'meetings', 'pinned' => '1',
        ])->assertRedirect('/admin/news');

        $news = News::where('title', 'خبر تجريبي جديد')->firstOrFail();
        $this->assertTrue($news->pinned);
        $this->assertSame('الإدارة العامة للابتعاث', $news->author);

        $this->put("/admin/news/{$news->id}", [
            'title' => 'خبر تجريبي معدّل', 'excerpt' => 'موجز الخبر', 'body' => 'نص الخبر الكامل هنا', 'category' => 'meetings',
        ])->assertRedirect();
        $this->assertFalse($news->fresh()->pinned);

        $this->delete("/admin/news/{$news->id}")->assertForbidden();
        $this->get('/admin/users')->assertForbidden();
    }

    /* ---------------- المحتوى ---------------- */

    public function test_news_validation_and_pin_toggle(): void
    {
        $this->actingAs($this->admin());
        $this->post('/admin/news', ['title' => 'قصير', 'category' => 'x'])->assertSessionHasErrors(['title', 'excerpt', 'body', 'category']);

        $news = News::where('pinned', false)->first();
        $this->patch("/admin/news/{$news->id}/pin")->assertRedirect();
        $this->assertTrue($news->fresh()->pinned);
    }

    public function test_knowledge_base_crud_and_core_entries_are_read_only(): void
    {
        $this->actingAs($this->admin());

        $this->post('/admin/kb', ['question' => 'هل يوجد سكن جامعي؟', 'answer' => 'نعم يتوفر سكن مدعوم', 'keywords' => 'سكن، جامعي, اقامة'])->assertRedirect();
        $entry = KbEntry::where('question', 'هل يوجد سكن جامعي؟')->firstOrFail();
        $this->assertSame(['سكن', 'جامعي', 'اقامة'], $entry->keywords);
        $this->assertTrue($entry->is_custom);

        // يصبح متاحًا للمساعد فورًا
        $this->postJson('/assistant/ask', ['q' => 'هل يتوفر سكن جامعي؟'])->assertJsonPath('answer', 'نعم يتوفر سكن مدعوم');

        $core = KbEntry::where('is_custom', false)->first();
        $this->put("/admin/kb/{$core->id}", ['question' => 'تعديل', 'answer' => 'تعديل'])->assertForbidden();
        $this->delete("/admin/kb/{$core->id}")->assertForbidden();

        $this->delete("/admin/kb/{$entry->id}")->assertRedirect();
        $this->assertModelMissing($entry);
    }

    public function test_answering_pending_question_moves_it_to_kb_form(): void
    {
        $this->actingAs($this->editor());
        $q = UnansweredQuestion::create(['question' => 'سؤال بلا إجابة', 'first_asked_at' => now(), 'last_asked_at' => now()]);

        $this->post("/admin/pending/{$q->id}/answer")->assertRedirect(route('admin.kb.index', ['prefill' => 'سؤال بلا إجابة']));
        $this->assertModelMissing($q);
        $this->get(route('admin.kb.index', ['prefill' => 'سؤال بلا إجابة']))->assertSee('value="سؤال بلا إجابة"', false);
    }

    public function test_university_track_and_station_management(): void
    {
        $this->actingAs($this->admin());

        $this->post('/admin/universities', ['name_en' => 'KAUST', 'name_ar' => 'جامعة الملك عبدالله', 'region' => 'asia', 'fields' => 'طاقة، مياه'])->assertRedirect();
        $uni = University::where('name_en', 'KAUST')->firstOrFail();
        $this->assertSame(999, $uni->rank);
        $this->assertSame(['طاقة', 'مياه'], $uni->fields);

        $this->post('/admin/tracks', [
            'name' => 'مسار تجريبي', 'description' => 'وصف المسار التجريبي', 'icon' => 'rocket',
            'degrees' => ['phd', 'bachelor'], 'perks' => "ميزة أولى\nميزة ثانية",
        ])->assertRedirect();
        $track = Track::where('name', 'مسار تجريبي')->firstOrFail();
        $this->assertSame(['bachelor', 'phd'], $track->degrees);
        $this->assertSame(['ميزة أولى', 'ميزة ثانية'], $track->perks);
        $this->assertSame('07', $track->code);

        $station = Station::first();
        $this->put("/admin/stations/{$station->id}", ['title' => 'محطة معدلة', 'description' => 'وصف جديد', 'points' => 'أ، ب'])->assertRedirect();
        $this->assertSame(['أ', 'ب'], $station->fresh()->points);

        $this->get('/')->assertSee('مسار تجريبي')->assertSee('جامعة الملك عبدالله')->assertSee('محطة معدلة');

        $this->post('/admin/tracks/reset')->assertRedirect();
        $this->assertSame(6, Track::count());
    }

    public function test_file_upload_and_delete(): void
    {
        Storage::fake('public');
        $this->actingAs($this->editor());

        $this->post('/admin/files', ['files' => [UploadedFile::fake()->image('logo.png'), UploadedFile::fake()->create('guide.pdf', 50, 'application/pdf')]])
            ->assertRedirect('/admin/files');

        $this->assertSame(2, ManagedFile::count());
        $file = ManagedFile::where('name', 'logo.png')->firstOrFail();
        Storage::disk('public')->assertExists($file->path);
        $this->get('/admin/files')->assertSee('logo.png');

        $this->post('/admin/files', ['files' => [UploadedFile::fake()->create('evil.exe', 10)]])->assertSessionHasErrors();

        $this->delete("/admin/files/{$file->id}")->assertForbidden(); // المحرّر لا يحذف
        $this->actingAs($this->admin())->delete("/admin/files/{$file->id}")->assertRedirect();
        Storage::disk('public')->assertMissing($file->path);
    }

    /* ---------------- المستخدمون ---------------- */

    public function test_admin_manages_users_with_last_admin_protection(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        $this->post('/admin/users', ['name' => 'نورة العبدالله', 'email' => 'Noura@kasp.gov.sa', 'role' => 'editor', 'password' => 'Secret@123', 'is_active' => '1'])->assertRedirect();
        $noura = User::where('email', 'noura@kasp.gov.sa')->firstOrFail();
        $this->assertSame('editor', $noura->role);

        // بريد مكرر
        $this->post('/admin/users', ['name' => 'مكرر', 'email' => 'noura@kasp.gov.sa', 'role' => 'viewer', 'password' => 'Secret@123'])->assertSessionHasErrors('email');

        // تعديل بدون تغيير كلمة المرور
        $hash = $noura->password;
        $this->put("/admin/users/{$noura->id}", ['name' => 'نورة', 'email' => 'noura@kasp.gov.sa', 'role' => 'viewer', 'password' => '', 'is_active' => '1'])->assertRedirect();
        $this->assertSame($hash, $noura->fresh()->password);
        $this->assertSame('viewer', $noura->fresh()->role);

        // لا يمكن للمدير خفض صلاحيته أو حذف نفسه
        $this->put("/admin/users/{$admin->id}", ['name' => $admin->name, 'email' => $admin->email, 'role' => 'viewer', 'is_active' => '1'])->assertSessionHasErrors('role');
        $this->delete("/admin/users/{$admin->id}")->assertSessionHasErrors('role');
        $this->assertModelExists($admin);

        $this->patch("/admin/users/{$noura->id}/toggle")->assertRedirect();
        $this->assertFalse($noura->fresh()->is_active);

        $this->delete("/admin/users/{$noura->id}")->assertRedirect();
        $this->assertModelMissing($noura);
    }

    public function test_admin_cannot_deactivate_self_but_can_deactivate_another_admin(): void
    {
        $other = User::factory()->role('admin')->create();
        $this->actingAs($other);

        $this->patch('/admin/users/'.$this->admin()->id.'/toggle')->assertSessionHasNoErrors();
        $this->assertFalse($this->admin()->is_active);

        // إرسال النموذج دون is_active يعني إيقاف الذات — ممنوع
        $this->put("/admin/users/{$other->id}", ['name' => $other->name, 'email' => $other->email, 'role' => 'admin'])
            ->assertSessionHasErrors('role');
        $this->patch("/admin/users/{$other->id}/toggle")->assertSessionHasErrors('role');
        $this->assertTrue($other->fresh()->is_active);
    }
}
