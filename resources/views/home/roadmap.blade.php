@php $count = $stations->count(); @endphp

<section id="journey" class="pattern-star-dark grain relative overflow-hidden bg-forest-950 py-24 md:py-32" x-data="roadmap({{ $count }})">
    <div class="absolute -top-32 start-1/4 h-[26rem] w-[26rem] rounded-full bg-forest-600/20 blur-[140px]" aria-hidden="true"></div>
    <div class="absolute -bottom-40 end-0 h-[26rem] w-[26rem] rounded-full bg-gold-500/10 blur-[140px]" aria-hidden="true"></div>

    <div class="relative mx-auto max-w-7xl px-5 md:px-8">
        <x-section-heading dark center eyebrow="خارطة الطريق الأكاديمية"
                           desc="ثماني محطات واضحة تنظم مسيرتك الدراسية وتضمن حصولك على الدعم الكامل في كل خطوة.">
            رحلة الابتعاث من الفكرة
            <span class="text-shimmer-gold"> إلى التميّز</span>
        </x-section-heading>

        @if ($count > 0)
            {{-- station rail --}}
            <div class="reveal no-scrollbar -mx-5 mt-16 overflow-x-auto px-5 pb-2" style="--delay: .15s">
                <div class="relative min-w-[860px]">
                    <div class="absolute end-[6.25%] start-[6.25%] top-[21px] h-[3px] rounded-full bg-white/10">
                        <div class="h-full rounded-full bg-gradient-to-l from-gold-500 via-gold-400 to-forest-400 shadow-[0_0_16px_rgba(201,163,56,0.5)] transition-[width] duration-600 ease-out-expo"
                             :style="`width: ${last > 0 ? (active / last) * 100 : 0}%`" style="width: 0"></div>
                    </div>

                    <ol class="relative grid" style="grid-template-columns: repeat({{ $count }}, minmax(0, 1fr))">
                        @foreach ($stations as $i => $s)
                            <li>
                                <button type="button" @click="active = {{ $i }}" aria-label="المحطة {{ $s->code }}: {{ $s->title }}" :aria-current="active === {{ $i }} ? 'step' : null"
                                        class="group relative flex w-full flex-col items-center gap-3 outline-none">
                                    <span class="relative z-10 grid size-11 place-items-center rounded-full border-2 font-plex text-sm font-bold transition-all duration-500"
                                          :class="active === {{ $i }} ? 'scale-110 border-gold-400 bg-gold-500 text-forest-950 shadow-[0_0_28px_rgba(201,163,56,0.55)]'
                                              : (active > {{ $i }} ? 'border-forest-500 bg-forest-600 text-gold-200' : 'border-white/15 bg-forest-950 text-white/40 group-hover:border-gold-400/50 group-hover:text-gold-300')">
                                        <span x-show="!(active > {{ $i }})">{{ $s->code }}</span>
                                        <x-lucide-check class="size-5" x-show="active > {{ $i }}" x-cloak />
                                        <span x-show="active === {{ $i }}" class="animate-pulse-ring absolute inset-0 rounded-full bg-gold-400/40"></span>
                                    </span>
                                    <span class="text-[13px] font-bold transition-colors duration-300"
                                          :class="active === {{ $i }} ? 'text-gold-300' : (active > {{ $i }} ? 'text-white/75' : 'text-white/40 group-hover:text-white/70')">{{ $s->title }}</span>
                                </button>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>

            {{-- detail panel --}}
            <div class="reveal relative mx-auto mt-10 max-w-6xl overflow-hidden rounded-[2rem] border border-white/10 bg-white/[0.05] shadow-[0_40px_90px_-30px_rgba(0,0,0,0.6)] backdrop-blur-xl" style="--delay: .2s">
                @foreach ($stations as $i => $s)
                    <div x-show="active === {{ $i }}" @if ($i > 0) x-cloak @endif
                         x-transition:enter="transition duration-500 ease-out-expo" x-transition:enter-start="translate-y-7 opacity-0" x-transition:enter-end="translate-y-0 opacity-100">
                        <span dir="ltr" class="pointer-events-none absolute -top-10 end-6 select-none font-plex text-[10rem] font-bold leading-none text-white/[0.04]" aria-hidden="true">{{ $s->code }}</span>

                        <div class="relative grid items-center gap-10 p-8 md:p-12 lg:grid-cols-[260px_1fr] lg:gap-16">
                            <div class="relative mx-auto aspect-square w-full max-w-[260px]">
                                <x-star-emblem class="animate-spin-slower absolute inset-0 size-full text-white/10" stroke-width="0.5" />
                                <x-star-emblem class="animate-spin-slower absolute inset-[14%] size-[72%] text-gold-500/25 [animation-direction:reverse]" stroke-width="0.8" />
                                <div class="absolute inset-[26%] grid place-items-center rounded-[2rem] border border-white/10 bg-forest-800 shadow-[0_24px_60px_-16px_rgba(8,39,29,0.8)]">
                                    @svg('lucide-'.$s->icon, 'size-14 text-gold-400', ['stroke-width' => 1.5])
                                </div>
                                <span class="absolute -bottom-1 start-1/2 inline-flex -translate-x-1/2 items-center gap-1.5 whitespace-nowrap rounded-full border border-gold-500/40 bg-forest-950/90 px-4 py-2 text-[11px] font-bold text-gold-300 shadow-lg backdrop-blur">
                                    <x-lucide-clock-3 class="size-3.5" />
                                    {{ $s->duration }}
                                </span>
                            </div>

                            <div>
                                <div class="flex flex-wrap items-center gap-3">
                                    <span class="inline-flex items-center gap-2 rounded-full bg-gold-500/15 px-4 py-1.5 text-xs font-bold text-gold-300">
                                        <x-lucide-sparkles class="size-3.5" />
                                        المحطة {{ $s->code }} من {{ str_pad((string) $count, 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        @foreach ($stations as $j => $_)
                                            <span @class([
                                                'h-1.5 rounded-full',
                                                'w-7 bg-gold-400' => $j === $i,
                                                'w-1.5 bg-forest-500' => $j < $i,
                                                'w-1.5 bg-white/15' => $j > $i,
                                            ])></span>
                                        @endforeach
                                    </span>
                                </div>

                                <h3 class="mt-5 text-3xl font-bold text-white md:text-4xl">{{ $s->title }}</h3>
                                <p class="mt-4 max-w-2xl text-base leading-loose text-slate-300 md:text-lg md:leading-loose">{{ $s->description }}</p>

                                @if ($s->detail)
                                    <div class="mt-5 max-w-2xl rounded-2xl border-s-2 border-gold-500 bg-white/[0.04] p-4 pe-6">
                                        <p class="text-sm leading-relaxed text-slate-400">{{ $s->detail }}</p>
                                    </div>
                                @endif

                                <ul class="mt-5 flex flex-wrap gap-2">
                                    @foreach ($s->points as $p)
                                        <li class="inline-flex items-center gap-2 rounded-full border border-white/12 bg-white/[0.05] px-4 py-2 text-xs font-bold text-slate-200">
                                            <x-lucide-circle-check class="size-4 text-gold-400" />
                                            {{ $p }}
                                        </li>
                                    @endforeach
                                </ul>

                                <div class="mt-9 flex flex-wrap items-center gap-3">
                                    <button type="button" @click="prev()" @disabled($i === 0)
                                            class="inline-flex items-center gap-2 rounded-full border border-white/20 px-6 py-3 text-sm font-bold text-white transition-all hover:border-gold-400 hover:text-gold-300 disabled:opacity-30 disabled:hover:border-white/20 disabled:hover:text-white">
                                        <x-lucide-arrow-right class="size-4" />
                                        المحطة السابقة
                                    </button>
                                    @if ($i < $count - 1)
                                        <button type="button" @click="next()"
                                                class="group inline-flex items-center gap-2 rounded-full bg-gold-500 px-6 py-3 text-sm font-bold text-forest-950 transition-all hover:bg-gold-400 hover:shadow-[0_12px_36px_-10px_rgba(201,163,56,0.6)]">
                                            المحطة التالية
                                            <x-lucide-arrow-left class="size-4 transition-transform group-hover:-translate-x-1" />
                                        </button>
                                    @else
                                        <a href="#matcher"
                                           class="group inline-flex items-center gap-2 rounded-full bg-gold-500 px-6 py-3 text-sm font-bold text-forest-950 transition-all hover:bg-gold-400 hover:shadow-[0_12px_36px_-10px_rgba(201,163,56,0.6)]">
                                            ابدأ رحلتك الآن
                                            <x-lucide-sparkles class="size-4" />
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <p class="mx-auto mt-7 max-w-lg text-center text-xs leading-relaxed text-white/40">
            اضغط على أي محطة في الخط أعلاه لاستعراض تفاصيلها والتنقل بحرية بين مراحل رحلتك.
        </p>
    </div>
</section>
