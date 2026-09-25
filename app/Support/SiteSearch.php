<?php

namespace App\Support;

use App\Models\KbEntry;
use App\Models\News;
use App\Models\Station;
use App\Models\Track;
use App\Models\University;

/**
 * البحث الفوري في كل محتوى الواجهة العامة (Ctrl+K).
 */
class SiteSearch
{
    private const GROUP_ORDER = ['sections', 'tracks', 'universities', 'news', 'journey', 'faq', 'kb'];

    private const SECTION_KEYWORDS = [
        '#home' => 'رئيسية بداية هيرو واجهة',
        '#tracks' => 'مسار مسارات رواد امداد تميز بحث تطوير صحي واعد استراتيجية ابتعاث',
        '#matcher' => 'ذكاء اصطناعي مطابقة توصية توافق اسئلة اختبار توجيه',
        '#universities' => 'جامعه جامعات تصنيف عوالم قبول دول مدن تخصصات',
        '#news' => 'اخبار اعلانات اعلام مستجدات شراكات ملتقيات قبول',
        '#journey' => 'خارطه طريق محطات رحله خطوات تقديم مستندات ترشيح',
        '#assistant' => 'مساعد ذكي دردشه اسئله اجابات محاوره تواصل لوحه',
        '#faq' => 'اسئله شائعه اجابات استفسارات',
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

        foreach (config('kasp.nav') as $l) {
            $add("sec-{$l['href']}", 'sections', 'أقسام المنصة', $l['label'], "الانتقال إلى قسم {$l['label']}", $l['href'],
                $l['label'].' '.(self::SECTION_KEYWORDS[$l['href']] ?? ''));
        }
        foreach (Track::orderBy('sort')->get() as $t) {
            $add("track-{$t->id}", 'tracks', 'مسارات الابتعاث', $t->name, "{$t->badge} • معدل {$t->gpa}", '#tracks',
                "{$t->name} مسار {$t->en_subtitle} {$t->badge} {$t->ranking} ".implode(' ', $t->fields));
        }
        foreach (University::orderBy('rank')->get() as $u) {
            $add("uni-{$u->id}", 'universities', 'الجامعات العالمية', $u->name_ar, "{$u->city}، {$u->country} • تصنيف #{$u->rank}", '#universities',
                "{$u->name_ar} {$u->name_en} {$u->city} {$u->country} ".implode(' ', $u->fields).' جامعة');
        }
        foreach (News::ordered()->get() as $n) {
            $add("news-{$n->id}", 'news', 'الأخبار والإعلانات', $n->title, "{$n->author} • {$n->published_on->format('Y-m-d')}", '#news',
                "{$n->title} {$n->excerpt} {$n->author} خبر اعلان");
        }
        foreach (Station::orderBy('sort')->get() as $s) {
            $add("station-{$s->id}", 'journey', 'خارطة الطريق', "المحطة {$s->code}: {$s->title}", implode(' • ', $s->points), '#journey',
                "{$s->title} {$s->description} ".implode(' ', $s->points).' محطة خطوة');
        }
        foreach (config('kasp.faqs') as $i => $f) {
            $add("faq-{$i}", 'faq', 'الأسئلة الشائعة', $f['q'], self::excerpt($f['a']), '#faq', "{$f['q']} {$f['a']}");
        }
        foreach (KbEntry::all() as $k) {
            $add("kb-{$k->id}", 'kb', 'أسئلة المساعد الذكي', $k->question, self::excerpt($k->answer), '#assistant',
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
            'id' => "quick-{$n->id}", 'groupId' => 'news', 'group' => 'أحدث الأخبار',
            'title' => $n->title, 'sub' => $n->published_on->format('Y-m-d'), 'href' => '#news',
        ])->all();

        return [
            ['id' => 'q1', 'groupId' => 'sections', 'group' => 'روابط سريعة', 'title' => 'مسارات الابتعاث الستة', 'sub' => 'استكشف المسار الأنسب', 'href' => '#tracks'],
            ['id' => 'q2', 'groupId' => 'sections', 'group' => 'روابط سريعة', 'title' => 'المطابقة بالذكاء الاصطناعي', 'sub' => 'أربعة أسئلة فقط', 'href' => '#matcher'],
            ['id' => 'q3', 'groupId' => 'sections', 'group' => 'روابط سريعة', 'title' => 'الجامعات العالمية المعتمدة', 'sub' => 'أكثر من 200 جامعة', 'href' => '#universities'],
            ['id' => 'q4', 'groupId' => 'sections', 'group' => 'روابط سريعة', 'title' => 'خارطة الطريق الأكاديمية', 'sub' => 'ثماني محطات واضحة', 'href' => '#journey'],
            ...$news,
        ];
    }
}
