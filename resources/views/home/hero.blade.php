@php
    $destinations = ['أكسفورد', 'هارفارد', 'إم آي تي', 'سنغافورة', 'طوكيو', 'ستانفورد'];
    $trust = [['v' => '98%', 'l' => 'معدّل رضا المبتعثين'], ['v' => '72h', 'l' => 'متوسط زمن الفرز الذكي'], ['v' => '01', 'l' => 'تصنيف عربي في مخرجات البحث']];
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
                <span class="text-[13px] font-semibold text-forest-800">فتح باب التقديم للعام الأكاديمي 1447هـ — 2026م</span>
            </div>

            <h1 class="animate-enter mt-7 text-[2.65rem] font-bold leading-[1.14] tracking-tight text-ink md:text-6xl md:leading-[1.12]" style="--delay: .1s">
                مستقبلُك يبدأ من
                <br>
                أعرق <span class="text-shimmer-gold">جامعات العالم</span>
            </h1>

            <p class="animate-enter mt-6 max-w-xl text-lg leading-relaxed text-slate-600" style="--delay: .2s">
                منصّة رقمية واحدة تجمع كل ما تحتاجه رحلة ابتعاثك: اكتشاف المسار
                الأنسب، مطابقة ذكية بالذكاء الاصطناعي، قبول مباشر في الجامعات
                الشريكة، ومتابعة أكاديمية من التقديم حتى التخرج — بروح
                <span class="font-bold text-forest-700">رؤية السعودية 2030</span>.
            </p>

            {{-- destination rotator --}}
            <div x-data="heroRotator(@js($destinations))" class="animate-enter mt-6 flex items-center gap-2 text-[15px] font-semibold text-slate-500" style="--delay: .3s">
                <x-lucide-globe class="size-4.5 text-forest-600" />
                <span>وجهتك القادمة:</span>
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
                    اكتشف مسارك بالذكاء الاصطناعي
                    <x-lucide-arrow-up-left class="size-4 transition-transform duration-300 group-hover:-translate-x-1 group-hover:-translate-y-1" />
                </a>
                <a href="#tracks" class="inline-flex items-center justify-center gap-2.5 rounded-full border border-forest-700/30 px-8 py-4 text-sm font-bold text-forest-800 transition-all duration-300 hover:-translate-y-0.5 hover:border-forest-700 hover:bg-forest-700 hover:text-white">
                    <x-lucide-play class="size-4" />
                    استكشف المسارات الستة
                </a>
            </div>

            {{-- mini trust row --}}
            <div class="animate-enter mt-12 flex flex-wrap items-center gap-x-10 gap-y-4 border-t border-forest-800/10 pt-7" style="--delay: .55s">
                @foreach ($trust as $s)
                    <div class="leading-tight">
                        <div class="font-plex text-2xl font-bold text-forest-800" dir="ltr">{{ $s['v'] }}</div>
                        <div class="mt-1 text-xs font-medium text-slate-500">{{ $s['l'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- visual --}}
        <div class="relative lg:col-span-6">
            <div class="animate-enter relative mx-auto max-w-md lg:max-w-none" style="--delay: .25s">
                <x-star-emblem class="animate-spin-slower absolute -top-12 end-4 size-40 text-forest-700/15 md:size-56" stroke-width="0.8" />
                <x-star-emblem class="animate-spin-slower absolute -bottom-10 start-2 size-24 text-gold-500/25" stroke-width="0.8" />

                <div class="relative overflow-hidden rounded-[2.5rem] border-[5px] border-forest-800 shadow-[0_40px_80px_-30px_rgba(8,39,29,0.5)]">
                    <img src="{{ asset('images/hero-students-group.jpg') }}" alt="مجموعة من الطلاب السعوديين المبتعثين في أحد الحرم الجامعية العالمية"
                         class="aspect-[4/3] w-full object-cover transition-transform duration-700 hover:scale-105" fetchpriority="high">
                    <div class="absolute inset-0 bg-gradient-to-t from-forest-950/55 via-transparent to-transparent"></div>

                    <div class="absolute start-4 top-4 flex items-center gap-2 rounded-full border border-white/20 bg-forest-950/55 py-2 pe-4 ps-2 backdrop-blur-md">
                        <span class="flex -space-x-2" dir="ltr">
                            @foreach (['س', 'ن', 'م', 'ر'] as $ch)
                                <span class="grid size-6 place-items-center rounded-full border-2 border-white/70 bg-gradient-to-br from-gold-400 to-forest-700 text-[10px] font-bold text-white">{{ $ch }}</span>
                            @endforeach
                        </span>
                        <span class="text-[11px] font-bold text-white">طلابنا في 57 دولة</span>
                    </div>

                    <div class="absolute inset-x-5 bottom-5 flex items-center justify-between rounded-2xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur-md">
                        <div class="flex items-center gap-2.5">
                            <x-lucide-badge-check class="size-5 text-gold-300" />
                            <div class="leading-tight">
                                <div class="text-xs font-bold text-white">قبول نهائي</div>
                                <div class="text-[11px] text-white/70">جامعة أكسفورد — طب</div>
                            </div>
                        </div>
                        <span class="font-plex text-[10px] tracking-widest text-gold-300" dir="ltr">KASP '26</span>
                    </div>
                </div>

                {{-- floating chip: AI --}}
                <div class="animate-float-y absolute -top-7 start-3 rounded-2xl border border-forest-800/10 bg-white/90 p-4 shadow-xl shadow-forest-900/10 backdrop-blur md:-start-5">
                    <div class="flex items-center gap-3">
                        <span class="grid size-10 place-items-center rounded-xl bg-forest-700 text-gold-300">
                            <x-lucide-brain-circuit class="size-5" />
                        </span>
                        <div class="leading-tight">
                            <div class="text-[11px] font-medium text-slate-500">مطابقة المسار الذكية</div>
                            <div class="flex items-baseline gap-1.5">
                                <span class="font-plex text-xl font-bold text-forest-800" dir="ltr">98.2%</span>
                                <span class="text-[11px] font-bold text-emerald-600">توافق ممتاز</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 h-1.5 w-44 overflow-hidden rounded-full bg-slate-100">
                        <div x-data="{ w: 0 }" x-init="setTimeout(() => w = 98.2, 1100)" :style="`width: ${w}%`"
                             class="h-full w-0 rounded-full bg-gradient-to-l from-gold-500 to-forest-600 transition-[width] duration-[1400ms] ease-out-expo"></div>
                    </div>
                </div>

                {{-- floating chip: vision --}}
                <div class="animate-float-y-soft absolute -bottom-8 end-4 rounded-2xl bg-forest-800 p-4 pe-6 text-white shadow-2xl shadow-forest-900/40 md:-end-4">
                    <div class="flex items-center gap-3">
                        <x-star-emblem class="size-8 text-gold-400" />
                        <div class="leading-tight">
                            <div class="font-plex text-lg font-bold text-gold-300" dir="ltr">Vision 2030</div>
                            <div class="text-[11px] text-white/70">رأس مال بشري ينافس عالميًا</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
