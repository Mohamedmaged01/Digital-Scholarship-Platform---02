<?php

namespace App\Support;

use App\Models\GuidelineInstitution;
use App\Models\KbEntry;
use App\Models\Major;
use App\Models\News;
use App\Models\PathMajor;
use App\Models\PathMajorInstitution;
use App\Models\Program;
use App\Models\Station;
use App\Models\Track;
use App\Models\University;

/**
 * البحث الفوري في كل محتوى الواجهة العامة (Ctrl+K).
 */
class SiteSearch
{
    private const GROUP_ORDER = ['sections', 'tracks', 'majors', 'waed', 'universities', 'news', 'journey', 'faq', 'kb'];

    private const SECTION_KEYWORDS = [
        '#home' => 'رئيسية بداية هيرو واجهة',
        '#tracks' => 'مسار مسارات رواد امداد تميز بحث تطوير صحي واعد استراتيجية ابتعاث',
        '#matcher' => 'مطابقة توصية توافق اسئلة توجيه ضوابط',
        '#news' => 'اخبار اعلانات اعلام مستجدات',
        '#journey' => 'خارطه طريق محطات رحله خطوات تقديم مستندات ترشيح',
        '#faq' => 'اسئله شائعه اجابات استفسارات',
        '/contact' => 'اتصل بنا تواصل راسلنا نموذج رساله بريد هاتف موظف دعم',
    ];

    private static function score(string $qNorm, array $qTokens, string $text): int
    {
        $t = ArabicText::normalize($text);
        if ($t === '') {
            return 0;
        }
        if (mb_strlen($qNorm) >= 3 && str_contains($t, $qNorm)) {
            return 120;
        }
        $s = 0;
        foreach ($qTokens as $tok) {
            if (str_contains($t, $tok)) {
                $s += 10;
            }
        }

        return $s;
    }

    private static function excerpt(string $s, int $len = 80): string
    {
        return mb_strlen($s) > $len ? mb_substr($s, 0, $len).'…' : $s;
    }

