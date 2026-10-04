<?php

namespace App\Http\Controllers;

use App\Models\AcademicDegree;
use App\Models\DataSource;
use App\Models\News;
use App\Models\Station;
use App\Models\Track;
use App\Models\University;
use App\Support\SiteSearch;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $structural = $this->structuralCounts();

        return view('home', [
            'tracks' => Track::orderBy('sort')->get(),
            // §47: لم يعد الترتيب بالتصنيف العالمي — لا علاقة له بأهلية الابتعاث.
            'universities' => University::orderBy('name_ar')->get(),
            'stations' => Station::orderBy('sort')->get(),
            'news' => News::ordered()->get(),
            'quickLinks' => SiteSearch::quickLinks(),
            'structural' => $structural,
            'stats' => $this->stats($structural),
        ]);
    }

    /**
     * الأرقام الهيكلية التي حلّت محل الإحصاءات المحذوفة (§K).
     *
     * "130K+ مبتعث" و"57 دولة" و"200+ جامعة" و"21B ريال" حُذفت لأن لا مصدر
     * رسمي محدّثًا يسندها. البديل أرقام يعدّها النظام من بنيته، فهي صحيحة
     * بالتعريف ولا تحتاج تحقّقًا خارجيًا.
     *
     * @return array<string, int>
     */
    private function structuralCounts(): array
    {
        return [
            'tracks' => Track::count(),
            'stations' => Station::count(),
            'degrees' => AcademicDegree::where('status', 'active')->count(),
            'sources' => DataSource::where('status', 'active')->count(),
        ];
    }

    /**
     * @param  array<string, int>  $counts
     * @return list<array<string, mixed>>
     */
    private function stats(array $counts): array
    {
        return [
            [
                'value' => $counts['tracks'],
                'suffix' => '',
                'label' => 'مسارات ابتعاث',
                'note' => 'لكل مسار شروطه وتخصصاته',
            ],
            [
                'value' => $counts['stations'],
                'suffix' => '',
                'label' => 'محطات في خارطة الطريق',
                'note' => 'من التقديم إلى ما بعد التخرج',
            ],
            [
                'value' => $counts['degrees'],
                'suffix' => '',
                'label' => 'درجات علمية معتمدة',
                'note' => 'من الدبلوم إلى الدكتوراه المهنية',
            ],
            [
                'value' => $counts['sources'],
                'suffix' => '',
                'label' => 'مصادر بيانات معتمدة',
                'note' => 'كل معلومة تُنسب إلى مصدرها',
            ],
        ];
    }
}
