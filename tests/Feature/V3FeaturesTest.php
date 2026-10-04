<?php

namespace Tests\Feature;

use App\Models\ContactMethod;
use App\Models\Guide;
use App\Models\LegalPage;
use App\Models\Major;
use App\Models\PathMajor;
use App\Models\PathMajorInstitution;
use App\Models\Program;
use App\Models\Statistic;
use App\Models\Track;
use App\Models\University;
use App\Models\User;
use App\Support\DefaultContent;
use App\Support\Showcase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class V3FeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function admin(): User
    {
        return User::where('email', 'admin@kasp.gov.sa')->firstOrFail();
    }

    /* ---------------- البيانات ---------------- */

    public function test_seed_imports_the_full_v3_relational_dataset(): void
    {
        $this->assertSame(37, University::count());
        $this->assertSame(54, Major::count());
        $this->assertSame(64, PathMajor::count());
        $this->assertSame(197, PathMajorInstitution::count());
        $this->assertSame(7, Program::waed()->count());
        $this->assertSame(6, Guide::count());
        $this->assertSame(['privacy', 'terms'], LegalPage::orderBy('sort')->pluck('slug')->all());
    }

    public function test_resetting_tracks_keeps_their_relations(): void
    {
        $before = PathMajorInstitution::count();
        DefaultContent::resetTracks();
        DefaultContent::resetUniversities();

        $this->assertSame($before, PathMajorInstitution::count());
    }

    /* ---------------- صفحة المسار ---------------- */

    public function test_track_page_exposes_explorer_tree_and_constraints(): void
    {
        $tamayoz = Track::where('slug', 'tamayoz')->firstOrFail();
        $explorer = Showcase::explorer($tamayoz);

        $phd = collect($explorer['degrees'])->firstWhere('id', 'professional_doctorate');
        $law = collect($phd['majors'])->firstWhere('id', 'm-t-law');
        $this->assertCount(7, $law['universities']);
        $this->assertSame(['professional_doctorate'], $explorer['constraints']['m-t-law'][0]['allowedIds']);

        $this->get('/tracks/tamayoz')
            ->assertOk()
            ->assertSee('مسار التميز')
            ->assertSee('الجامعات والتخصصات المتاحة لهذا المسار')
            ->assertSee('الدليل الاسترشادي — مسار التميز 2026–2027');
    }

    public function test_waed_track_redirects_to_programs_page(): void
    {
        $this->get('/tracks/waed')->assertRedirect(route('waed.index'));
        $this->get('/tracks/unknown')->assertNotFound();
    }

    /* ---------------- واعد ---------------- */

    public function test_waed_page_separates_current_and_past_programs(): void
    {
        $this->get('/waed')
            ->assertOk()
            ->assertSee('برنامج تطوير كفاءات الذكاء الاصطناعي')
            ->assertSee('البرامج السابقة');

        $this->get('/waed/w-ai-01')
            ->assertOk()
            ->assertSee('IELTS')
            ->assertSee('الهيئة السعودية للذكاء الاصطناعي (SDAIA)')
            ->assertSee('لا يتجاوز 35 عاماً');
    }

    public function test_admin_creates_waed_program_with_repeaters(): void
    {
        $this->actingAs($this->admin())->post('/admin/waed', [
            'name_ar' => 'برنامج تجريبي للطاقة',
            'name_en' => 'Test Energy Program',
            'company_ar' => 'جهة تجريبية',
            'program_type' => 'scholarship',
            'degree_id' => 'master',
            'sector' => 'الطاقة والاستدامة',
            'application_status' => 'open',
            'required_majors' => "الهندسة الكيميائية\nهندسة الطاقة",
            'gpa_min' => '3.5',
            'languages' => [['test' => 'IELTS', 'score' => '6.5', 'notes' => ''], ['test' => '', 'score' => '', 'notes' => '']],
            'conditions' => [['category' => 'general', 'text' => 'الجنسية السعودية', 'required' => '1'], ['category' => 'other', 'text' => 'شرط اختياري', 'required' => '0']],
        ])->assertRedirect('/admin/waed');

        $p = Program::where('slug', 'test-energy-program')->firstOrFail();
        $this->assertSame(['الهندسة الكيميائية', 'هندسة الطاقة'], $p->required_majors);
        $this->assertCount(1, $p->languages);
        $this->assertSame(['min' => '3.5', 'scale' => '5.0', 'notes' => ''], $p->gpa);
        $this->assertTrue($p->conditions[0]['required']);
        $this->assertFalse($p->conditions[1]['required']);
        $this->get('/waed')->assertSee('برنامج تجريبي للطاقة');
    }

    /* ---------------- إدارة العلاقات ---------------- */

    public function test_relations_manager_updates_majors_and_institutions(): void
    {
        $track = Track::where('slug', 'emdad')->firstOrFail();
        $this->actingAs($this->admin());
        $this->get('/admin/relations?track=emdad')->assertOk()->assertSee('مسار إمداد');

        // إبقاء درجة البكالوريوس وتخصص واحد فقط
        $this->put('/admin/relations/emdad/majors', [
            'degrees' => ['bachelor'],
            'majors' => ['bachelor' => ['m-scm']],
        ])->assertRedirect();
        $this->assertSame(['bachelor|m-scm'], PathMajor::where('path_id', $track->id)->get()->map(fn ($p) => "{$p->degree_id}|{$p->major_id}")->all());
        $this->assertSame(0, PathMajorInstitution::where('path_id', $track->id)->where('major_id', '!=', 'm-scm')->count());

        $mit = University::where('slug', 'mit')->value('id');
        $this->put('/admin/relations/emdad/institutions', [
            'status' => 'verified',
            'links' => ['bachelor|m-scm' => [$mit]],
        ])->assertRedirect();
        $link = PathMajorInstitution::where('path_id', $track->id)->sole();
        $this->assertSame($mit, $link->institution_id);
        $this->assertSame('verified', $link->status);
    }

    public function test_relations_manager_is_admin_only(): void
    {
        $editor = User::where('email', 'editor@kasp.gov.sa')->firstOrFail();
        $this->actingAs($editor)->get('/admin/relations')->assertForbidden();
    }

    /* ---------------- الأدلة والأرقام والاتصال والصفحات ---------------- */

    public function test_new_guide_becomes_the_only_current_one(): void
    {
        $track = Track::where('slug', 'rowad')->firstOrFail();
        $this->actingAs($this->admin())->post('/admin/guides', [
            'path_id' => $track->id, 'title_ar' => 'دليل الروّاد 2027', 'guide_type' => 'link',
            'url' => 'https://example.com/guide.pdf', 'status' => 'active', 'is_current' => '1',
        ])->assertRedirect();

        $this->assertSame(['دليل الروّاد 2027'], Guide::where('path_id', $track->id)->where('is_current', true)->pluck('title_ar')->all());
        $this->get('/tracks/rowad')->assertSee('دليل الروّاد 2027')->assertSee('عرض الإصدارات السابقة');
    }

    public function test_managed_statistics_replace_structural_fallback(): void
    {
        $this->get('/')->assertSee('مسارات ابتعاث');

        $this->actingAs($this->admin())->post('/admin/statistics', ['label_ar' => 'رقم موثّق للاختبار', 'value' => 42, 'location' => 'general'])
            ->assertSessionHasErrors('source_id');

        $this->post('/admin/statistics', ['label_ar' => 'رقم موثّق للاختبار', 'value' => 42, 'location' => 'general', 'source_id' => 'src-moe'])
            ->assertRedirect();
        $this->assertSame(1, Statistic::count());
        $this->get('/')->assertSee('رقم موثّق للاختبار');
    }

    public function test_contact_methods_reject_unsafe_links_and_render_in_footer(): void
    {
        $this->actingAs($this->admin());
        $this->post('/admin/contact', ['label' => 'رابط خطير', 'value' => 'x', 'href' => 'javascript:alert(1)', 'icon' => 'phone'])
            ->assertSessionHasErrors('href');

        $this->post('/admin/contact', ['label' => 'هاتف الطوارئ', 'value' => '911', 'href' => 'tel:911', 'icon' => 'phone'])->assertRedirect();
        $this->assertSame(4, ContactMethod::count());
        $this->get('/')->assertSee('tel:911', false);
    }

    public function test_legal_pages_escape_html_and_link_from_footer(): void
    {
        $page = LegalPage::where('slug', 'privacy')->firstOrFail();
        $this->actingAs($this->admin())->put("/admin/pages/{$page->id}", [
            'title_ar' => 'سياسة الخصوصية', 'slug' => 'privacy',
            'content' => "**عنوان عريض**\n\nفقرة فيها <script>alert(1)</script> نص.",
        ])->assertRedirect();

        $this->get('/pages/privacy')
            ->assertOk()
            ->assertSee('<strong class="text-ink">عنوان عريض</strong>', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;', false);

        $this->get('/')->assertSee(route('pages.show', 'terms'), false);
    }

    public function test_all_new_admin_pages_render(): void
    {
        $this->actingAs($this->admin());
        foreach (['/admin/guides', '/admin/waed', '/admin/relations', '/admin/statistics', '/admin/contact', '/admin/pages', '/admin/pages?edit=new'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get('/admin/waed?edit='.Program::first()->id)->assertOk()->assertSee('تعديل البرنامج');
    }
}
