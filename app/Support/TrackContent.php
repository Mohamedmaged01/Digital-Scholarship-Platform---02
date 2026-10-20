<?php

namespace App\Support;

/**
 * محتوى الدليل الاسترشادي للمسار (السياسات والشروط والأسئلة الشائعة وبطاقة البيانات).
 *
 * يُخزَّن منظّمًا في أعمدة JSON، ويُحرَّر في لوحة التحكم نصًّا بسيطًا:
 *
 *   الشروط:   ## عنوان القسم        الأسئلة:   س: نص السؤال        البطاقة:  البند: المحتوى
 *             - بند                            ج: نص الإجابة
 *               - بند فرعي
 *
 * @phpstan-type Item string|array{text: string, sub: list<string>}
 * @phpstan-type Section array{title: string, items: list<Item>}
 */
class TrackContent
{
    /** @return list<Section> */
    public static function parseSections(string $text): array
    {
        $sections = [];
        $current = null;

        foreach (preg_split('/\R/u', $text) as $raw) {
            if (trim($raw) === '') {
                continue;
            }
            if (preg_match('/^\s*##\s*(.+)$/u', $raw, $m)) {
                if ($current) {
                    $sections[] = $current;
                }
                $current = ['title' => trim($m[1]), 'items' => []];

                continue;
            }

            $current ??= ['title' => '', 'items' => []];
            $isSub = (bool) preg_match('/^(\s{2,}|\t)[-•]|^\s*--/u', $raw);
            $line = trim(preg_replace('/^\s*(--|[-•])\s*/u', '', $raw));

            if ($isSub && $current['items']) {
                $last = array_pop($current['items']);
                $last = is_array($last) ? $last : ['text' => $last, 'sub' => []];
                $last['sub'][] = $line;
                $current['items'][] = $last;
            } else {
                $current['items'][] = $line;
            }
        }
        if ($current) {
            $sections[] = $current;
        }

        return array_values(array_filter($sections, fn ($s) => $s['items'] || $s['title'] !== ''));
    }

    public static function formatSections(?array $sections): string
    {
        $out = [];
        foreach ($sections ?? [] as $s) {
            if (($s['title'] ?? '') !== '') {
                $out[] = '## '.$s['title'];
            }
            foreach ($s['items'] ?? [] as $item) {
                if (is_array($item)) {
                    $out[] = '- '.$item['text'];
                    foreach ($item['sub'] ?? [] as $sub) {
                        $out[] = '  - '.$sub;
                    }
                } else {
                    $out[] = '- '.$item;
                }
            }
            $out[] = '';
        }

        return rtrim(implode("\n", $out));
    }

    /** @return list<array{q: string, a: string}> */
    public static function parseFaqs(string $text): array
    {
        $faqs = [];
        $current = null;

        foreach (preg_split('/\R/u', $text) as $raw) {
            $line = trim($raw);
            if ($line === '') {
                continue;
            }
            if (preg_match('/^س\s*[:：]\s*(.+)$/u', $line, $m)) {
                if ($current) {
                    $faqs[] = $current;
                }
                $current = ['q' => trim($m[1]), 'a' => ''];
            } elseif ($current && preg_match('/^ج\s*[:：]\s*(.*)$/u', $line, $m)) {
                $current['a'] = trim($m[1]);
            } elseif ($current) {
                $current['a'] = trim($current['a'].' '.$line);
            }
        }
        if ($current) {
            $faqs[] = $current;
        }

        return array_values(array_filter($faqs, fn ($f) => $f['q'] !== '' && $f['a'] !== ''));
    }

    public static function formatFaqs(?array $faqs): string
    {
        return implode("\n\n", array_map(fn ($f) => "س: {$f['q']}\nج: {$f['a']}", $faqs ?? []));
    }

    /** @return list<array{label: string, value: string}> */
    public static function parseDetails(string $text): array
    {
        $rows = [];
        foreach (preg_split('/\R/u', $text) as $raw) {
            $parts = preg_split('/\s*[:：]\s*/u', trim($raw), 2);
            if (count($parts) === 2 && $parts[0] !== '' && $parts[1] !== '') {
                $rows[] = ['label' => $parts[0], 'value' => $parts[1]];
            }
        }

        return $rows;
    }

    public static function formatDetails(?array $rows): string
    {
        return implode("\n", array_map(fn ($r) => "{$r['label']}: {$r['value']}", $rows ?? []));
    }
}
