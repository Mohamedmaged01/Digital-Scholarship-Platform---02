@php
    $destinations = $tracks->filter->isMain()->map->tr('name')->values()->all();
    // ملاحظات 10 و38: حالة التقديم موحّدة من بيانات المسارات
    $openTracks = $tracks->where('application_status', 'open')->values();
    // العنوان والنص ومعرض الصور تُدار من «الواجهة الرئيسية» في لوحة التحكم
    $heroTitle = \App\Models\SiteSetting::localized('hero_title');
    $heroHighlight = \App\Models\SiteSetting::localized('hero_highlight');
    $slideCount = $heroSlides->count();
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

            <h1 class="animate-enter mt-7 text-balance text-[2.1rem] font-bold leading-[1.25] tracking-tight text-ink md:text-[2.5rem] xl:rtl:text-[2.3rem]" style="--delay: .1s">
                {{ $heroTitle }} @if ($heroHighlight)<span class="text-shimmer-gold">{{ $heroHighlight }}</span>@endif
            </h1>

            <p class="animate-enter mt-6 max-w-xl text-lg leading-relaxed text-slate-600" style="--delay: .2s">
                {{ \App\Models\SiteSetting::localized('hero_text') }}
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

                {{-- معرض متحرك بنسبة الإطار نفسها (4:3) — الصور والعناوين من «الواجهة الرئيسية» في اللوحة --}}
                <div x-data="heroGallery({{ max($slideCount, 1) }})" @mouseenter="hover = true" @mouseleave="hover = false" @focusin="hover = true" @focusout="hover = false"
                     role="region" aria-roledescription="carousel" aria-label="{{ __('معرض صور البرنامج ومساراته') }}"
                     class="relative aspect-[4/3] overflow-hidden rounded-[2.5rem] border-[5px] border-forest-800 bg-white shadow-[0_40px_80px_-30px_rgba(8,39,29,0.5)]">
                    @forelse ($heroSlides as $i => $s)
                        @php $isBanner = $s->image === 'images/hero-kasp-banner.jpg'; @endphp
                        {{-- الشريحة الأولى فوق البقية فتظهر وحدها قبل تحميل السكربت --}}
                        <div @class(['absolute inset-0 transition-opacity duration-1000 ease-out', 'z-10' => $i === 0])
                             :class="idx === {{ $i }} ? '' : 'pointer-events-none opacity-0'" :aria-hidden="(idx !== {{ $i }}).toString()"
                             role="group" aria-roledescription="slide" aria-label="{{ $i + 1 }} / {{ $slideCount }}">
                            <img src="{{ $s->imageUrl() }}" alt="{{ $isBanner ? __('شعار برنامج خادم الحرمين الشريفين للابتعاث مع برنامج تنمية القدرات البشرية ورؤية السعودية 2030') : $s->title() }}"
                                 @if ($i === 0) fetchpriority="high" @else loading="lazy" decoding="async" @endif
                                 @class(['h-full w-full object-cover', 'transition-transform duration-[6s] ease-out' => $s->overlay])
                                 @if ($s->overlay) :class="idx === {{ $i }} ? 'motion-safe:scale-110' : ''" @endif>
                            @if ($s->overlay)
                                <div class="absolute inset-0 bg-gradient-to-t from-forest-950/60 via-transparent to-transparent"></div>
                            @endif

                            @if ($s->title())
                                <a href="{{ $s->href() }}" tabindex="-1" :tabindex="idx === {{ $i }} ? 0 : -1"
                                   @class(['absolute inset-x-4 bottom-4 items-center justify-between gap-3 rounded-2xl bg-forest-900/95 px-4 py-3 shadow-lg shadow-forest-900/20 transition-colors hover:bg-forest-800 sm:inset-x-5 sm:bottom-5', 'hidden sm:flex' => ! $s->overlay, 'flex' => $s->overlay])>
                                    <div class="flex min-w-0 items-center gap-2.5">
                                        <x-lucide-badge-check class="size-5 shrink-0 text-gold-300" />
                                        <div class="min-w-0 leading-tight">
                                            <div class="truncate text-xs font-bold text-white">{{ $s->title() }}</div>
                                            <div class="truncate text-[11px] text-white/70">{{ $s->subtitle() }}</div>
                                        </div>
                                    </div>
                                    <span class="shrink-0 font-plex text-[10px] tracking-widest text-gold-300" dir="ltr">KASP '26</span>
                                </a>
                            @endif
                        </div>
                    @empty
                        <img src="{{ asset('images/hero-kasp-banner.jpg') }}" alt="{{ __('شعار برنامج خادم الحرمين الشريفين للابتعاث مع برنامج تنمية القدرات البشرية ورؤية السعودية 2030') }}"
                             class="h-full w-full object-cover" fetchpriority="high">
                    @endforelse

                    {{-- مؤشرات المعرض وزر الإيقاف (لا يُعرض معرض متحرك دون إمكانية إيقافه) --}}
                    @if ($slideCount > 1)
                    <div x-cloak class="absolute end-4 top-4 z-20 flex items-center gap-1.5 rounded-full bg-forest-900/80 py-1.5 pe-1.5 ps-3 backdrop-blur sm:end-5 sm:top-5">
                        @foreach ($heroSlides as $i => $s)
                            <button type="button" @click="go({{ $i }})" :aria-current="(idx === {{ $i }}).toString()"
                                    aria-label="{{ __('عرض الصورة :n', ['n' => $i + 1]) }}" class="h-1.5 rounded-full transition-all duration-500"
                                    :class="idx === {{ $i }} ? 'w-5 bg-gold-400' : 'w-1.5 bg-white/45 hover:bg-white/80'"></button>
                        @endforeach
                        <button type="button" @click="paused = ! paused"
                                :aria-label="paused ? @js(__('تشغيل العرض التلقائي')) : @js(__('إيقاف العرض التلقائي'))"
                                class="ms-1 grid size-6 place-items-center rounded-full text-white/80 transition-colors hover:bg-white/15 hover:text-white">
                            <x-lucide-pause class="size-3.5" x-show="! paused" />
                            <x-lucide-play class="size-3.5" x-show="paused" x-cloak />
                        </button>
                    </div>
                    @endif
                </div>

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
