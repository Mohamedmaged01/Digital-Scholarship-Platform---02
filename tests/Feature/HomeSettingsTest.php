<?php

namespace Tests\Feature;

use App\Models\ContactMethod;
use App\Models\HeroSlide;
use App\Models\SocialLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/** الواجهة الرئيسية (العنوان والنص ورابط التقديم ومعرض الصور) ومواقع التواصل من لوحة التحكم. */
class HomeSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function admin(): User
    {
        return User::where('email', 'admin@kasp.gov.sa')->firstOrFail();
    }

    public function test_defaults_render_on_home_page(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('href="https://uap.sa/"', false)
            ->assertSee('مستقبلُك يبدأ من أعرق')
            ->assertSee('جامعات العالم')
            ->assertSee('aria-label="7 / 7"', false)
            ->assertSee('href="https://x.com/moe_gov_sa"', false)
            ->assertSee('aria-label="YouTube"', false);

        $this->get('/lang/en');
        $this->get('/')->assertSee("Your future starts at the world&#039;s", false)->assertSee('leading universities');
    }

    public function test_admin_edits_hero_texts_and_apply_link_in_both_languages(): void
    {
        $this->actingAs($this->admin())->get('/admin/home')->assertOk()->assertSee('عنوان الواجهة ونصها');

        $this->put('/admin/home/settings', [
            'apply_url' => 'https://uap.sa/apply', 'hero_title_ar' => 'ابدأ رحلتك نحو', 'hero_highlight_ar' => 'التميّز العالمي',
            'hero_text_ar' => 'نص تعريفي جديد للواجهة', 'hero_title_en' => 'Start your journey to', 'hero_highlight_en' => 'global excellence',
            'hero_text_en' => 'A new hero introduction',
        ])->assertRedirect('/admin/home');

        $this->get('/')->assertSee('ابدأ رحلتك نحو')->assertSee('التميّز العالمي')->assertSee('نص تعريفي جديد للواجهة')
            ->assertSee('href="https://uap.sa/apply"', false)->assertDontSee('مستقبلُك يبدأ من أعرق');
        $this->get('/lang/en');
        $this->get('/')->assertSee('Start your journey to')->assertSee('global excellence')->assertSee('A new hero introduction');

        // رابط غير آمن أو حقل مطلوب فارغ لا يُحفظ
        $this->put('/admin/home/settings', ['apply_url' => 'javascript:alert(1)', 'hero_title_ar' => '', 'hero_text_ar' => 'x'])
            ->assertSessionHasErrors(['apply_url', 'hero_title_ar']);
    }

    public function test_admin_uploads_replaces_and_deletes_gallery_images(): void
    {
        $this->actingAs($this->admin());

        $this->post('/admin/home/slides', [
            'image_file' => UploadedFile::fake()->image('campus.jpg', 1600, 1200),
            'title_ar' => 'حرم جامعي', 'title_en' => 'Campus', 'link' => 'tracks/rowad', 'sort' => 2, 'is_active' => 1, 'overlay' => 1,
        ])->assertRedirect('/admin/home');
        $slide = HeroSlide::where('title_ar', 'حرم جامعي')->firstOrFail();
        $this->assertTrue($slide->hasUploadedImage());
        $this->assertFileExists(public_path($slide->image));
        $this->get('/')->assertSee($slide->image, false)->assertSee('aria-label="8 / 8"', false)->assertSee(url('tracks/rowad'), false);

        $old = $slide->image;
        $this->put("/admin/home/slides/{$slide->id}", [
            'image_file' => UploadedFile::fake()->image('new.png', 800, 600), 'title_ar' => 'حرم جامعي', 'sort' => 2, 'is_active' => 1,
        ])->assertRedirect();
        $this->assertFileDoesNotExist(public_path($old));
        $this->assertFileExists(public_path($slide->fresh()->image));

        // شريحة مخفية لا تظهر في الواجهة
        $this->put("/admin/home/slides/{$slide->id}", ['title_ar' => 'حرم جامعي', 'sort' => 2])->assertRedirect();
        $this->get('/')->assertSee('aria-label="7 / 7"', false);

        $path = $slide->fresh()->image;
        $this->delete("/admin/home/slides/{$slide->id}")->assertRedirect();
        $this->assertFileDoesNotExist(public_path($path));
        $this->assertSame(7, HeroSlide::count());

        // صورة مطلوبة عند الإضافة، والملف يجب أن يكون صورة
        $this->post('/admin/home/slides', ['title_ar' => 'بدون صورة'])->assertSessionHasErrors('image_file');
        $this->post('/admin/home/slides', ['title_ar' => 'ملف', 'image_file' => UploadedFile::fake()->create('a.pdf', 10, 'application/pdf')])
            ->assertSessionHasErrors('image_file');
    }

    public function test_viewer_cannot_change_home_page(): void
    {
        $viewer = User::where('email', 'viewer@kasp.gov.sa')->firstOrFail();
        $this->actingAs($viewer)->get('/admin/home')->assertOk();
        $this->put('/admin/home/settings', ['apply_url' => 'https://evil.example', 'hero_title_ar' => 'x', 'hero_text_ar' => 'x'])->assertForbidden();
        $this->post('/admin/home/slides', ['title_ar' => 'x'])->assertForbidden();
    }

    public function test_admin_manages_social_links_shown_in_footer(): void
    {
        $this->actingAs($this->admin());

        $this->post('/admin/social', ['name' => 'LinkedIn', 'platform' => 'linkedin', 'url' => 'https://www.linkedin.com/company/example'])
            ->assertRedirect('/admin/contact');
        $this->get('/')->assertSee('href="https://www.linkedin.com/company/example"', false)->assertSee('aria-label="LinkedIn"', false);

        $this->post('/admin/social', ['name' => 'Bad', 'platform' => 'x', 'url' => 'javascript:alert(1)'])->assertSessionHasErrors('url');
        $this->post('/admin/social', ['name' => 'Bad', 'platform' => 'myspace', 'url' => 'https://example.org'])->assertSessionHasErrors('platform');

        $link = SocialLink::where('platform', 'linkedin')->firstOrFail();
        $this->delete("/admin/social/{$link->id}")->assertRedirect();
        $this->assertSame(2, SocialLink::count());
    }

    public function test_contact_links_are_built_from_the_value(): void
    {
        $this->actingAs($this->admin());

        $this->post('/admin/contact', ['label' => 'البريد الإلكتروني', 'value' => 'info@moe.gov.sa', 'icon' => 'mail'])->assertRedirect();
        $this->assertSame('mailto:info@moe.gov.sa', ContactMethod::where('value', 'info@moe.gov.sa')->value('href'));

        $this->post('/admin/contact', ['label' => 'الموقع', 'value' => 'portal.example.org', 'icon' => 'globe'])->assertRedirect();
        $this->assertSame('https://portal.example.org', ContactMethod::where('value', 'portal.example.org')->value('href'));

        $this->post('/admin/contact', ['label' => 'هاتف', 'value' => '920 000 000', 'icon' => 'phone'])->assertRedirect();
        $this->assertSame('tel:920000000', ContactMethod::where('value', '920 000 000')->value('href'));

        $this->get('/')->assertSee('href="mailto:info@moe.gov.sa"', false);
    }

    /** @var list<string> صور موجودة قبل الاختبار — لا تُحذف */
    private array $existingUploads = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->existingUploads = File::glob(public_path(HeroSlide::UPLOAD_DIR.'/*'));
    }

    protected function tearDown(): void
    {
        File::delete(array_diff(File::glob(public_path(HeroSlide::UPLOAD_DIR.'/*')), $this->existingUploads));
        parent::tearDown();
    }
}
