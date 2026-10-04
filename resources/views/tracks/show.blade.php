@extends('layouts.page')

@section('title', "{$track->name} | الجامعات والتخصصات المتاحة")

@php
    $degrees = config('kasp.degrees');
    $guideTypes = config('kasp.guide_types');
    $statusClass = [
        'open' => 'border-emerald-400/50 bg-emerald-500/15 text-emerald-200',
        'not_started' => 'border-amber-400/50 bg-amber-500/15 text-amber-200',
        'closed' => 'border-red-400/50 bg-red-500/15 text-red-200',
        'unavailable' => 'border-white/25 bg-white/10 text-white/70',
    ][$track->application_status] ?? 'border-white/25 bg-white/10 text-white/70';
@endphp

@section('page')
    {{-- ══════ ترويسة المسار ══════ --}}
    <section class="relative h-72 overflow-hidden md:h-80">
        <img src="{{ asset(ltrim($track->image, '/')) }}" alt="" class="h-full w-full object-cover" fetchpriority="high">
        <div class="absolute inset-0 bg-gradient-to-t from-forest-950/95 via-forest-950/50 to-forest-950/10"></div>
        <div class="absolute inset-x-0 bottom-0">
            <div class="mx-auto max-w-5xl px-5 pb-8 md:px-8">
                <nav class="mb-4 flex flex-wrap items-center gap-1.5 text-[11px] font-bold text-white/60" aria-label="مسار التنقل">
                    <a href="{{ route('home') }}" class="hover:text-gold-300">الرئيسية</a>
                    <x-lucide-chevron-left class="size-3" />
                    <a href="{{ route('home') }}#tracks" class="hover:text-gold-300">المسارات</a>
                    <x-lucide-chevron-left class="size-3" />
                    <span class="text-gold-300" aria-current="page">{{ $track->name }}</span>
                </nav>
                <div class="mb-3 flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full border px-3.5 py-1.5 text-[11px] font-bold backdrop-blur-md {{ $statusClass }}">
                        <x-lucide-clock class="size-3.5" />
                        {{ $track->applicationStatusLabel() }}
                    </span>
                    @if ($track->application_start && $track->application_end)
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-white/20 bg-white/10 px-3.5 py-1.5 text-[11px] font-bold text-white backdrop-blur-md">
                            <x-lucide-calendar-days class="size-3.5" />
                            فترة التقديم: <span dir="ltr" class="font-plex">{{ $track->application_start->format('Y-m-d') }} — {{ $track->application_end->format('Y-m-d') }}</span>
                        </span>
                    @endif
                </div>
                <h1 class="text-3xl font-bold text-white md:text-5xl">{{ $track->name }}</h1>
                <p dir="ltr" class="mt-2 text-right font-plex text-xs font-semibold tracking-[0.15em] text-gold-300">{{ $track->en_subtitle }}</p>
            </div>
        </div>
    </section>

    <div class="mx-auto max-w-5xl px-5 md:px-8">
        {{-- نبذة --}}
        <div class="-mt-2 rounded-3xl border border-forest-800/10 bg-white p-6 shadow-sm md:p-7">
            <p class="text-[15px] leading-loose text-slate-700">{{ $track->description }}</p>
            <div class="mt-5 flex flex-wrap gap-2.5">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-forest-50 px-3 py-1.5 text-[11px] font-bold text-forest-700">
                    <x-lucide-globe class="size-3.5 text-gold-600" /> {{ $track->ranking }}
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-forest-50 px-3 py-1.5 text-[11px] font-bold text-forest-700">
                    <x-lucide-graduation-cap class="size-3.5 text-gold-600" /> {{ $explorer['totals']['majors'] }} تخصص
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-forest-50 px-3 py-1.5 text-[11px] font-bold text-forest-700">
                    <x-lucide-university class="size-3.5 text-gold-600" /> {{ $explorer['totals']['universities'] }} مؤسسة تعليمية
                </span>
                @foreach ($track->degrees as $d)
                    <span class="rounded-full bg-forest-800/5 px-3 py-1.5 text-[11px] font-bold text-forest-700">{{ $degrees[$d] ?? $d }}</span>
                @endforeach
            </div>
            @if ($track->perks)
                <ul class="mt-5 grid gap-2 border-t border-dashed border-forest-800/10 pt-5 sm:grid-cols-3">
                    @foreach ($track->perks as $perk)
                        <li class="flex items-start gap-2 text-[13px] font-semibold leading-relaxed text-slate-600">
                            <x-lucide-badge-check class="mt-0.5 size-4 shrink-0 text-gold-600" />{{ $perk }}
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- ══════ المستكشف ══════ --}}
        <section class="mt-12" x-data="trackExplorer(@js($explorer))" aria-labelledby="explorer-title">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 id="explorer-title" class="flex items-center gap-2.5 text-2xl font-bold text-ink">
                        <x-lucide-university class="size-6 text-gold-600" />
                        الجامعات والتخصصات المتاحة لهذا المسار
                    </h2>
                    <p class="mt-2 text-sm text-slate-500">المسار ← الدرجة العلمية ← التخصص/المجال ← الجامعات المتاحة</p>
                </div>
                <x-last-updated :date="$track->last_verified_at" source="الجامعات والتخصصات" :version="$track->version" compact />
            </div>

            {{-- مسار الاختيار --}}
            <div class="mt-5 flex flex-wrap items-center gap-1.5 rounded-xl bg-sand/60 px-4 py-2.5 text-xs font-bold" aria-live="polite">
                <span class="text-forest-700">{{ $track->name }}</span>
                <template x-if="degreeObj">
                    <span class="flex items-center gap-1.5"><x-lucide-chevron-left class="size-3 text-slate-400" /><span class="text-forest-700" x-text="degreeObj.name"></span></span>
                </template>
                <template x-if="majorObj">
                    <span class="flex items-center gap-1.5"><x-lucide-chevron-left class="size-3 text-slate-400" /><span class="text-forest-700" x-text="majorObj.name"></span></span>
                </template>
                <template x-if="majorObj">
                    <span class="flex items-center gap-1.5"><x-lucide-chevron-left class="size-3 text-slate-400" /><span class="text-gold-600" x-text="`${universities.length} جامعة`"></span></span>
                </template>
            </div>

            <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="sel-degree" class="mb-1.5 block text-[11px] font-bold text-slate-500">① الدرجة العلمية</label>
                    <div class="relative">
                        <select id="sel-degree" x-model="degree"
                                class="w-full appearance-none rounded-xl border border-forest-800/15 bg-white px-4 py-3 text-sm font-bold text-ink outline-none transition-all focus:border-forest-600 focus:ring-4 focus:ring-forest-600/10">
                            <option value="">-- اختر الدرجة --</option>
                            <template x-for="d in data.degrees" :key="d.id">
                                <option :value="d.id" x-text="d.name" :disabled="allowedDegrees && !allowedDegrees.includes(d.id)"></option>
                            </template>
                        </select>
                        <x-lucide-chevron-down class="pointer-events-none absolute end-4 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                    </div>
                </div>
                <div :class="!degree && 'pointer-events-none opacity-40'" class="transition-opacity">
                    <label for="sel-major" class="mb-1.5 block text-[11px] font-bold text-slate-500">② التخصص / المجال</label>
                    <div class="relative">
                        <select id="sel-major" x-model="major" :disabled="!degree"
                                class="w-full appearance-none rounded-xl border border-forest-800/15 bg-white px-4 py-3 text-sm font-bold text-ink outline-none transition-all focus:border-forest-600 focus:ring-4 focus:ring-forest-600/10">
                            <option value="">-- اختر التخصص --</option>
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
                    قيود واستثناءات خاصة بهذا التخصص
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
                                    <span x-show="c.source" class="opacity-60" x-text="`المصدر: ${c.source}`"></span>
                                    <span x-show="c.allowed.length" class="font-bold" x-text="`الدرجات المسموحة: ${c.allowed.join('، ')}`"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
                <p x-show="allowedDegrees" class="rounded-lg border border-blue-200 bg-blue-50 px-3.5 py-2.5 text-[11px] text-blue-700">
                    <x-lucide-info class="me-1 inline size-3.5 align-[-2px]" />
                    قائمة الدرجات العلمية تعرض الدرجات المسموحة لهذا التخصص فقط وفق الدليل الاسترشادي.
                </p>
            </div>

            <div x-show="majorObj && majorObj.universities.length" x-cloak class="relative mt-4">
                <x-lucide-search class="absolute start-4 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                <input x-model="search" type="search" placeholder="ابحث في الجامعات…" aria-label="ابحث في الجامعات"
                       class="w-full rounded-xl border border-forest-800/15 bg-white py-2.5 pe-4 ps-10 text-sm outline-none focus:border-forest-600 focus:ring-4 focus:ring-forest-600/10">
            </div>

            {{-- النتائج --}}
            <div class="mt-6">
                <div x-show="!majorObj" class="rounded-3xl border-2 border-dashed border-forest-800/10 bg-white/60 py-14 text-center">
                    <x-lucide-book-open class="mx-auto mb-3 size-9 text-slate-300" />
                    <p class="text-sm font-bold text-slate-500" x-text="!degree ? 'اختر الدرجة العلمية أولًا' : 'اختر التخصص لعرض الجامعات'">اختر الدرجة العلمية أولًا</p>
                    <p class="mt-1 text-xs text-slate-400">الجامعات تظهر وفق ارتباطها بالمسار والدرجة والتخصص</p>
                </div>

                <div x-show="majorObj && universities.length === 0" x-cloak class="rounded-3xl border-2 border-dashed border-forest-800/10 bg-white/60 py-14 text-center">
                    <x-lucide-university class="mx-auto mb-3 size-9 text-slate-300" />
                    <p class="text-sm font-bold text-slate-600">لا توجد جامعات مرتبطة بهذه المعايير حاليًا</p>
                    <p class="mt-1.5 text-xs text-slate-400">تُضاف الجامعات من لوحة التحكم ← إدارة العلاقات</p>
                </div>

                <template x-if="majorObj && universities.length">
                    <div>
                        <div class="mb-4 flex items-center gap-2 text-sm font-bold text-forest-800">
                            <x-lucide-graduation-cap class="size-4 text-gold-600" />
                            <span x-text="`${universities.length} جامعة متاحة لتخصص «${majorObj.name}» — ${degreeObj.name} — {{ $track->name }}`"></span>
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
                                            <x-lucide-map-pin class="size-3.5 text-gold-600" /><span x-text="`${u.city}، ${u.country}`"></span>
                                        </span>
                                        <a x-show="u.website" :href="u.website" target="_blank" rel="noopener noreferrer" class="flex items-center gap-1 text-[11px] font-bold text-forest-600 transition-colors hover:text-gold-600">
                                            <x-lucide-external-link class="size-3" />الموقع
                                        </a>
                                    </div>
                                    <p class="mt-2.5 text-[10px] font-bold" :class="u.verified ? 'text-forest-600' : 'text-slate-400'"
                                       x-text="u.verified ? '✓ بيانات المؤسسة مُتحقق منها' : 'بيانات المؤسسة قيد التحقق'"></p>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </section>

        {{-- ══════ الأدلة الاسترشادية ══════ --}}
        <section class="mt-14" x-data="{ showPast: false }" aria-labelledby="guides-title">
            <h2 id="guides-title" class="flex items-center gap-2.5 text-2xl font-bold text-ink">
                <x-lucide-file-text class="size-6 text-gold-600" />
                الدليل الاسترشادي المعتمد
            </h2>
            <p class="mt-1.5 text-sm text-slate-500">المرجع المعتمد الذي تُبنى عليه بيانات هذا المسار من جامعات وتخصصات وشروط.</p>

            @if ($currentGuides->isEmpty() && $pastGuides->isEmpty())
                <div class="mt-4 rounded-3xl border-2 border-dashed border-forest-800/10 bg-white/60 py-10 text-center">
                    <x-lucide-file-text class="mx-auto mb-3 size-8 text-slate-300" />
                    <p class="text-sm font-bold text-slate-500">لم يُرفق دليل استرشادي بعد</p>
                    <p class="mt-1 text-xs text-slate-400">تستطيع الإدارة إضافة الدليل من لوحة التحكم ← الأدلة الاسترشادية</p>
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
                                    <span class="rounded-full bg-emerald-500/20 px-3 py-1 text-[10px] font-bold text-emerald-300">الإصدار الحالي</span>
                                    <span dir="ltr" class="font-plex text-[10px] font-bold text-gold-400">{{ $g->version }}</span>
                                    <span class="rounded-full bg-white/10 px-2.5 py-1 text-[10px] font-bold text-white/60">{{ $guideTypes[$g->guide_type] ?? $g->guide_type }}</span>
                                </div>
                                <h3 class="mt-2 text-[15px] font-bold text-white">{{ $g->title_ar }}</h3>
                                @if ($g->publication_date)
                                    <p class="mt-1.5 flex flex-wrap items-center gap-3 text-[12px] text-slate-400">
                                        <span>تاريخ النشر: <span dir="ltr" class="font-plex">{{ $g->publication_date->format('Y-m-d') }}</span></span>
                                        @if ($g->effective_date)
                                            <span>السريان: <span dir="ltr" class="font-plex">{{ $g->effective_date->format('Y-m-d') }}</span></span>
                                        @endif
                                    </p>
                                @endif
                                @if ($g->notes)
                                    <p class="mt-2 flex items-start gap-1.5 text-[11px] text-amber-300"><x-lucide-info class="mt-0.5 size-3.5 shrink-0" />{{ $g->notes }}</p>
                                @endif
                                <x-last-updated :date="$g->updated_at" source="الدليل الاسترشادي" :version="$g->version" compact dark class="mt-3" />
                            </div>
                        </div>
                        @if ($href)
                            <div class="flex flex-wrap gap-3 border-t border-white/10 px-5 py-4">
                                <a href="{{ $href }}" target="_blank" rel="noopener noreferrer"
                                   class="inline-flex items-center gap-2 rounded-full bg-gold-500 px-6 py-2.5 text-sm font-bold text-forest-950 transition-all hover:bg-gold-400 hover:shadow-[0_10px_28px_-8px_rgba(201,163,56,0.6)]">
                                    <x-lucide-external-link class="size-4" /> عرض الدليل
                                </a>
                                @if ($g->file)
                                    <a href="{{ $href }}" download
                                       class="inline-flex items-center gap-2 rounded-full border border-white/20 px-6 py-2.5 text-sm font-bold text-white transition-all hover:border-gold-400 hover:text-gold-300">
                                        <x-lucide-download class="size-4" /> تحميل
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
                        <span x-text="showPast ? 'إخفاء الإصدارات السابقة' : 'عرض الإصدارات السابقة ({{ $pastGuides->count() }})'"></span>
                    </button>
                    <div x-show="showPast" x-collapse x-cloak class="space-y-2">
                        @foreach ($pastGuides as $g)
                            @php $href = $g->file ? $g->file->url() : $g->url; @endphp
                            <div class="flex items-center gap-4 rounded-2xl border border-forest-800/10 bg-white p-4">
                                <x-lucide-history class="size-5 shrink-0 text-slate-400" />
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-bold text-slate-700">{{ $g->title_ar }}</p>
                                    <p class="mt-0.5 font-plex text-xs text-slate-400" dir="ltr">v{{ $g->version }}{{ $g->publication_date ? ' — '.$g->publication_date->format('Y-m-d') : '' }}</p>
                                </div>
                                @if ($href)
                                    <a href="{{ $href }}" target="_blank" rel="noopener noreferrer"
                                       class="inline-flex items-center gap-1.5 rounded-full border border-forest-800/15 px-4 py-2 text-xs font-bold text-forest-700 transition-all hover:bg-forest-800 hover:text-gold-300">
                                        <x-lucide-external-link class="size-3.5" />عرض
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
            المعلومات تعريفية وإرشادية. قوائم الجامعات والتخصصات تُحدَّث دوريًا من الإدارة عند إطلاق كل فترة تقديم جديدة. يُرجى الرجوع إلى الدليل الاسترشادي المعتمد.
        </p>

        {{-- مسارات أخرى --}}
        <nav class="mt-12" aria-label="مسارات أخرى">
            <h2 class="text-sm font-bold text-slate-500">مسارات أخرى</h2>
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach ($otherTracks as $t)
                    <a href="{{ $t->slug === 'waed' ? route('waed.index') : route('tracks.show', $t) }}"
                       class="rounded-full border border-forest-800/15 bg-white px-5 py-2.5 text-sm font-bold text-forest-800 transition-all hover:border-forest-800 hover:bg-forest-800 hover:text-gold-300">{{ $t->name }}</a>
                @endforeach
            </div>
        </nav>
    </div>
@endsection
