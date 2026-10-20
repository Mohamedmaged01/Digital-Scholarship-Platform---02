{{--
    المحتوى الرسمي للمسار وفق الدليل الاسترشادي (ملاحظات 18 و36 و45):
    بطاقة المسار، ثم الضوابط الرسمية مفصولة عن المحتوى التعريفي، ثم الأسئلة الشائعة.
--}}
@php
    $tabs = collect([
        'policies' => [__('سياسات التقديم'), $track->tr('policies')],
        'general' => [__('الشروط العامة'), $track->tr('general_conditions')],
        'additional' => [__('شروط إضافية (2026–2027)'), $track->tr('additional_conditions')],
        'special' => [__('الشروط الخاصة بالمسار'), $track->tr('special_conditions')],
    ])->filter(fn ($t) => ! empty($t[1]));
    // نصوص الشروط غير المترجمة تُعرض بالعربية كما وردت في الدليل الاسترشادي
    $arabicOnly = app()->getLocale() === 'en' && collect(['policies', 'general_conditions', 'additional_conditions', 'special_conditions'])
        ->contains(fn ($f) => ! empty($track->{$f}) && ! $track->hasEnglish($f));
    $details = $track->tr('details');
    $faqs = $track->tr('faqs');
@endphp

@if ($details)
    <section class="mt-12" aria-labelledby="details-title">
        <h2 id="details-title" class="flex items-center gap-2.5 text-2xl font-bold text-ink">
            <x-lucide-clipboard-list class="size-6 text-gold-600" />
            {{ __('بطاقة المسار') }}
        </h2>
        <dl class="mt-5 divide-y divide-forest-800/8 overflow-hidden rounded-3xl border border-forest-800/10 bg-white">
            @foreach ($details as $row)
                <div class="grid gap-1.5 px-5 py-4 sm:grid-cols-[200px_1fr] sm:gap-6 md:px-6">
                    <dt class="text-[13px] font-bold text-forest-800">{{ $row['label'] }}</dt>
                    <dd class="text-[14px] leading-loose text-slate-700">{{ $row['value'] }}</dd>
                </div>
            @endforeach
        </dl>
    </section>
@endif

@if ($tabs->isNotEmpty())
    <section class="mt-12" x-data="{ tab: @js($tabs->keys()->first()) }" aria-labelledby="rules-title">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 id="rules-title" class="flex items-center gap-2.5 text-2xl font-bold text-ink">
                    <x-lucide-scale class="size-6 text-gold-600" />
                    {{ __('الضوابط والشروط الرسمية') }}
                </h2>
                <p class="mt-1.5 text-sm text-slate-500">{{ __('وفق الدليل الاسترشادي المعتمد للمسار.') }}</p>
            </div>
            <x-last-updated source="{{ __('الدليل الاسترشادي') }}" :version="$track->version" compact />
        </div>

        <div class="no-scrollbar -mx-5 mt-5 flex gap-2 overflow-x-auto px-5 md:mx-0 md:px-0" role="tablist" aria-label="{{ __('أقسام الضوابط') }}">
            @foreach ($tabs as $key => [$label, $sections])
                <button type="button" role="tab" @click="tab = '{{ $key }}'" :aria-selected="tab === '{{ $key }}'"
                        class="shrink-0 rounded-full border px-5 py-2.5 text-xs font-bold transition-all"
                        :class="tab === '{{ $key }}' ? 'border-forest-800 bg-forest-800 text-gold-300 shadow-md' : 'border-forest-800/15 bg-white text-slate-600 hover:border-forest-700/50'">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        @if ($arabicOnly)
            <p class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-[12px] text-amber-800">
                <x-lucide-languages class="me-1 inline size-3.5 align-[-2px]" />
                {{ __('تُعرض الشروط الرسمية لهذا المسار باللغة العربية كما وردت في الدليل الاسترشادي.') }}
            </p>
        @endif

        @foreach ($tabs as $key => [$label, $sections])
            <div role="tabpanel" x-show="tab === '{{ $key }}'" @if (! $loop->first) x-cloak @endif
                 class="mt-4 rounded-3xl border border-forest-800/10 bg-white/70 p-5 md:p-7">
                @include('tracks._sections', ['sections' => $sections])
            </div>
        @endforeach
    </section>
@endif

@if ($faqs)
    <section class="mt-12" x-data="{ open: 0 }" aria-labelledby="faqs-title">
        <h2 id="faqs-title" class="flex items-center gap-2.5 text-2xl font-bold text-ink">
            <x-lucide-circle-help class="size-6 text-gold-600" />
            {{ __('الأسئلة الشائعة عن :track', ['track' => $track->tr('name')]) }}
        </h2>
        <div class="mt-5 space-y-3">
            @foreach ($faqs as $i => $f)
                <div class="overflow-hidden rounded-2xl border transition-colors"
                     :class="open === {{ $i }} ? 'border-forest-700/40 bg-white shadow-sm' : 'border-forest-800/10 bg-white/70'">
                    <button type="button" @click="open = open === {{ $i }} ? -1 : {{ $i }}" :aria-expanded="open === {{ $i }}"
                            class="flex w-full items-start justify-between gap-4 px-5 py-4 text-start">
                        <span class="text-[15px] font-bold leading-relaxed text-ink">{{ $f['q'] }}</span>
                        <x-lucide-plus class="mt-1 size-4 shrink-0 text-forest-700 transition-transform duration-300" ::class="open === {{ $i }} && 'rotate-45'" />
                    </button>
                    <div x-show="open === {{ $i }}" x-collapse @if ($i !== 0) x-cloak @endif>
                        <p class="border-t border-forest-800/8 px-5 pb-5 pt-4 text-[14px] leading-loose text-slate-600">{{ $f['a'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endif
