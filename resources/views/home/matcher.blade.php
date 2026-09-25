<section id="matcher" class="pattern-star-dark grain relative overflow-hidden bg-forest-950 py-24 md:py-32"
         x-data="matcher(@js($tracks->map->toPublicArray()->values()), @js($universities->map->toPublicArray()->values()))">
    <div class="absolute -start-32 top-1/4 h-[28rem] w-[28rem] rounded-full bg-forest-600/20 blur-[140px]" aria-hidden="true"></div>
    <div class="absolute -end-24 bottom-0 h-[24rem] w-[24rem] rounded-full bg-gold-500/10 blur-[130px]" aria-hidden="true"></div>

    <div class="relative mx-auto max-w-7xl px-5 md:px-8">
        <x-section-heading dark center eyebrow="الفرز بالذكاء الاصطناعي"
                           desc="محرّك مطابقة مدرّب على بيانات آلاف المبتعثين ومتطلبات الجامعات العالمية. أجب بصدق، ودَع الخوارزمية ترسم لك الخطة.">
            أربعة أسئلة تفصلك عن
            <span class="text-shimmer-gold"> مسارك الأمثل</span>
        </x-section-heading>

        <div class="reveal relative mx-auto mt-14 max-w-4xl overflow-hidden rounded-[2rem] border border-white/10 bg-white/[0.05] shadow-[0_40px_90px_-30px_rgba(0,0,0,0.6)] backdrop-blur-xl">
            {{-- progress --}}
            <div class="relative h-1.5 w-full bg-white/10">
                <div class="h-full bg-gradient-to-l from-gold-500 via-gold-400 to-forest-400 transition-[width] duration-600 ease-out-expo" :style="`width: ${progress}%`" style="width: 0"></div>
            </div>

            <div class="flex items-center justify-between px-7 pt-6 md:px-10">
                <div class="flex items-center gap-2.5 text-sm font-bold text-gold-300">
                    <x-lucide-brain-circuit class="size-5" />
                    <span x-text="done ? 'اكتمل التحليل' : `السؤال ${step + 1} من ${questions.length}`">السؤال 1 من 4</span>
                </div>
                <button type="button" x-show="!done" @click="back()" :disabled="step === 0"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-white/50 transition-colors hover:text-white disabled:opacity-30">
                    <x-lucide-chevron-right class="size-4" />
                    السؤال السابق
                </button>
            </div>

            <div class="px-7 pb-10 pt-7 md:px-10">
                {{-- question --}}
                <template x-for="s in (done ? [] : [step])" :key="s">
                    <div class="animate-enter">
                        <h3 class="text-2xl font-bold text-white md:text-3xl" x-text="current.q"></h3>
                        <div class="mt-7 grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <template x-for="opt in current.options" :key="step + opt.label">
                                <button type="button" @click="choose(opt)"
                                        class="group relative overflow-hidden rounded-2xl border p-5 text-start transition-all duration-300"
                                        :class="answers[step]?.label === opt.label ? 'border-gold-400 bg-gold-500/15' : 'border-white/12 bg-white/[0.04] hover:-translate-y-1 hover:border-gold-400/60 hover:bg-white/[0.08]'">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <div class="text-[15px] font-bold text-white" x-text="opt.label"></div>
                                            <div x-show="opt.hint" class="mt-1 text-xs font-medium text-white/50" x-text="opt.hint"></div>
                                        </div>
                                        <span class="grid size-6 shrink-0 place-items-center rounded-full border transition-all"
                                              :class="answers[step]?.label === opt.label ? 'border-gold-400 bg-gold-400 text-forest-950' : 'border-white/25 text-transparent group-hover:border-gold-400/70'">
                                            <x-lucide-circle-check class="size-4" />
                                        </span>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- result --}}
                <template x-if="done && result">
                    <div class="animate-enter grid grid-cols-1 items-center gap-10 md:grid-cols-[auto_1fr]">
                        <div class="relative mx-auto">
                            <svg width="190" height="190" viewBox="0 0 190 190" class="-rotate-90" aria-hidden="true">
                                <circle cx="95" cy="95" r="68" fill="none" stroke-width="11" class="ring-track" />
                                <circle cx="95" cy="95" r="68" fill="none" stroke="url(#goldGrad)" stroke-width="11" stroke-linecap="round"
                                        :stroke-dasharray="ringCircumference" :stroke-dashoffset="ringOffset" />
                                <defs>
                                    <linearGradient id="goldGrad" x1="0" y1="0" x2="1" y2="1">
                                        <stop offset="0%" stop-color="#c9a338" />
                                        <stop offset="100%" stop-color="#2f8a68" />
                                    </linearGradient>
                                </defs>
                            </svg>
                            <div class="absolute inset-0 grid place-items-center text-center">
                                <div>
                                    <div class="font-plex text-5xl font-bold text-white" dir="ltr"><span x-text="shownMatch"></span>%</div>
                                    <div class="mt-1 text-xs font-bold text-gold-300">درجة التوافق</div>
                                </div>
                            </div>
                            <x-star-emblem class="absolute -start-3 -top-3 size-8 text-gold-500/50" stroke-width="1" />
                        </div>

                        <div>
                            <div class="inline-flex items-center gap-2 rounded-full bg-gold-500/15 px-4 py-1.5 text-xs font-bold text-gold-300">
                                <x-lucide-sparkles class="size-3.5" />
                                توصية الخوارزمية
                            </div>
                            <h3 class="mt-4 text-3xl font-bold text-white md:text-4xl" x-text="result.track.name"></h3>
                            <p class="mt-2 text-sm font-semibold text-forest-300" x-text="`${result.track.badge} — وأقرب بديل لك: ${result.runnerUp.name}`"></p>
                            <div class="mt-4 flex flex-wrap gap-2">
                                <span dir="ltr" class="rounded-full border border-white/15 bg-white/[0.06] px-3.5 py-1.5 font-plex text-xs font-bold text-white/80"
                                      x-text="`GPA ≥ ${result.track.gpa.replace('من 5.0', '/ 5.0')}`"></span>
                                <span class="rounded-full border border-white/15 bg-white/[0.06] px-3.5 py-1.5 text-xs font-bold text-white/80" x-text="result.track.ranking"></span>
                            </div>
                            <ul class="mt-5 grid gap-2">
                                <template x-for="p in result.track.perks.slice(0, 3)" :key="p">
                                    <li class="flex items-start gap-2.5 text-sm text-slate-300">
                                        <x-lucide-badge-check class="mt-0.5 size-4.5 shrink-0 text-gold-400" />
                                        <span x-text="p"></span>
                                    </li>
                                </template>
                            </ul>

                            <div class="mt-6 rounded-2xl border border-white/10 bg-white/[0.04] p-4">
                                <div class="flex items-center gap-2 text-xs font-bold text-white/60">
                                    <x-lucide-scan-search class="size-4 text-gold-400" />
                                    جامعات مقترحة لملفك
                                </div>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <template x-for="u in result.unis" :key="u.id">
                                        <span class="inline-flex items-center gap-2 rounded-full border border-gold-500/30 bg-gold-500/10 px-3.5 py-1.5 text-xs font-bold text-gold-200">
                                            <span class="font-plex text-gold-400" dir="ltr" x-text="`#${u.rank}`"></span>
                                            <span x-text="u.nameAr"></span>
                                        </span>
                                    </template>
                                </div>
                            </div>

                            <div class="mt-7 flex flex-wrap gap-3">
                                <a href="#journey" class="group inline-flex items-center gap-2 rounded-full bg-gold-500 px-7 py-3.5 text-sm font-bold text-forest-950 transition-all hover:bg-gold-400 hover:shadow-[0_12px_36px_-10px_rgba(201,163,56,0.6)]">
                                    ابدأ رحلة التقديم
                                    <x-lucide-arrow-left class="size-4 transition-transform group-hover:-translate-x-1" />
                                </a>
                                <button type="button" @click="reset()" class="inline-flex items-center gap-2 rounded-full border border-white/20 px-7 py-3.5 text-sm font-bold text-white transition-colors hover:border-gold-400 hover:text-gold-300">
                                    <x-lucide-rotate-ccw class="size-4" />
                                    إعادة الاختبار
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <p class="mx-auto mt-6 max-w-xl text-center text-xs leading-relaxed text-white/40">
            النتائج استرشادية وتُبنى على إجاباتك الفورية؛ يُجري فريق القبول مراجعة
            بشرية شاملة لكل ملف قبل اعتماد المسار النهائي.
        </p>
    </div>
</section>
