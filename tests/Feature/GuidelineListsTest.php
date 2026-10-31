<?php

namespace Tests\Feature;

use App\Models\GuidelineInstitution;
use App\Models\Track;
use App\Models\University;
use App\Support\DefaultContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** قوائم الدليل الاسترشادي لترتيب قوائم الجامعات حسب المجالات 2026-2027 (ملاحظتا 32 و33). */
class GuidelineListsTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function links(string $slug, ?string $field = null): int
    {
        return GuidelineInstitution::where('path_id', Track::where('slug', $slug)->value('id'))
            ->when($field, fn ($q) => $q->where('field_key', $field))->count();
    }

    public function test_guideline_lists_are_imported_per_track_and_field(): void
    {
        $this->assertSame(30, $this->links('rowad'));
        $this->assertSame(160, $this->links('research', 'nat'));
        $this->assertSame(70, $this->links('emdad', 'srv'));
        $this->assertSame(0, $this->links('sahhi'));

        $research = Track::where('slug', 'research')->first();
        $this->assertSame(['nat', 'ict', 'eng', 'agr', 'soc'], array_column($research->guideline_lists, 'key'));

        // المؤسسة الموجودة مسبقًا تحتفظ باسمها العربي وتأخذ اسم الدليل الإنجليزي
        $mit = University::where('slug', 'mit')->first();
        $this->assertSame('Massachusetts Institute of Technology (MIT)', $mit->name_en);
        $this->assertSame('معهد ماساتشوستس للتقنية', $mit->name_ar);
        $this->assertTrue($mit->isVerified());
    }

    public function test_track_pages_show_guideline_lists_with_card_notes(): void
    {
        $this->get('/tracks/rowad')->assertOk()
            ->assertSee('guidelineLists(', false)
            ->assertSee('Harvard University')
            ->assertSee('Washington University in St. Louis')
            ->assertDontSee('غير منشورة في المنصة حاليًا');

        $this->get('/tracks/emdad')->assertOk()
            ->assertSee('Gallaudet University')
            ->assertSee('Judicial studies')
            ->assertSee('DigiPen Institute of Technology');

        // مسار بلا قوائم منشورة يبقي التنويه
        $this->get('/tracks/sahhi')->assertOk()->assertSee('غير منشورة في المنصة حاليًا');

        $this->get('/lang/en');
        $this->get('/tracks/emdad')->assertOk()
            ->assertSee('Majors in this field')
            ->assertSee('For persons with hearing disabilities')
            ->assertSee("Master's degree only");
    }

    public function test_search_finds_guideline_universities_and_majors(): void
    {
        $this->getJson('/search?q=Gallaudet')->assertOk()
            ->assertJsonPath('results.0.groupId', 'universities')
            ->assertJsonPath('results.0.href', route('tracks.show', 'emdad'));

        $majors = collect($this->getJson('/search?q='.urlencode('علم الوراثة'))->json('results'))->where('groupId', 'majors');
        $this->assertNotEmpty($majors);
        $this->assertContains($majors->first()['href'], [route('tracks.show', 'research'), route('tracks.show', 'emdad')]);
    }

    public function test_resets_keep_guideline_lists_intact(): void
    {
        $before = GuidelineInstitution::count();

        DefaultContent::resetUniversities();
        $this->assertSame($before, GuidelineInstitution::count());

        DefaultContent::resetGuideline();
        $this->assertSame($before, GuidelineInstitution::count());
        $this->assertSame(1, University::where('slug', 'gallaudet-university')->count());
    }
}
