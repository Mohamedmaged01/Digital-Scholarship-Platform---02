{{-- محرك التوجيه (ملاحظة 31): المطابقة مبنية مباشرة على ضوابط كل مسار — الدرجات ونطاق التخصصات والشروط الخاصة --}}
<section id="matcher" class="pattern-star-dark grain relative overflow-hidden bg-forest-950 py-24 md:py-32" x-data="matcher(@js($matcher))">
    <div class="absolute -start-32 top-1/4 h-[28rem] w-[28rem] rounded-full bg-forest-600/20 blur-[140px]" aria-hidden="true"></div>
    <div class="absolute -end-24 bottom-0 h-[24rem] w-[24rem] rounded-full bg-gold-500/10 blur-[130px]" aria-hidden="true"></div>

    <div class="relative mx-auto max-w-7xl px-5 md:px-8">
        <x-section-heading dark center eyebrow="{{ __('محرك التوجيه') }}"
                           desc="{{ __('اختر الدرجة العلمية والمجال، وسنعرض لك المسارات التي تنطبق ضوابطها على اختياراتك مع شروطها الخاصة.') }}">
            {{ __('اكتشف المسار') }}
            <span class="text-shimmer-gold"> {{ __('الأنسب لك') }}</span>
        </x-section-heading>

        <div class="reveal relative mx-auto mt-10 max-w-4xl overflow-hidden rounded-[2rem] border border-white/10 bg-white/[0.05] shadow-[0_40px_90px_-30px_rgba(0,0,0,0.6)] backdrop-blur-xl">
            <div class="relative h-1.5 w-full bg-white/10">
                <div class="h-full bg-gradient-to-l from-gold-500 via-gold-400 to-forest-400 transition-[width] duration-600 ease-out-expo" :style="`width: ${progress}%`" style="width: 0"></div>
            </div>

            <div class="flex items-center justify-between px-7 pt-6 md:px-10">
                <div class="flex items-center gap-2.5 text-sm font-bold text-gold-300">
                    <x-lucide-list-checks class="size-5" />
                    <span x-text="done ? @js(__('المسارات المطابقة لاختياراتك')) : @js(__('الخطوة :step من 2')).replace(':step', step + 1)">{{ __('الخطوة 1 من 2') }}</span>
                </div>
                <button type="button" x-show="step > 0 && !done" x-cloak @click="step--"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-white/50 transition-colors hover:text-white">
                    <x-lucide-chevron-right class="rtl-flip size-4" />
                    {{ __('الخطوة السابقة') }}
                </button>
            </div>

            <div class="px-7 pb-10 pt-7 md:px-10">
                {{-- الأسئلة: كل خطوة تُعاد تركيبها لتعيد حركة الظهور --}}
                <template x-for="s in (done ? [] : [step])" :key="s">
                    <div class="animate-enter">
                        <template x-if="step === 0">
                            <div>
                                <h3 class="text-2xl font-bold text-white md:text-3xl">{{ __('ما الدرجة العلمية التي تستهدفها؟') }}</h3>
                                <p class="mt-2 text-sm text-slate-400">{{ __('سنعرض المسارات التي تشمل هذه الدرجة وفق ضوابطها') }}</p>
                                <div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <template x-for="d in data.degrees" :key="d.id">
                                        <button type="button" @click="degree = d.id" :aria-pressed="degree === d.id"
                                                class="rounded-2xl border p-5 text-start transition-all duration-300"
                                                :class="degree === d.id ? 'border-gold-400 bg-gold-500/15' : 'border-white/12 bg-white/[0.04] hover:border-gold-400/60 hover:bg-white/[0.08]'">
                                            <div class="flex items-center justify-between">
                                                <span class="text-[15px] font-bold text-white" x-text="d.name"></span>
                                                <span class="grid size-6 place-items-center rounded-full border transition-all"
                                                      :class="degree === d.id ? 'border-gold-400 bg-gold-400 text-forest-950' : 'border-white/25 text-transparent'">
                                                    <x-lucide-circle-check class="size-4" />
                                                </span>
                                            </div>
                                            <div class="mt-1 font-plex text-xs text-white/50" dir="ltr" x-text="d.nameEn"></div>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <template x-if="step === 1">
                            <div>
                                <h3 class="text-2xl font-bold text-white md:text-3xl">{{ __('ما المجال المعرفي لتخصصك؟') }}</h3>
                                <p class="mt-2 text-sm text-slate-400">{{ __('سنطابقه مع نطاق التخصصات المحدد في كل مسار') }}</p>
                                <div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <template x-for="f in fields" :key="f.id">
                                        <button type="button" @click="field = f.id" :aria-pressed="field === f.id"
                                                class="rounded-2xl border p-4 text-start transition-all duration-300"
                                                :class="field === f.id ? 'border-gold-400 bg-gold-500/15' : 'border-white/12 bg-white/[0.04] hover:border-gold-400/60 hover:bg-white/[0.08]'">
                                            <div class="flex items-center justify-between gap-3">
                                                <span class="text-[14px] font-bold text-white" x-text="f.name"></span>
                                                <span class="grid size-5 shrink-0 place-items-center rounded-full border transition-all"
                                                      :class="field === f.id ? 'border-gold-400 bg-gold-400 text-forest-950' : 'border-white/25 text-transparent'">
                                                    <x-lucide-circle-check class="size-3.5" />
                                                </span>
                                            </div>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <div class="mt-8 flex justify-end">
                            <button type="button" @click="next()" :disabled="!canNext"
                                    class="inline-flex items-center gap-2 rounded-full px-8 py-3.5 text-sm font-bold transition-all"
                                    :class="canNext ? 'bg-gold-500 text-forest-950 hover:bg-gold-400 hover:shadow-[0_12px_36px_-10px_rgba(201,163,56,0.6)]' : 'cursor-not-allowed bg-white/10 text-white/30'">
                                <span x-text="step === 1 ? @js(__('عرض النتائج')) : @js(__('التالي'))">{{ __('التالي') }}</span>
                                <x-lucide-arrow-left class="rtl-flip size-4" />
                            </button>
                        </div>
                    </div>
                </template>

                {{-- النتائج --}}
                <template x-if="done">
                    <div class="animate-enter space-y-6">
                        <div class="flex flex-wrap items-center gap-2 text-xs font-bold text-slate-400">
                            <span class="rounded-full bg-white/10 px-3 py-1.5 text-white/80" x-text="degreeName"></span>
                            <x-lucide-chevron-left class="rtl-flip size-3.5" />
                            <span class="rounded-full bg-white/10 px-3 py-1.5 text-white/80" x-text="fieldName"></span>
                            <span class="ms-auto font-plex text-gold-300" x-text="@js(__(':count مسار')).replace(':count', results.length)"></span>
                        </div>

                        <div x-show="results.length === 0" class="rounded-2xl border-2 border-dashed border-white/15 py-14 text-center">
                            <x-lucide-book-open class="mx-auto mb-3 size-9 text-slate-500" />
                            <p class="text-sm font-bold text-slate-400">{{ __('لا توجد مسارات تنطبق ضوابطها على هذه الاختيارات') }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ __('جرّب تغيير الدرجة العلمية أو المجال المعرفي') }}</p>
                        </div>

                        <div class="space-y-4">
                            <template x-for="r in results" :key="r.track.id">
                                <a :href="r.track.url" class="block rounded-2xl border border-white/10 bg-white/[0.04] p-5 transition-all hover:-translate-y-0.5 hover:border-gold-400/50">
                                    <div class="flex flex-wrap items-start justify-between gap-3">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="rounded bg-white/10 px-2 py-0.5 font-plex text-[10px] font-bold text-white/60" dir="ltr" x-text="r.track.code"></span>
                                                <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold"
                                                      :class="r.track.status === 'open' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-white/10 text-white/60'" x-text="r.track.statusLabel"></span>
                                            </div>
                                            <h4 class="mt-2 text-xl font-bold text-white" x-text="r.track.name"></h4>
                                        </div>
                                        <span class="inline-flex items-center gap-1 text-[12px] font-bold text-gold-300">{{ __('صفحة المسار') }} <x-lucide-arrow-left class="rtl-flip size-3.5" /></span>
                                    </div>

                                    <dl class="mt-4 grid gap-2 text-[12px] sm:grid-cols-2">
                                        <div class="rounded-xl bg-white/[0.04] px-3 py-2.5">
                                            <dt class="font-bold text-slate-500">{{ __('التخصصات') }}</dt>
                                            <dd class="mt-0.5 font-bold text-white/85" x-text="r.majorsNote"></dd>
                                        </div>
                                        <div class="rounded-xl bg-white/[0.04] px-3 py-2.5">
                                            <dt class="font-bold text-slate-500">{{ __('المؤسسات التعليمية') }}</dt>
                                            <dd class="mt-0.5 font-bold text-white/85" x-text="r.track.institutions"></dd>
                                        </div>
                                    </dl>

                                    <div x-show="r.majors.length" class="mt-3 flex flex-wrap gap-1.5">
                                        <template x-for="m in r.majors.slice(0, 6)" :key="m.name">
                                            <span class="rounded-full bg-gold-500/10 px-2.5 py-1 text-[11px] font-bold text-gold-200" x-text="m.name"></span>
                                        </template>
                                        <span x-show="r.majors.length > 6" class="px-1 text-[11px] text-slate-500" x-text="`+ ${r.majors.length - 6}`"></span>
                                    </div>

                                    <ul x-show="r.track.rules.length" class="mt-3 space-y-1.5 border-t border-white/10 pt-3">
                                        <template x-for="rule in r.track.rules" :key="rule">
                                            <li class="flex items-start gap-2 text-[12px] leading-relaxed text-slate-300">
                                                <x-lucide-circle-check class="mt-0.5 size-3.5 shrink-0 text-gold-400" />
                                                <span x-text="rule"></span>
                                            </li>
                                        </template>
                                    </ul>
                                </a>
                            </template>
                        </div>

                        <div class="flex flex-wrap gap-3 pt-2">
                            <a href="#tracks" class="group inline-flex items-center gap-2 rounded-full bg-gold-500 px-7 py-3.5 text-sm font-bold text-forest-950 transition-all hover:bg-gold-400">
                                {{ __('استكشف المسارات بالتفصيل') }}
                                <x-lucide-arrow-left class="rtl-flip size-4" />
                            </a>
                            <button type="button" @click="reset()" class="inline-flex items-center gap-2 rounded-full border border-white/20 px-7 py-3.5 text-sm font-bold text-white transition-colors hover:border-gold-400 hover:text-gold-300">
                                <x-lucide-rotate-ccw class="size-4" />
                                {{ __('إعادة التوجيه') }}
                            </button>
                        </div>

                        <p class="text-[11px] leading-relaxed text-slate-500">
                            <x-lucide-info class="me-1 inline size-3.5 align-[-2px]" />
                            {{ __('المطابقة مبنية على ضوابط كل مسار. القبول الجامعي يصدر من المؤسسة التعليمية، والترشيح يصدر في برنامج الابتعاث بعد دراسة الطلب.') }}
                        </p>
                    </div>
                </template>
            </div>
        </div>
    </div>
</section>
