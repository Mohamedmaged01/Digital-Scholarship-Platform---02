<?php

namespace App\Support;

use App\Models\KbEntry;
use App\Models\UnansweredQuestion;

/**
 * محرّك مطابقة أسئلة الزوار مع قاعدة المعرفة.
 * عند غياب إجابة واضحة يُسجَّل السؤال في «الأسئلة بلا إجابة».
 */
class Assistant
{
    private const STOP_WORDS = [
        'ما', 'ماذا', 'هل', 'كيف', 'من', 'عن', 'في', 'الي', 'الى', 'علي', 'على', 'متي', 'متى',
        'اين', 'لماذا', 'هو', 'هي', 'ان', 'او', 'ثم', 'قد', 'و', 'يا', 'اي', 'ذلك', 'هذا', 'هذه',
        'لي', 'لدي', 'لدى', 'مع', 'بعد', 'قبل', 'عند', 'كان', 'تكون', 'يكون', 'الذي', 'التي', 'لمن',
        'يمكن', 'هلل', 'بتاع', 'عشان', 'اذا', 'كل', 'غير', 'دون', 'فقط', 'تم',
    ];

    /** @return list<string> */
    private static function tokens(string $s): array
    {
        return array_values(array_filter(
            ArabicText::words($s),
            fn (string $t) => mb_strlen($t) > 1 && ! in_array($t, self::STOP_WORDS, true)
        ));
    }

    /**
     * @return array{entry: ?KbEntry, suggestions: list<string>}
     */
    public static function ask(string $query): array
    {
        $qTokens = array_values(array_unique(self::tokens($query)));
        if ($qTokens === []) {
            return ['entry' => null, 'suggestions' => []];
        }

        $scored = KbEntry::query()->orderBy('is_custom')->orderBy('id')->get()
            ->map(function (KbEntry $entry) use ($qTokens) {
                $corpus = array_unique(self::tokens($entry->question.' '.implode(' ', $entry->keywords)));
                $score = 0;
                foreach ($qTokens as $t) {
                    if (in_array($t, $corpus, true)) {
                        $score += 2;
                    } elseif (mb_strlen($t) > 3) {
                        $a = mb_substr($t, 0, 4);
                        foreach ($corpus as $c) {
                            $b = mb_substr($c, 0, 4);
                            if (mb_strlen($c) > 3 && ($a === $b || str_starts_with($c, $a) || str_starts_with($t, $b))) {
                                $score += 1;
                                break;
                            }
                        }
                    }
                }

                return ['entry' => $entry, 'score' => $score];
            })
            ->sortByDesc('score')
            ->values();

        $threshold = count($qTokens) <= 1 ? 2 : 4;
        $best = $scored->first();

        if ($best && $best['score'] >= $threshold) {
            return ['entry' => $best['entry'], 'suggestions' => []];
        }

        self::logUnanswered($query);

        return [
            'entry' => null,
            'suggestions' => $scored->where('score', '>=', 2)->take(2)
                ->map(fn ($s) => $s['entry']->question)->values()->all(),
        ];
    }

    public static function logUnanswered(string $question): void
    {
        $question = mb_substr(trim($question), 0, 500);
        $existing = UnansweredQuestion::where('question', $question)->first();

        if ($existing) {
            $existing->increment('asked_count', 1, ['last_asked_at' => now()]);

            return;
        }

        UnansweredQuestion::create([
            'question' => $question,
            'first_asked_at' => now(),
            'last_asked_at' => now(),
        ]);
    }
}
