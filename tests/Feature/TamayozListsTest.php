<?php

namespace Tests\Feature;

use App\Models\GuidelineInstitution;
use App\Models\PathDegree;
use App\Models\PathMajor;
use App\Models\Track;
use App\Models\University;
use App\Support\DefaultContent;
use App\Support\Showcase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** مسار التميز وفق ملف «تخصصات وجامعات التميز — محدث 2026م». */
class TamayozListsTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function tamayoz(): Track
    {
        return Track::where('slug', 'tamayoz')->firstOrFail();
    }

    /** @return array<string, array> key => list */
    private function lists(): array
    {
        return collect(Showcase::guideline($this->tamayoz()))->keyBy('key')->all();
    }

    public function test_majors_and_institution_lists_match_the_file(): void
    {
        $track = $this->tamayoz();
        $lists = $this->lists();

        // التخصصات الـ23 بترتيب الملف — والقانون لم يعد منها
        $this->assertCount(23, $lists);
        $this->assertSame('إدارة أعمال', $track->guideline_lists[0]['nameAr']);
        $this->assertSame('الألعاب الرقمية', $track->guideline_lists[22]['nameAr']);
        $this->assertArrayNotHasKey('m-t-law', $lists);
        $this->assertSame(['bachelor', 'master', 'phd'], $track->degrees);
        $this->assertFalse(PathDegree::where('path_id', $track->id)->where('degree_id', 'professional_doctorate')->exists());
        $this->assertSame(69, PathMajor::where('path_id', $track->id)->count());

        // قوائم خاصة لا تنطبق عليها القائمة العامة
        foreach (['m-mba' => 20, 'm-t-natsec' => 17, 'm-t-crim' => 7, 'm-t-polisci' => 20] as $key => $count) {
            $this->assertSame('own', $lists[$key]['kind']);
            $this->assertCount($count, $lists[$key]['institutions'], $key);
        }

        // القائمة العامة (52) + الإضافية الخاصة بالتخصص (بلا تكرار)
        $general = fn (string $key) => collect($lists[$key]['institutions'])->reject->extra;
        $extra = fn (string $key) => collect($lists[$key]['institutions'])->filter->extra;
        $this->assertCount(52, $general('m-t-anthro'));
        $this->assertCount(0, $extra('m-t-anthro'));
        $this->assertCount(14, $extra('m-t-marine'));
        $this->assertCount(34, $extra('m-t-sports'));
        $this->assertCount(1, $extra('m-econ'));
        $this->assertSame('Erasmus University Rotterdam', $extra('m-econ')->first()['name']);
        $this->assertSame('Bournemouth University', $lists['m-t-hosp']['institutions'][0]['name']);

        // الإعلانات والألعاب الرقمية: جامعات وبرامج محددة
        $this->assertSame('programs', $lists['m-t-games']['kind']);
        $this->assertCount(10, $lists['m-t-games']['institutions']);
        $hku = collect($lists['m-t-games']['institutions'])->firstWhere('name', 'The University of Hong Kong');
        $this->assertCount(4, $hku['programs']);
        $this->assertSame(['Master of Science in Marketing'], $lists['m-t-digadv']['institutions'][0]['programs']);

        // المؤسسات الجديدة والمواقع الرسمية (مع تجاهل أخطاء النسخ في الملف)
        $copenhagen = University::where('slug', 'university-of-copenhagen')->firstOrFail();
        $this->assertSame('الدنمارك', $copenhagen->country);
        $this->assertSame('https://www.ku.dk/english', $copenhagen->website);
        $this->assertStringContainsString('ed.ac.uk', University::where('slug', 'edinburgh')->value('website'));
        $this->assertSame('https://www.uva.nl/en/home', University::where('slug', 'university-of-amsterdam')->value('website'));
    }

    public function test_track_page_shows_major_lists_in_both_languages(): void
    {
        // نصوص بيانات Alpine تُرمَّز داخل JSON.parse('…') في @js
        $js = fn (string $s) => str_replace('\\', '\\\\', trim(json_encode($s), '"'));

        $this->get('/tracks/tamayoz')->assertOk()
            ->assertSee('guidelineLists(', false)
            ->assertSee('وفق قائمة التخصصات والجامعات المعتمدة لمسار التميز (محدثة 2026م)')
            ->assertSee($js('جامعات إضافية خاصة بالتخصص'), false)
            ->assertSee($js('لهذا التخصص قائمة جامعات خاصة به'), false)
            ->assertSee($js('علوم سياسية وعلاقات دولية'), false)
            ->assertSee('BFA in Game Design')
            ->assertSee('www.ku.dk', false)
            ->assertDontSee('trackExplorer(', false);

        // بقية المسارات تبقى على الدليل الاسترشادي
        $this->get('/tracks/research')->assertOk()->assertSee('عرض الدليل (PDF)')->assertDontSee('قائمة مسار التميز');

        $this->get('/lang/en');
        $this->get('/tracks/tamayoz')->assertOk()
            ->assertSee('Tamayoz track list')
            ->assertSee('Additional universities for this major')
            ->assertSee('Political Science and International Relations')
            ->assertSee('Denmark')
            ->assertDontSee($js('إدارة أعمال'), false);
    }

    public function test_matcher_search_and_assistant_use_the_new_majors(): void
    {
        $matcher = Showcase::matcher(Track::orderBy('sort')->get());
        $tamayoz = collect($matcher['tracks'])->firstWhere('id', 'tamayoz');
        $bachelorBusiness = collect($tamayoz['majors'])->where('degree', 'bachelor')->where('field', 'f-bus')->pluck('name')->all();
        $this->assertSame(['إدارة أعمال', 'اقتصاد', 'موارد بشرية'], $bachelorBusiness);
        $this->assertNotContains('professional_doctorate', $tamayoz['degrees']);

        $major = collect($this->getJson('/search?q='.urlencode('علوم بحار'))->json('results'))->firstWhere('groupId', 'majors');
        $this->assertSame(route('tracks.show', 'tamayoz'), $major['href']);
        $uni = collect($this->getJson('/search?q=Copenhagen')->json('results'))->firstWhere('groupId', 'universities');
        $this->assertSame(route('tracks.show', 'tamayoz'), $uni['href']);

        $this->postJson('/assistant/ask', ['q' => 'ما تخصصات مسار التميز؟'])->assertOk()
            ->assertJsonPath('answer', fn ($a) => str_contains($a, 'الألعاب الرقمية') && str_contains($a, '23 تخصصًا'));
    }

    public function test_resets_keep_tamayoz_lists(): void
    {
        $track = $this->tamayoz();
        $rows = GuidelineInstitution::where('path_id', $track->id)->count();
        $total = GuidelineInstitution::count();

        DefaultContent::resetGuideline();
        DefaultContent::resetUniversities();
        DefaultContent::resetRelations();
        $this->assertSame($rows, GuidelineInstitution::where('path_id', $track->id)->count());
        $this->assertSame($total, GuidelineInstitution::count());
        $this->assertSame(69, PathMajor::where('path_id', $track->id)->count());
        $this->assertTrue(University::where('slug', 'ku-leuven')->exists());

        DefaultContent::resetTamayoz();
        $this->assertSame($rows, GuidelineInstitution::where('path_id', $track->id)->count());
    }
}
