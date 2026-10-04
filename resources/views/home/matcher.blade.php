@php
    $regions = ['all' => 'لا تفضيل — أعرض الكل'] + config('kasp.regions');
@endphp

<section id="matcher" class="pattern-star-dark grain relative overflow-hidden bg-forest-950 py-24 md:py-32" x-data="matcher(@js($matcher))">
    <div class="absolute -start-32 top-1/4 h-[28rem] w-[28rem] rounded-full bg-forest-600/20 blur-[140px]" aria-hidden="true"></div>
    <div class="absolute -end-24 bottom-0 h-[24rem] w-[24rem] rounded-full bg-gold-500/10 blur-[130px]" aria-hidden="true"></div>

    <div class="relative mx-auto max-w-7xl px-5 md:px-8">
        <x-section-heading dark center eyebrow="محرك التوجيه الاسترشادي"
                           desc="أجب عن ثلاثة أسئلة بسيطة وسنطابق اختياراتك مع المسارات والتخصصات والجامعات المرتبطة بها فعليًا في بيانات المنصة.">
            اكتشف المسار
            <span class="text-shimmer-gold"> الأقرب لطموحك</span>
        </x-section-heading>

        {{-- تنبيه استرشادي ثابت فوق المحرك (§G) --}}
        <p class="reveal mx-auto mt-6 max-w-2xl rounded-2xl border border-amber-400/30 bg-amber-500/10 px-5 py-3 text-center text-[12px] font-semibold leading-relaxed text-amber-200">
            <x-lucide-triangle-alert class="me-1.5 inline size-3.5 align-[-2px]" />
            النتائج استرشادية ولا تمثل قبولًا أو أهلية نهائية. يجب الرجوع إلى الشروط والضوابط والمصادر الرسمية المعتمدة لكل مسار.
        </p>

        <div class="reveal relative mx-auto mt-10 max-w-4xl overflow-hidden rounded-[2rem] border border-white/10 bg-white/[0.05] shadow-[0_40px_90px_-30px_rgba(0,0,0,0.6)] backdrop-blur-xl">
            <div class="relative h-1.5 w-full bg-white/10">
                <div class="h-full bg-gradient-to-l from-gold-500 via-gold-400 to-forest-400 transition-[width] duration-600 ease-out-expo" :style="`width: ${progress}%`" style="width: 0"></div>
            </div>

            <div class="flex items-center justify-between px-7 pt-6 md:px-10">
                <div class="flex items-center gap-2.5 text-sm font-bold text-gold-300">
                    <x-lucide-brain-circuit class="size-5" />
                    <span x-text="done ? 'نتائج التوجيه الاسترشادي' : `الخطوة ${step + 1} من 3`">الخطوة 1 من 3</span>
                </div>
                <button type="button" x-show="step > 0 && !done" x-cloak @click="step--"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-white/50 transition-colors hover:text-white">
                    <x-lucide-chevron-right class="size-4" />
                    الخطوة السابقة
                </button>
            </div>

            <div class="px-7 pb-10 pt-7 md:px-10">
                {{-- الأسئلة: كل خطوة تُعاد تركيبها لتعيد حركة الظهور --}}
                <template x-for="s in (done ? [] : [step])" :key="s">
                    <div class="animate-enter">
                        <template x-if="step === 0">
                            <div>
                                <h3 class="text-2xl font-bold text-white md:text-3xl">ما الدرجة العلمية التي تستهدفها؟</h3>
                                <p class="mt-2 text-sm text-slate-400">سنعرض لك المسارات التي تدعم هذه الدرجة فعليًا</p>
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
                                <h3 class="text-2xl font-bold text-white md:text-3xl">أي مجال معرفي يهمّك؟</h3>
                                <p class="mt-2 text-sm text-slate-400">سنطابق المجال مع التخصصات والجامعات المتاحة في كل مسار</p>
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

                        <template x-if="step === 2">
                            <div>
                                <h3 class="text-2xl font-bold text-white md:text-3xl">هل لديك تفضيل جغرافي للدراسة؟</h3>
                                <p class="mt-2 text-sm text-slate-400">اختياري — يمكنك تخطي هذه الخطوة</p>
                                <div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    @foreach ($regions as $key => $label)
                                        <button type="button" @click="region = '{{ $key }}'" :aria-pressed="region === '{{ $key }}'"
                                                class="rounded-2xl border p-4 text-start transition-all duration-300"
                                                :class="region === '{{ $key }}' ? 'border-gold-400 bg-gold-500/15' : 'border-white/12 bg-white/[0.04] hover:border-gold-400/60 hover:bg-white/[0.08]'">
                                            <div class="flex items-center justify-between gap-3">
                                                <span class="text-[14px] font-bold text-white">{{ $label }}</span>
                                                <span class="grid size-5 shrink-0 place-items-center rounded-full border transition-all"
                                                      :class="region === '{{ $key }}' ? 'border-gold-400 bg-gold-400 text-forest-950' : 'border-white/25 text-transparent'">
                                                    <x-lucide-circle-check class="size-3.5" />
                                                </span>
                                            </div>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </template>

                        <div class="mt-8 flex justify-end">
                            <button type="button" @click="next()" :disabled="!canNext"
                                    class="inline-flex items-center gap-2 rounded-full px-8 py-3.5 text-sm font-bold transition-all"
                                    :class="canNext ? 'bg-gold-500 text-forest-950 hover:bg-gold-400 hover:shadow-[0_12px_36px_-10px_rgba(201,163,56,0.6)]' : 'cursor-not-allowed bg-white/10 text-white/30'">
                                <span x-text="step === 2 ? 'عرض النتائج' : 'التالي'">التالي</span>
                                <x-lucide-arrow-left class="size-4" />
                            </button>
                        </div>
                    </div>
                </template>

                {{-- النتائج --}}
                <template x-if="done">
                    <div class="animate-enter space-y-6">
                        <div class="rounded-2xl border border-amber-400/40 bg-amber-500/15 px-5 py-4 text-[13px] font-semibold leading-relaxed text-amber-200">
                            <x-lucide-triangle-alert class="me-2 inline size-4 align-[-3px]" />
                            <strong>تنبيه مهم:</strong> هذه النتائج استرشادية فقط بناءً على إجاباتك. لا تمثل قبولًا أو أهلية نهائية.
                            يجب الرجوع إلى الشروط والضوابط والدليل الاسترشادي المعتمد لكل مسار قبل التقديم.
                        </div>

                        <h3 class="text-xl font-bold text-white">
                            المسارات المطابقة لاختياراتك
                            <span class="ms-2 font-plex text-base text-gold-300" x-text="`(${results.length})`"></span>
                        </h3>

                        <div x-show="results.length === 0" class="rounded-2xl border-2 border-dashed border-white/15 py-14 text-center">
                            <x-lucide-book-open class="mx-auto mb-3 size-9 text-slate-500" />
                            <p class="text-sm font-bold text-slate-400">لا توجد مسارات مطابقة لهذه المعايير حاليًا</p>
                            <p class="mt-1 text-xs text-slate-500">جرّب تغيير الدرجة العلمية أو المجال المعرفي</p>
                        </div>

                        <div class="space-y-4">
                            <template x-for="(r, idx) in results" :key="r.track.id">
                                <a :href="r.track.url" class="block rounded-2xl border p-5 transition-all hover:-translate-y-0.5"
                                   :class="idx === 0 ? 'border-gold-400/50 bg-gold-500/10 hover:border-gold-400' : 'border-white/10 bg-white/[0.04] hover:border-white/25'">
                                    <div class="flex flex-wrap items-start justify-between gap-3">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span x-show="idx === 0" class="inline-flex items-center gap-1.5 rounded-full bg-gold-500/20 px-3 py-1 text-[10px] font-bold text-gold-300">
                                                    <x-lucide-sparkles class="size-3" /> الأقرب لاختياراتك
                                                </span>
                                                <span class="rounded bg-white/10 px-2 py-0.5 font-plex text-[10px] font-bold text-white/60" dir="ltr" x-text="r.track.code"></span>
                                            </div>
                                            <h4 class="mt-2 text-xl font-bold text-white" x-text="r.track.name"></h4>
                                            <p class="mt-1 text-sm text-slate-400" x-text="r.track.badge"></p>
                                        </div>
                                        <div class="text-end">
                                            <div class="font-plex text-3xl font-bold text-gold-400" dir="ltr"><span x-text="r.score"></span><span class="text-lg text-gold-300/70">%</span></div>
                                            <div class="text-[10px] font-bold text-slate-500">درجة التطابق</div>
                                        </div>
                                    </div>

                                    <div class="mt-4 flex flex-wrap items-center gap-2">
                                        <x-lucide-graduation-cap class="size-4 text-gold-500/70" />
                                        <template x-for="d in r.track.degreeNames" :key="d">
                                            <span class="rounded-full bg-white/10 px-2.5 py-1 text-[10px] font-bold text-white/70" x-text="d"></span>
                                        </template>
                                    </div>

                                    <div x-show="r.majors.length" class="mt-4 rounded-xl border border-white/10 bg-white/[0.03] p-3">
                                        <div class="text-[11px] font-bold text-gold-300/80" x-text="`تخصصات متاحة مع جامعاتها (${r.majors.length})`"></div>
                                        <div class="mt-2 space-y-1.5">
                                            <template x-for="m in r.majors.slice(0, 5)" :key="m.name">
                                                <div class="flex items-center justify-between text-[12px]">
                                                    <span class="text-white/80" x-text="m.name"></span>
                                                    <span class="flex items-center gap-1 font-plex text-slate-400"><x-lucide-map-pin class="size-3" /><span x-text="`${m.unis} جامعة`"></span></span>
                                                </div>
                                            </template>
                                            <p x-show="r.majors.length > 5" class="text-[11px] text-slate-500" x-text="`+ ${r.majors.length - 5} تخصص آخر`"></p>
                                        </div>
                                    </div>

                                    <div class="mt-3 flex items-center justify-between gap-2 text-[12px] font-bold text-slate-400">
                                        <span class="flex items-center gap-2">
                                            <x-lucide-map-pin class="size-3.5 text-gold-500/60" />
                                            <span x-text="`إجمالي المؤسسات التعليمية المتاحة في هذا المسار: ${r.totalUnis}`"></span>
                                        </span>
                                        <span class="inline-flex items-center gap-1 text-gold-300">صفحة المسار <x-lucide-arrow-left class="size-3.5" /></span>
                                    </div>
                                </a>
                            </template>
                        </div>

                        <div class="flex flex-wrap gap-3 pt-2">
                            <a href="#tracks" class="group inline-flex items-center gap-2 rounded-full bg-gold-500 px-7 py-3.5 text-sm font-bold text-forest-950 transition-all hover:bg-gold-400">
                                استكشف المسارات بالتفصيل
                                <x-lucide-arrow-left class="size-4" />
                            </a>
                            <button type="button" @click="reset()" class="inline-flex items-center gap-2 rounded-full border border-white/20 px-7 py-3.5 text-sm font-bold text-white transition-colors hover:border-gold-400 hover:text-gold-300">
                                <x-lucide-rotate-ccw class="size-4" />
                                إعادة التوجيه
                            </button>
                        </div>

                        <p class="rounded-xl border border-blue-300/30 bg-blue-500/10 px-4 py-3 text-[11px] leading-relaxed text-blue-200">
                            <x-lucide-info class="me-1.5 inline size-3.5 align-[-2px]" />
                            التطابق مبني على البيانات المعتمدة في النظام (المسارات، الدرجات، التخصصات، الجامعات المربوطة) وليس على تقديرات عامة.
                            للتأكد من الأهلية الفعلية، يُرجى مراجعة صفحة المسار والدليل الاسترشادي والشروط المعتمدة.
                        </p>
                    </div>
                </template>
            </div>
        </div>
    </div>
</section>