    /** @return list<array{id: string, groupId: string, group: string, title: string, sub: string, href: string}> */
    public static function search(string $query): array
    {
        $qNorm = ArabicText::normalize($query);
        if (mb_strlen($qNorm) < 2) {
            return [];
        }
        $qTokens = array_values(array_unique(array_filter(explode(' ', $qNorm), fn ($t) => mb_strlen($t) > 1)));

        $candidates = [];
        $add = function (string $id, string $groupId, string $group, string $title, string $sub, string $href, string $text) use (&$candidates) {
            $candidates[] = compact('id', 'groupId', 'group', 'title', 'sub', 'href') + ['_text' => $text];
        };

        foreach (SiteNav::links() as $l) {
            $add("sec-{$l['href']}", 'sections', __('أقسام المنصة'), $l['label'], __('الانتقال إلى قسم :section', ['section' => $l['label']]), $l['home'],
                $l['label'].' '.(self::SECTION_KEYWORDS[$l['href']] ?? ''));
        }
        $tracks = Track::orderBy('sort')->get()->keyBy('id');
        $trackUrl = fn (Track $t) => $t->url();

        foreach ($tracks as $t) {
            $add("track-{$t->id}", 'tracks', __('مسارات الابتعاث'), $t->tr('name'), "{$t->tr('badge')} • {$t->applicationStatusLabel()}", $trackUrl($t),
                "{$t->name} مسار {$t->en_subtitle} {$t->badge} {$t->ranking} ".implode(' ', $t->fields));
        }
        foreach (Program::waed()->orderBy('sort')->get() as $p) {
            $add("waed-{$p->id}", 'waed', __('برامج واعد'), $p->name_ar, "{$p->company_ar} • {$p->statusLabel()}", route('waed.show', $p),
                "{$p->name_ar} {$p->name_en} {$p->company_ar} {$p->major_name} {$p->sector} برنامج واعد");
        }

        // التخصص والجامعة يقودان إلى صفحة أول مسار يرتبطان به
        $majorTracks = PathMajor::select('major_id', 'path_id')->get()->groupBy('major_id');
        foreach (Major::orderBy('sort')->get() as $m) {
            $paths = ($majorTracks[$m->id] ?? collect())->pluck('path_id')->unique();
            if ($paths->isEmpty()) {
                continue;
            }
            $names = $paths->map(fn ($id) => $tracks[$id]?->name)->filter()->implode('، ');
            $add("major-{$m->id}", 'majors', __('التخصصات'), $m->name_ar, "متاح في: {$names}", $trackUrl($tracks[$paths->first()]),
                "{$m->name_ar} {$m->name_en} تخصص");
        }
        // تخصصات قوائم الدليل الاسترشادي (البحث والتطوير وإمداد)
        $guideMajors = [];
        foreach ($tracks as $t) {
            foreach ($t->guideline_lists ?? [] as $l) {
                foreach ($l['majors'] as $gm) {
                    $guideMajors["{$gm['ar']}|{$gm['en']}"][$t->id] = $t;
                }
            }
        }
        foreach ($guideMajors as $key => $paths) {
            [$ar, $en] = explode('|', $key, 2);
            $names = collect($paths)->map(fn (Track $t) => $t->name)->implode('، ');
            $add('gmajor-'.md5($key), 'majors', __('التخصصات'), $ar, "متاح في: {$names}", $trackUrl(reset($paths)), "{$ar} {$en} تخصص");
        }

        $uniTracks = PathMajorInstitution::select('institution_id', 'path_id')->get()
            ->concat(GuidelineInstitution::select('institution_id', 'path_id')->get())
            ->groupBy('institution_id');
        foreach (University::orderBy('name_ar')->get() as $u) {
            $firstPath = ($uniTracks[$u->id] ?? collect())->pluck('path_id')->first();
            // المؤسسة غير المرتبطة بمسار لا تظهر (لا تُعرض جامعات خارج الدليل الاسترشادي)
            if (! $firstPath || ! isset($tracks[$firstPath])) {
                continue;
            }
            $add("uni-{$u->id}", 'universities', __('المؤسسات التعليمية'), $u->name_ar, collect([$u->city, $u->country])->filter()->implode('، '),
                $trackUrl($tracks[$firstPath]),
                "{$u->name_ar} {$u->name_en} {$u->city} {$u->country} ".implode(' ', $u->fields).' جامعة');
        }
        foreach (News::ordered()->get() as $n) {
            $add("news-{$n->id}", 'news', __('الأخبار والإعلانات'), $n->title, "{$n->author} • {$n->published_on->format('Y-m-d')}", '#news',
                "{$n->title} {$n->excerpt} {$n->author} خبر اعلان");
        }
        foreach (Station::orderBy('sort')->get() as $s) {
            $add("station-{$s->id}", 'journey', __('خارطة الطريق'), "المحطة {$s->code}: {$s->title}", implode(' • ', $s->points), '#journey',
                "{$s->title} {$s->description} ".implode(' ', $s->points).' محطة خطوة');
        }
        foreach (config('kasp.faqs') as $i => $f) {
            $add("faq-{$i}", 'faq', __('الأسئلة الشائعة'), $f['q'], self::excerpt($f['a']), '#faq', "{$f['q']} {$f['a']}");
        }
        foreach (KbEntry::all() as $k) {
            $add("kb-{$k->id}", 'kb', __('أسئلة المساعد الذكي'), $k->question, self::excerpt($k->answer), '#chat',
                "{$k->question} ".implode(' ', $k->keywords).' سؤال مساعد');
        }

        $scored = collect($candidates)
            ->map(function ($c) use ($qNorm, $qTokens) {
                $c['score'] = self::score($qNorm, $qTokens, $c['_text']);
                unset($c['_text']);

                return $c;
            })
            ->filter(fn ($c) => $c['score'] > 0)
            ->sortByDesc('score')
            ->values();

        // حد أقصى 4 لكل مجموعة ثم 16 للنتائج كلها
        $perGroup = [];

        return $scored
            ->filter(function ($r) use (&$perGroup) {
                $perGroup[$r['groupId']] = ($perGroup[$r['groupId']] ?? 0) + 1;

                return $perGroup[$r['groupId']] <= 4;
            })
            ->take(16)
            ->sort(fn ($a, $b) => (array_search($a['groupId'], self::GROUP_ORDER) <=> array_search($b['groupId'], self::GROUP_ORDER))
                ?: ($b['score'] <=> $a['score']))
            ->map(fn ($r) => array_diff_key($r, ['score' => true]))
            ->values()
            ->all();
    }

    /** روابط سريعة تظهر قبل الكتابة */
    public static function quickLinks(): array
    {
        $news = News::ordered()->take(2)->get()->map(fn (News $n) => [
            'id' => "quick-{$n->id}", 'groupId' => 'news', 'group' => __('أحدث الأخبار'),
            'title' => $n->title, 'sub' => $n->published_on->format('Y-m-d'), 'href' => '#news',
        ])->all();

        return [
            ['id' => 'q1', 'groupId' => 'sections', 'group' => __('روابط سريعة'), 'title' => __('مسارات الابتعاث'), 'sub' => __('الشروط والضوابط لكل مسار'), 'href' => '#tracks'],
            ['id' => 'q2', 'groupId' => 'sections', 'group' => __('روابط سريعة'), 'title' => __('محرك التوجيه'), 'sub' => __('سؤالان فقط'), 'href' => '#matcher'],
            ['id' => 'q3', 'groupId' => 'waed', 'group' => __('روابط سريعة'), 'title' => __('برامج مسار واعد'), 'sub' => __('ابتعاث مبتدئ بالتوظيف'), 'href' => route('waed.index')],
            ['id' => 'q4', 'groupId' => 'sections', 'group' => __('روابط سريعة'), 'title' => __('رحلة المستفيد'), 'sub' => __('من القبول الجامعي حتى مباشرة الدراسة'), 'href' => '#journey'],
            ...$news,
        ];
    }
}
