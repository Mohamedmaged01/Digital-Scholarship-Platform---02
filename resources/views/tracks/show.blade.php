@extends('layouts.page')

@section('title', $track->tr('name').' | '.__('الشروط والضوابط والمؤسسات التعليمية'))

@php
    $degrees = config('kasp.degrees');
    $guideTypes = config('kasp.guide_types');
    $statusClass = [
        'open' => 'border-emerald-400/50 bg-emerald-500/15 text-emerald-200',
        'not_started' => 'border-amber-400/50 bg-amber-500/15 text-amber-200',
        'closed' => 'border-red-400/50 bg-red-500/15 text-red-200',
        'unavailable' => 'border-white/25 bg-white/10 text-white/70',
        'per_program' => 'border-gold-400/50 bg-gold-500/15 text-gold-200',
    ][$track->application_status] ?? 'border-white/25 bg-white/10 text-white/70';
    $hasExplorer = collect($explorer['degrees'])->contains(fn ($d) => count($d['majors']) > 0);
@endphp

@section('page')
    {{-- ══════ ترويسة المسار ══════ --}}
    <section class="relative h-72 overflow-hidden md:h-80">
        <img src="{{ asset(ltrim($track->image, '/')) }}" alt="" class="h-full w-full object-cover" fetchpriority="high">
        <div class="absolute inset-0 bg-gradient-to-t from-forest-950/95 via-forest-950/50 to-forest-950/10"></div>
        <div class="absolute inset-x-0 bottom-0">
            <div class="mx-auto max-w-5xl px-5 pb-8 md:px-8">
                <nav class="mb-4 flex flex-wrap items-center gap-1.5 text-[11px] font-bold text-white/60" aria-label="{{ __('مسار التنقل') }}">
                    <a href="{{ route('home') }}" class="hover:text-gold-300">{{ __('الرئيسية') }}</a>
                    <x-lucide-chevron-left class="rtl-flip size-3" />
                    <a href="{{ route('home') }}#tracks" class="hover:text-gold-300">{{ __('المسارات') }}</a>
                    <x-lucide-chevron-left class="rtl-flip size-3" />
                    <span class="text-gold-300" aria-current="page">{{ $track->tr('name') }}</span>
                </nav>
                <div class="mb-3 flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full border px-3.5 py-1.5 text-[11px] font-bold backdrop-blur-md {{ $statusClass }}">
                        <x-lucide-clock class="size-3.5" />
                        {{ $track->applicationStatusLabel() }}
                    </span>
                    @if ($track->application_start && $track->application_end)
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-white/20 bg-white/10 px-3.5 py-1.5 text-[11px] font-bold text-white backdrop-blur-md">
                            <x-lucide-calendar-days class="size-3.5" />
                            {{ __('فترة التقديم:') }} <span dir="ltr" class="font-plex">{{ $track->application_start->format('Y-m-d') }} — {{ $track->application_end->format('Y-m-d') }}</span>
                        </span>
                    @endif
                </div>
                <h1 class="text-3xl font-bold text-white md:text-5xl">{{ $track->tr('name') }}</h1>
                <p dir="ltr" class="mt-2 text-right font-plex text-xs font-semibold tracking-[0.15em] text-gold-300">{{ $track->en_subtitle }}</p>
            </div>
        </div>
    </section>

    <div class="mx-auto max-w-5xl px-5 md:px-8">
        {{-- نبذة --}}
        <div class="-mt-2 rounded-3xl border border-forest-800/10 bg-white p-6 shadow-sm md:p-7">
            <p class="text-[15px] leading-loose text-slate-700">{{ $track->tr('description') }}</p>
            <div class="mt-5 flex flex-wrap gap-2.5">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-forest-50 px-3 py-1.5 text-[11px] font-bold text-forest-700">
                    <x-lucide-university class="size-3.5 text-gold-600" /> {{ $track->institutionsLabel() }}
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-forest-50 px-3 py-1.5 text-[11px] font-bold text-forest-700">
                    <x-lucide-graduation-cap class="size-3.5 text-gold-600" /> {{ $track->majorsLabel($explorer['totals']['majors']) }}
                </span>
                @foreach ($track->degrees as $d)
                    <span class="rounded-full bg-forest-800/5 px-3 py-1.5 text-[11px] font-bold text-forest-700">{{ __($degrees[$d] ?? $d) }}</span>
                @endforeach
            </div>
            @if ($track->tr('perks'))
                <p class="mt-5 border-t border-dashed border-forest-800/10 pt-5 text-[11px] font-bold text-slate-400">{{ __('أبرز ملامح المسار') }}</p>
                <ul class="mt-3 grid gap-2 sm:grid-cols-3">
                    @foreach ($track->tr('perks') as $perk)
                        <li class="flex items-start gap-2 text-[13px] font-semibold leading-relaxed text-slate-600">
                            <x-lucide-badge-check class="mt-0.5 size-4 shrink-0 text-gold-600" />{{ $perk }}
                        </li>
                    @endforeach
                </ul>
            @endif
            @if ($track->external_url || $track->extra_link_url)
                <div class="mt-5 flex flex-wrap gap-3">
                    @if ($track->external_url)
                        <a href="{{ $track->external_url }}" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-2 rounded-full bg-forest-800 px-6 py-3 text-sm font-bold text-gold-300 transition-all hover:bg-forest-700">
                            <x-lucide-external-link class="size-4" /> {{ __('صفحة المسار على موقع وزارة التعليم') }}
                        </a>
                    @endif
                    @if ($track->extra_link_url)
                        <a href="{{ $track->extra_link_url }}" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-2 rounded-full border border-forest-800/25 px-6 py-3 text-sm font-bold text-forest-800 transition-all hover:border-forest-800 hover:bg-forest-800 hover:text-gold-300">
                            <x-lucide-search-check class="size-4" /> {{ $track->tr('extra_link_label') }}
                        </a>
                    @endif
                </div>
            @endif
        </div>

        @if ($guideline)
            @include('tracks._guideline-lists')
        @elseif (! $hasExplorer)
            {{-- ملاحظات 32-34: لا تُعرض قوائم غير مستندة إلى الدليل الاسترشادي --}}
            <section class="mt-12" aria-labelledby="scope-title">
                <h2 id="scope-title" class="flex items-center gap-2.5 text-2xl font-bold text-ink">
                    <x-lucide-university class="size-6 text-gold-600" />
                    {{ __('المؤسسات التعليمية والتخصصات المحددة للمسار') }}
                </h2>
                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-2xl border border-forest-800/10 bg-white p-5">
                        <p class="flex items-center gap-2 text-[11px] font-bold text-slate-400"><x-lucide-graduation-cap class="size-4 text-gold-600" /> {{ __('التخصصات') }}</p>
                        <p class="mt-2 text-base font-bold text-forest-800">{{ $track->majorsLabel() }}</p>
                    </div>
                    <div class="rounded-2xl border border-forest-800/10 bg-white p-5">
                        <p class="flex items-center gap-2 text-[11px] font-bold text-slate-400"><x-lucide-university class="size-4 text-gold-600" /> {{ __('المؤسسات التعليمية') }}</p>
                        <p class="mt-2 text-base font-bold text-forest-800">{{ $track->institutionsLabel() }}</p>
                    </div>
                </div>
                <p class="mt-3 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-[12px] leading-relaxed text-blue-700">
                    <x-lucide-info class="me-1 inline size-3.5 align-[-2px]" />
                    {{ __('قائمة المؤسسات التعليمية والتخصصات المحددة لهذا المسار غير منشورة في المنصة حاليًا. يُرجع إلى الشروط الخاصة بالمسار والدليل الاسترشادي المعتمد.') }}
                </p>
            </section>
        @endif

        {{-- ══════ المستكشف (للمسارات التي ليس لها قوائم منشورة) ══════ --}}
        @if ($hasExplorer && ! $guideline)
        <section class="mt-12" x-data="trackExplorer(@js($explorer))" aria-labelledby="explorer-title">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 id="explorer-title" class="flex items-center gap-2.5 text-2xl font-bold text-ink">
                        <x-lucide-university class="size-6 text-gold-600" />
                        {{ __('المؤسسات التعليمية والتخصصات المحددة للمسار') }}
                    </h2>
                    <p class="mt-2 text-sm text-slate-500">{{ __('المسار ← الدرجة العلمية ← التخصص ← المؤسسات التعليمية المحددة') }}</p>
                </div>
                <x-last-updated source="{{ __('الدليل الاسترشادي') }}" :version="$track->version" compact />
            </div>

            {{-- مسار الاختيار --}}
            <div class="mt-5 flex flex-wrap items-center gap-1.5 rounded-xl bg-sand/60 px-4 py-2.5 text-xs font-bold" aria-live="polite">
                <span class="text-forest-700">{{ $track->tr('name') }}</span>
                <template x-if="degreeObj">
                    <span class="flex items-center gap-1.5"><x-lucide-chevron-left class="rtl-flip size-3 text-slate-400" /><span class="text-forest-700" x-text="degreeObj.name"></span></span>
                </template>
                <template x-if="majorObj">
                    <span class="flex items-center gap-1.5"><x-lucide-chevron-left class="rtl-flip size-3 text-slate-400" /><span class="text-forest-700" x-text="majorObj.name"></span></span>
                </template>
                <template x-if="majorObj">
                    <span class="flex items-center gap-1.5"><x-lucide-chevron-left class="rtl-flip size-3 text-slate-400" /><span class="text-gold-600" x-text="@js(__(':count مؤسسة تعليمية')).replace(':count', universities.length)"></span></span>
                </template>
            </div>

            <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="sel-degree" class="mb-1.5 block text-[11px] font-bold text-slate-500">{{ __('① الدرجة العلمية') }}</label>
                    <div class="relative">
                        <select id="sel-degree" x-model="degree"
                                class="w-full appearance-none rounded-xl border border-forest-800/15 bg-white px-4 py-3 text-sm font-bold text-ink outline-none transition-all focus:border-forest-600 focus:ring-4 focus:ring-forest-600/10">
                            <option value="">{{ __('-- اختر الدرجة --') }}</option>
                            <template x-for="d in data.degrees" :key="d.id">
                                <option :value="d.id" x-text="d.name" :disabled="allowedDegrees && !allowedDegrees.includes(d.id)"></option>
                            </template>
                        </select>
                        <x-lucide-chevron-down class="pointer-events-none absolute end-4 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                    </div>
                </div>
                <div :class="!degree && 'pointer-events-none opacity-40'" class="transition-opacity">
                    <label for="sel-major" class="mb-1.5 block text-[11px] font-bold text-slate-500">{{ __('② التخصص / المجال') }}</label>
                    <div class="relative">
                        <select id="sel-major" x-model="major" :disabled="!degree"
                                class="w-full appearance-none rounded-xl border border-forest-800/15 bg-white px-4 py-3 text-sm font-bold text-ink outline-none transition-all focus:border-forest-600 focus:ring-4 focus:ring-forest-600/10">
                            <option value="">{{ __('-- اختر التخصص --') }}</option>
                            <template x-for="g in fieldGroups" :key="g.name">
                                <optgroup :label="g.name">
                                    <template x-for="m in g.majors" :key="m.id">
                                        <option :value="m.id" x-text="m.name"></option>
                                    </template>
                                </optgroup>
                            </template>
                        </select>
                        <x-lucide-chevron-down class="pointer-events-none absolute end-4 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                    </div>
                </div>
            </div>

            {{-- قيود واستثناءات التخصص --}}
            <div x-show="constraints.length" x-cloak class="mt-5 space-y-2.5">
                <div class="flex items-center gap-2 text-xs font-bold text-slate-600">
                    <x-lucide-shield-alert class="size-4 text-amber-500" />
                    {{ __('قيود واستثناءات خاصة بهذا التخصص') }}
                </div>
                <template x-for="c in constraints" :key="c.id">
                    <div class="rounded-xl border p-3.5 text-sm leading-relaxed" :class="c.class">
                        <div class="flex items-start gap-2.5">
                            <x-lucide-triangle-alert class="mt-0.5 size-4 shrink-0" />
                            <div class="min-w-0">
                                <p class="font-bold" x-text="c.title"></p>
                                <p class="mt-1 text-[12px] leading-relaxed opacity-90" x-text="c.description"></p>
                                <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-[10px]">
                                    <span class="font-semibold opacity-70" x-text="c.type"></span>
                                    <span x-show="c.source" class="opacity-60" x-text="@js(__('المصدر: :source')).replace(':source', c.source)"></span>
                                    <span x-show="c.allowed.length" class="font-bold" x-text="@js(__('الدرجات المسموحة: :list')).replace(':list', c.allowed.join(@js(__('، '))))"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
                <p x-show="allowedDegrees" class="rounded-lg border border-blue-200 bg-blue-50 px-3.5 py-2.5 text-[11px] text-blue-700">
                    <x-lucide-info class="me-1 inline size-3.5 align-[-2px]" />
                    {{ __('قائمة الدرجات العلمية تعرض الدرجات المسموحة لهذا التخصص فقط وفق الدليل الاسترشادي.') }}
                </p>
            </div>

            <div x-show="majorObj && majorObj.universities.length" x-cloak class="relative mt-4">
                <x-lucide-search class="absolute start-4 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                <input x-model="search" type="search" placeholder="{{ __('ابحث في المؤسسات التعليمية…') }}" aria-label="{{ __('ابحث في المؤسسات التعليمية') }}"
                       class="w-full rounded-xl border border-forest-800/15 bg-white py-2.5 pe-4 ps-10 text-sm outline-none focus:border-forest-600 focus:ring-4 focus:ring-forest-600/10">
            </div>

            {{-- النتائج --}}
            <div class="mt-6">
                <div x-show="!majorObj" class="rounded-3xl border-2 border-dashed border-forest-800/10 bg-white/60 py-14 text-center">
                    <x-lucide-book-open class="mx-auto mb-3 size-9 text-slate-300" />
                    <p class="text-sm font-bold text-slate-500" x-text="!degree ? @js(__('اختر الدرجة العلمية أولًا')) : @js(__('اختر التخصص لعرض المؤسسات التعليمية'))">{{ __('اختر الدرجة العلمية أولًا') }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ __('المؤسسات التعليمية تظهر وفق ارتباطها بالمسار والدرجة والتخصص') }}</p>
                </div>

                <div x-show="majorObj && universities.length === 0" x-cloak class="rounded-3xl border-2 border-dashed border-forest-800/10 bg-white/60 py-14 text-center">
                    <x-lucide-university class="mx-auto mb-3 size-9 text-slate-300" />
                    <p class="text-sm font-bold text-slate-600">{{ __('لا توجد مؤسسات تعليمية منشورة لهذه المعايير حاليًا') }}</p>
                    <p class="mt-1.5 text-xs text-slate-400">{{ __('يُرجع إلى الدليل الاسترشادي المعتمد للمسار') }}</p>
                </div>

                <template x-if="majorObj && universities.length">
                    <div>
                        <div class="mb-4 flex items-center gap-2 text-sm font-bold text-forest-800">
                            <x-lucide-graduation-cap class="size-4 text-gold-600" />
                            <span x-text="@js(__(':count مؤسسة تعليمية محددة لتخصص «:major» — :degree — :track')).replace(':count', universities.length).replace(':major', majorObj.name).replace(':degree', degreeObj.name).replace(':track', @js($track->tr('name')))"></span>
                        </div>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            <template x-for="u in universities" :key="u.id">
                                <div class="group rounded-2xl border border-forest-800/10 bg-white p-4 transition-all hover:-translate-y-0.5 hover:border-gold-500/40 hover:shadow-lg">
                                    <div class="flex items-start gap-3">
                                        <span class="mt-0.5 grid size-10 shrink-0 place-items-center rounded-xl bg-forest-800 text-gold-300 transition-transform group-hover:scale-105">
                                            <x-lucide-university class="size-4.5" />
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-bold text-ink" x-text="u.nameAr"></p>
                                            <p dir="ltr" class="mt-0.5 truncate text-right font-plex text-xs text-slate-500" x-text="u.nameEn"></p>
                                        </div>
                                    </div>
                                    <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1.5">
                                        <span class="flex items-center gap-1.5 text-[12px] font-semibold text-forest-700">
                                            <x-lucide-map-pin class="size-3.5 text-gold-600" /><span x-text="[u.city, u.country].filter(Boolean).join(@js(__('، ')))"></span>
                                        </span>
                                        <a x-show="u.website" :href="u.website" target="_blank" rel="noopener noreferrer" class="flex items-center gap-1 text-[11px] font-bold text-forest-600 transition-colors hover:text-gold-600">
                                            <x-lucide-external-link class="size-3" />{{ __('الموقع') }}
                                        </a>
                                    </div>
                                    <p class="mt-2.5 text-[10px] font-bold" :class="u.verified ? 'text-forest-600' : 'text-slate-400'"
                                       x-text="u.verified ? @js(__('✓ بيانات المؤسسة مُتحقق منها')) : @js(__('بيانات المؤسسة قيد التحقق'))"></p>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </section>
        @endif

        @include('tracks._guideline')

        {{-- ══════ الأدلة الاسترشادية ══════ --}}
        <section class="mt-14" x-data="{ showPast: false }" aria-labelledby="guides-title">
            <h2 id="guides-title" class="flex items-center gap-2.5 text-2xl font-bold text-ink">
                <x-lucide-file-text class="size-6 text-gold-600" />
                {{ __('الدليل الاسترشادي المعتمد') }}
            </h2>
            <p class="mt-1.5 text-sm text-slate-500">{{ __('المرجع المعتمد الذي تُبنى عليه بيانات هذا المسار من شروط ومؤسسات تعليمية وتخصصات.') }}</p>

            @if ($currentGuides->isEmpty() && $pastGuides->isEmpty())
                <div class="mt-4 rounded-3xl border-2 border-dashed border-forest-800/10 bg-white/60 py-10 text-center">
                    <x-lucide-file-text class="mx-auto mb-3 size-8 text-slate-300" />
                    <p class="text-sm font-bold text-slate-500">{{ __('لم يُرفق دليل استرشادي بعد') }}</p>
                </div>
            @endif

            <div class="mt-4 space-y-3">
                @foreach ($currentGuides as $g)
                    @php $href = $g->file ? $g->file->url() : $g->url; @endphp
                    <div class="overflow-hidden rounded-3xl border border-forest-700/30 bg-forest-950">
                        <div class="flex items-start gap-4 p-5">
                            <span class="mt-0.5 grid size-12 shrink-0 place-items-center rounded-xl bg-gold-500 text-forest-950 shadow-lg shadow-gold-500/30">
                                <x-lucide-file-text class="size-6" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full bg-emerald-500/20 px-3 py-1 text-[10px] font-bold text-emerald-300">{{ __('الإصدار الحالي') }}</span>
                                    <span class="rounded-full bg-white/10 px-2.5 py-1 text-[10px] font-bold text-white/60">{{ __($guideTypes[$g->guide_type] ?? $g->guide_type) }}</span>
                                </div>
                                <h3 class="mt-2 text-[15px] font-bold text-white">{{ app()->getLocale() === 'en' && $g->title_en ? $g->title_en : $g->title_ar }}</h3>
                                @if ($g->notes)
                                    <p class="mt-2 flex items-start gap-1.5 text-[11px] text-amber-300"><x-lucide-info class="mt-0.5 size-3.5 shrink-0" />{{ __($g->notes) }}</p>
                                @endif
                                <x-last-updated :version="$g->version" compact dark class="mt-3 w-fit" />
                            </div>
                        </div>
                        @if ($href)
                            <div class="flex flex-wrap gap-3 border-t border-white/10 px-5 py-4">
                                <a href="{{ $href }}" target="_blank" rel="noopener noreferrer"
                                   class="inline-flex items-center gap-2 rounded-full bg-gold-500 px-6 py-2.5 text-sm font-bold text-forest-950 transition-all hover:bg-gold-400 hover:shadow-[0_10px_28px_-8px_rgba(201,163,56,0.6)]">
                                    <x-lucide-external-link class="size-4" /> {{ __('عرض الدليل') }}
                                </a>
                                @if ($g->file)
                                    <a href="{{ $href }}" download
                                       class="inline-flex items-center gap-2 rounded-full border border-white/20 px-6 py-2.5 text-sm font-bold text-white transition-all hover:border-gold-400 hover:text-gold-300">
                                        <x-lucide-download class="size-4" /> {{ __('تحميل') }}
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach

                @if ($pastGuides->isNotEmpty())
                    <button type="button" @click="showPast = !showPast" :aria-expanded="showPast"
                            class="flex items-center gap-2 text-xs font-bold text-slate-500 transition-colors hover:text-forest-700">
                        <x-lucide-history class="size-4" />
                        <span x-text="showPast ? @js(__('إخفاء الإصدارات السابقة')) : @js(__('عرض الإصدارات السابقة (:count)', ['count' => $pastGuides->count()]))">{{ __('عرض الإصدارات السابقة (:count)', ['count' => $pastGuides->count()]) }}</span>
                    </button>
                    <div x-show="showPast" x-collapse x-cloak class="space-y-2">
                        @foreach ($pastGuides as $g)
                            @php $href = $g->file ? $g->file->url() : $g->url; @endphp
                            <div class="flex items-center gap-4 rounded-2xl border border-forest-800/10 bg-white p-4">
                                <x-lucide-history class="size-5 shrink-0 text-slate-400" />
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-bold text-slate-700">{{ app()->getLocale() === 'en' && $g->title_en ? $g->title_en : $g->title_ar }}</p>
                                    <p class="mt-0.5 text-xs text-slate-400">{{ __('العام الأكاديمي') }} <span class="font-plex" dir="ltr">{{ $g->version }}</span></p>
                                </div>
                                @if ($href)
                                    <a href="{{ $href }}" target="_blank" rel="noopener noreferrer"
                                       class="inline-flex items-center gap-1.5 rounded-full border border-forest-800/15 px-4 py-2 text-xs font-bold text-forest-700 transition-all hover:bg-forest-800 hover:text-gold-300">
                                        <x-lucide-external-link class="size-3.5" />{{ __('عرض') }}
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        <p class="mt-10 rounded-xl border border-amber-200 bg-amber-50/70 px-4 py-3 text-[12px] leading-relaxed text-amber-800">
            <x-lucide-info class="me-1 inline size-3.5 align-[-2px]" />
            {{ __('المعلومات تعريفية وإرشادية. قوائم المؤسسات التعليمية والتخصصات مرتبطة بالعام الأكاديمي وتُحدَّث عند إطلاق كل فترة تقديم وفق الدليل الاسترشادي المعتمد.') }}
        </p>

        {{-- مسارات أخرى --}}
        <nav class="mt-12" aria-label="{{ __('مسارات أخرى') }}">
            <h2 class="text-sm font-bold text-slate-500">{{ __('مسارات أخرى') }}</h2>
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach ($otherTracks as $t)
                    <a href="{{ $t->url() }}"
                       class="rounded-full border border-forest-800/15 bg-white px-5 py-2.5 text-sm font-bold text-forest-800 transition-all hover:border-forest-800 hover:bg-forest-800 hover:text-gold-300">{{ $t->tr('name') }}</a>
                @endforeach
            </div>
        </nav>
    </div>
@endsection
