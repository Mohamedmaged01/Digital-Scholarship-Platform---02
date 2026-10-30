@php
    $destinations = $tracks->filter->isMain()->map->tr('name')->values()->all();
    // ملاحظات 10 و38: حالة التقديم موحّدة من بيانات المسارات
    $openTracks = $tracks->where('application_status', 'open')->values();
@endphp

<section id="home" class="relative overflow-hidden bg-cream pt-28 md:pt-36">
    <div class="pattern-star absolute inset-0" aria-hidden="true"></div>
    <div class="absolute -top-40 start-[-10%] h-[34rem] w-[34rem] rounded-full bg-forest-100/80 blur-[120px]" aria-hidden="true"></div>
    <div class="absolute bottom-0 end-[-8%] h-[30rem] w-[30rem] rounded-full bg-gold-200/60 blur-[130px]" aria-hidden="true"></div>
    <span class="text-outline pointer-events-none absolute -bottom-10 start-0 select-none font-plex text-[22vw] font-bold leading-none md:-bottom-24" dir="ltr" aria-hidden="true">2030</span>

    <div class="relative mx-auto grid max-w-7xl grid-cols-1 items-center gap-16 px-5 pb-16 md:px-8 lg:grid-cols-12 lg:gap-8 lg:pb-24">
        {{-- copy --}}
        <div class="lg:col-span-6">
            <div class="animate-enter inline-flex items-center gap-2.5 rounded-full border border-forest-700/15 bg-white/70 py-2 pe-5 ps-2.5 backdrop-blur">
                <span class="relative grid size-7 place-items-center rounded-full bg-forest-700 text-gold-300">
                    <x-lucide-sparkles class="size-3.5" />
                    <span class="animate-pulse-ring absolute inset-0 rounded-full bg-gold-400/40"></span>
                </span>
                @if ($openTracks->isNotEmpty())
                    <span class="text-[13px] font-semibold text-forest-800">
                        {{ __('التقديم متاح حاليًا على:') }}
                        @foreach ($openTracks as $t)<a href="{{ $t->url() }}" class="font-bold underline decoration-gold-500/60 underline-offset-4 hover:text-forest-600">{{ $t->tr('name') }}</a>{{ $loop->last ? '' : __('، ') }}@endforeach
                    </span>
                @else
                    <span class="text-[13px] font-semibold text-forest-800">{{ __('البوابة التعريفية والإرشادية — العام الأكاديمي 2026–2027') }}</span>
                @endif
            </div>

            <h1 class="animate-enter mt-7 text-balance text-[2.1rem] font-bold leading-[1.25] tracking-tight text-ink md:text-[2.5rem] xl:rtl:text-[2.3rem] xl:rtl:whitespace-nowrap" style="--delay: .1s">
                {{ __('مستقبلُك يبدأ من') }} {{ __('أعرق') }} <span class="text-shimmer-gold">{{ __('جامعات العالم') }}</span>
            </h1>

            <p class="animate-enter mt-6 max-w-xl text-lg leading-relaxed text-slate-600" style="--delay: .2s">
                {{-- النص كما ورد في الموقع التعريفي الرسمي للبرنامج (sites.moe.gov.sa/scholarship-program) --}}
                {{ __('الموقع التعريفي لبرنامج خادم الحرمين الشريفين للابتعاث، الذي يُعرّف باستراتيجية البرنامج وأهدافه ومساراته الأربعة، ويوضح الموقع شروط البرنامج العامة والخاصة، والتخصصات، والجامعات المعتمدة، كما يبيّن كيفية التقديم على البرنامج، والشرائح المستفيدة منه.') }}
            </p>

            {{-- destination rotator --}}
            <div x-data="heroRotator(@js($destinations))" class="animate-enter mt-6 flex items-center gap-2 text-[15px] font-semibold text-slate-500" style="--delay: .3s">
                <x-lucide-globe class="size-4.5 text-forest-600" />
                <span>{{ __('مسارات الابتعاث:') }}</span>
                <span class="relative inline-grid min-w-24 overflow-hidden text-forest-800" aria-live="polite">
                    <template x-for="(d, i) in items" :key="d">
                        <span x-show="idx === i" class="col-start-1 row-start-1 border-b-2 border-gold-500 pb-0.5"
                              x-transition:enter="transition duration-450 ease-out" x-transition:enter-start="translate-y-3.5 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
                              x-transition:leave="transition duration-300 ease-in" x-transition:leave-start="translate-y-0 opacity-100" x-transition:leave-end="-translate-y-3.5 opacity-0"
                              x-text="d"></span>
                    </template>
                </span>
            </div>

            <div class="animate-enter mt-9 flex flex-wrap items-center gap-4" style="--delay: .4s">
                <a href="#matcher" class="group relative inline-flex items-center justify-center gap-2.5 overflow-hidden rounded-full bg-gold-500 px-8 py-4 text-sm font-bold text-forest-950 transition-all duration-300 hover:-translate-y-0.5 hover:bg-gold-400 hover:shadow-[0_12px_40px_-8px_rgba(201,163,56,0.5)]">
                    {{ __('اكتشف المسار الأنسب لك') }}
                    <x-lucide-arrow-up-left class="rtl-flip size-4 transition-transform duration-300 group-hover:-translate-x-1 group-hover:-translate-y-1" />
                </a>
                <a href="#tracks" class="inline-flex items-center justify-center gap-2.5 rounded-full border border-forest-700/30 px-8 py-4 text-sm font-bold text-forest-800 transition-all duration-300 hover:-translate-y-0.5 hover:border-forest-700 hover:bg-forest-700 hover:text-white">
                    <x-lucide-play class="size-4" />
                    {{ __('استكشف مسارات الابتعاث') }}
                </a>
            </div>
        </div>

        {{-- visual --}}
        <div class="relative lg:col-span-6">
            <div class="animate-enter relative mx-auto max-w-md lg:max-w-none" style="--delay: .25s">
                <x-star-emblem class="animate-spin-slower absolute -top-12 end-4 size-40 text-forest-700/15 md:size-56" stroke-width="0.8" />
                <x-star-emblem class="animate-spin-slower absolute -bottom-10 start-2 size-24 text-gold-500/25" stroke-width="0.8" />

                {{-- شعار البرنامج مع برنامج تنمية القدرات البشرية ورؤية 2030 — بنسبة الإطار نفسها (4:3) --}}
                <div class="relative overflow-hidden rounded-[2.5rem] border-[5px] border-forest-800 bg-white shadow-[0_40px_80px_-30px_rgba(8,39,29,0.5)]">
                    <img src="{{ asset('images/hero-kasp-banner.jpg') }}" alt="{{ __('شعار برنامج خادم الحرمين الشريفين للابتعاث مع برنامج تنمية القدرات البشرية ورؤية السعودية 2030') }}"
                         class="aspect-[4/3] w-full object-cover" width="1600" height="1200" fetchpriority="high">

                    <a href="#tracks" class="absolute inset-x-5 bottom-5 hidden items-center justify-between rounded-2xl bg-forest-900/95 sm:flex px-4 py-3 shadow-lg shadow-forest-900/20 transition-colors hover:bg-forest-800">
                        <div class="flex items-center gap-2.5">
                            <x-lucide-badge-check class="size-5 text-gold-300" />
                            <div class="leading-tight">
                                <div class="text-xs font-bold text-white">{{ __('حدد مسارك') }}</div>
                                <div class="text-[11px] text-white/70">{{ __('أفضل مؤسسات تعليمية حول العالم') }}</div>
                            </div>
                        </div>
                        <span class="font-plex text-[10px] tracking-widest text-gold-300" dir="ltr">KASP '26</span>
                    </a>
                </div>

                {{-- floating chip: AI --}}
                <a href="#matcher" class="animate-float-y absolute -top-7 start-3 rounded-2xl border border-forest-800/10 bg-white/90 p-4 shadow-xl shadow-forest-900/10 backdrop-blur transition-colors hover:border-gold-500/50 md:-start-5">
                    <div class="flex items-center gap-3">
                        <span class="grid size-10 place-items-center rounded-xl bg-forest-700 text-gold-300">
                            <x-lucide-list-checks class="size-5" />
                        </span>
                        <div class="leading-tight">
                            <div class="text-[11px] font-medium text-slate-500">{{ __('محرك التوجيه') }}</div>
                            <div class="mt-0.5 text-sm font-bold text-forest-800">{{ __('مطابقة مع ضوابط كل مسار') }}</div>
                        </div>
                    </div>
                </a>

                {{-- floating chip: vision --}}
                <div class="animate-float-y-soft absolute -bottom-8 end-4 hidden rounded-2xl bg-forest-800 p-4 pe-6 text-white shadow-2xl shadow-forest-900/40 sm:block md:-end-4">
                    <div class="flex items-center gap-3">
                        <x-star-emblem class="size-8 text-gold-400" />
                        <div class="leading-tight">
                            <div class="font-plex text-lg font-bold text-gold-300" dir="ltr">Vision 2030</div>
                            <div class="text-[11px] text-white/70">{{ __('رأس مال بشري ينافس عالميًا') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
