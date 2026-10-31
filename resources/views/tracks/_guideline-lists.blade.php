{{--
    قوائم الدليل الاسترشادي لترتيب قوائم الجامعات حسب المجالات 2026-2027 (ملاحظتا 32 و33):
    لكل مجال تخصصاته، ثم المؤسسات التعليمية بترتيبها في الدليل مع ملاحظات البطاقات كما وردت.
--}}
@php
    $guideSource = \App\Support\DefaultContent::guidelineMeta()['source'];
    $multi = count($guideline) > 1;
@endphp

<section class="mt-12" x-data="guidelineLists(@js($guideline))" aria-labelledby="lists-title">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 id="lists-title" class="flex items-center gap-2.5 text-2xl font-bold text-ink">
                <x-lucide-university class="size-6 text-gold-600" />
                {{ __('المؤسسات التعليمية والتخصصات المحددة للمسار') }}
            </h2>
            <p class="mt-2 text-sm text-slate-500">
                {{ __('وفق الدليل الاسترشادي لترتيب قوائم الجامعات حسب المجالات 2026–2027') }} —
                <a href="{{ $guideSource['url'] }}" target="_blank" rel="noopener noreferrer" class="font-bold text-forest-700 underline decoration-gold-500/60 underline-offset-4 hover:text-forest-600">{{ __('عرض الدليل (PDF)') }}</a>
            </p>
        </div>
        <x-last-updated source="{{ __('الدليل الاسترشادي') }}" :version="$guideSource['version']" compact />
    </div>

    @if ($multi)
        {{-- المجالات --}}
        <div class="-mx-5 mt-6 overflow-x-auto px-5 pb-1 md:mx-0 md:px-0" role="tablist" aria-label="{{ __('مجالات المسار') }}">
            <div class="flex gap-2 md:flex-wrap">
                <template x-for="l in lists" :key="l.key">
                    <button type="button" role="tab" @click="select(l.key)" :aria-selected="(active === l.key).toString()"
                            class="shrink-0 rounded-full border px-4 py-2 text-[13px] font-bold transition-all duration-300"
                            :class="active === l.key ? 'border-forest-800 bg-forest-800 text-gold-300 shadow-lg shadow-forest-800/20' : 'border-forest-800/15 bg-white text-slate-600 hover:border-forest-700/50 hover:text-forest-800'">
                        <span x-text="l.name"></span>
                        <span class="ms-1 font-plex text-[11px] opacity-70" dir="ltr" x-text="l.institutions.length"></span>
                    </button>
                </template>
            </div>
        </div>
    @else
        <p class="mt-5 inline-flex items-center gap-2 rounded-full bg-forest-50 px-4 py-2 text-[13px] font-bold text-forest-700">
            <x-lucide-graduation-cap class="size-4 text-gold-600" /> {{ $track->majorsLabel() }}
        </p>
    @endif

    <template x-if="current">
        <div class="mt-6 space-y-6" role="tabpanel">
            {{-- التخصصات التابعة للمجال --}}
            <template x-if="current.majors.length">
                <div class="rounded-3xl border border-forest-800/10 bg-white p-5 md:p-6">
                    <h3 class="flex items-center gap-2 text-base font-bold text-ink">
                        <x-lucide-graduation-cap class="size-5 text-gold-600" />
                        {{ __('التخصصات التابعة للمجال') }}
                        <span class="rounded-md bg-sand px-2 py-0.5 font-plex text-xs text-slate-500" dir="ltr" x-text="current.majors.length"></span>
                    </h3>
                    <ul class="mt-4 flex flex-wrap gap-2">
                        <template x-for="m in current.majors" :key="m.name + m.alt">
                            <li class="rounded-2xl bg-forest-50 px-3.5 py-2 leading-tight">
                                <span class="block text-[13px] font-bold text-forest-800" x-text="m.name"></span>
                                <span class="mt-0.5 block text-[10.5px] text-slate-500" x-text="m.alt"></span>
                                <span x-show="m.mastersOnly" class="mt-1 inline-block rounded-full bg-gold-500/15 px-2 py-0.5 text-[10px] font-bold text-gold-700">{{ __('درجة الماجستير فقط') }}</span>
                            </li>
                        </template>
                    </ul>
                    <p class="mt-4 text-[12px] text-slate-500">
                        <x-lucide-info class="me-1 inline size-3.5 align-[-2px] text-slate-400" />
                        {{ __('قد يكون هناك اختلاف في مسمى التخصصات عن ما هو موجود في موقع الجامعة.') }}
                    </p>
                </div>
            </template>

            {{-- المؤسسات التعليمية بترتيب الدليل --}}
            <div>
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h3 class="flex items-center gap-2 text-base font-bold text-ink">
                        <x-lucide-landmark class="size-5 text-gold-600" />
                        {{ __('المؤسسات التعليمية') }}
                        <span class="rounded-md bg-sand px-2 py-0.5 font-plex text-xs text-slate-500" dir="ltr" x-text="current.institutions.length"></span>
                    </h3>
                    <label class="relative w-full sm:w-72">
                        <span class="sr-only">{{ __('ابحث باسم المؤسسة أو الدولة') }}</span>
                        <x-lucide-search class="pointer-events-none absolute start-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                        <input type="search" x-model.debounce.150ms="search" placeholder="{{ __('ابحث باسم المؤسسة أو الدولة') }}"
                               class="w-full rounded-full border border-forest-800/15 bg-white py-2.5 pe-4 ps-10 text-sm text-ink placeholder:text-slate-400 focus:border-forest-700 focus:outline-none focus:ring-2 focus:ring-forest-700/15">
                    </label>
                </div>

                <ol class="mt-4 grid grid-cols-1 gap-2.5 sm:grid-cols-2 lg:grid-cols-3">
                    <template x-for="u in filtered" :key="u.position + u.name">
                        <li class="flex items-start gap-3 rounded-2xl border border-forest-800/10 bg-white px-4 py-3.5">
                            <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-forest-800 font-plex text-[12px] font-bold text-gold-300" dir="ltr"
                                  :aria-label="@js(__('الترتيب في الدليل :n')).replace(':n', u.position)" x-text="String(u.position).padStart(2, '0')"></span>
                            <span class="min-w-0">
                                <span class="block text-[13.5px] font-bold leading-snug text-ink rtl:text-right" dir="ltr" x-text="u.name"></span>
                                <span class="mt-0.5 flex items-center gap-1 text-[11px] font-semibold text-slate-500"><x-lucide-map-pin class="size-3 text-gold-600" /><span x-text="u.country"></span></span>
                                <span x-show="u.note" class="mt-1.5 inline-block rounded-lg bg-gold-500/15 px-2 py-1 text-[10.5px] font-bold leading-relaxed text-gold-700" x-text="u.note"></span>
                            </span>
                        </li>
                    </template>
                </ol>
                <p x-show="! filtered.length" x-cloak class="mt-4 rounded-2xl border border-dashed border-forest-800/20 p-8 text-center text-sm font-bold text-slate-500">{{ __('لا توجد مؤسسات مطابقة لبحثك') }}</p>
            </div>
        </div>
    </template>
</section>
