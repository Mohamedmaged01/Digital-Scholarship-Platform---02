@php
    $features = [
        ['icon' => 'shield-check', 'title' => 'إجابات موثّقة', 'desc' => 'يرد المساعد من قاعدة معرفة معتمدة وفق نظام الابتعاث ولوائحه التنفيذية.'],
        ['icon' => 'database', 'title' => 'تُدار من لوحة التحكم', 'desc' => 'تزوّد الإدارة المساعد بالأسئلة والإجابات باستمرار ليبقى دقيقًا ومحدّثًا.'],
        ['icon' => 'life-buoy', 'title' => 'تحويل تلقائي للإدارة', 'desc' => 'حين لا توجد إجابة واضحة، يسجَّل سؤالك وتظهر لك قنوات التواصل مباشرة.'],
    ];
@endphp

<section id="assistant" class="relative overflow-hidden bg-sand py-24 md:py-32">
    <div class="absolute -start-32 top-1/3 h-[26rem] w-[26rem] rounded-full bg-forest-100/70 blur-[130px]" aria-hidden="true"></div>
    <div class="relative mx-auto max-w-7xl px-5 md:px-8">
        <div class="grid grid-cols-1 items-center gap-14 lg:grid-cols-12">
            <div class="lg:col-span-5">
                <x-section-heading eyebrow="خدمات الذكاء الاصطناعي">
                    محرّك التوجيه الذكي —
                    <span class="text-shimmer-gold"> محاورك الأول</span>
                </x-section-heading>
                <p dir="ltr" class="reveal mt-4 text-right font-plex text-[11px] font-semibold uppercase tracking-[0.3em] text-gold-600" style="--delay: .12s">AI Recommendation Engine</p>
                <p class="reveal mt-4 text-lg leading-relaxed text-slate-600" style="--delay: .18s">
                    لا تعرف أي مسار يناسبك؟ دعنا نساعدك في اكتشاف المسار الأقرب إلى
                    طموحك الأكاديمي والمهني ومؤهلاتك الحالية في خطوات بسيطة — واسأل
                    المساعد عن أي تفصيلة في نظام الابتعاث وسيجيبك فورًا.
                </p>

                <div class="mt-8 space-y-4">
                    @foreach ($features as $i => $f)
                        <div class="reveal flex items-start gap-4 rounded-2xl border border-forest-800/10 bg-white/70 p-4 transition-all duration-300 hover:border-gold-500/40 hover:bg-white" style="--delay: {{ 0.2 + $i * 0.08 }}s">
                            <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-forest-800 text-gold-400">
                                @svg('lucide-'.$f['icon'], 'size-5')
                            </span>
                            <div class="leading-relaxed">
                                <h3 class="text-sm font-bold text-ink">{{ $f['title'] }}</h3>
                                <p class="mt-1 text-[13px] text-slate-500">{{ $f['desc'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="reveal mt-9 flex flex-wrap gap-3" style="--delay: .45s">
                    <a href="#matcher" class="group relative inline-flex items-center justify-center gap-2.5 overflow-hidden rounded-full bg-gold-500 px-8 py-4 text-sm font-bold text-forest-950 transition-all duration-300 hover:-translate-y-0.5 hover:bg-gold-400 hover:shadow-[0_12px_40px_-8px_rgba(201,163,56,0.5)]">
                        <x-lucide-brain-circuit class="size-4.5" />
                        اكتشف مسارك بالذكاء الاصطناعي
                    </a>
                    <a href="{{ route('admin.kb.index') }}" class="inline-flex items-center gap-2 rounded-full border border-forest-800/25 px-7 py-4 text-sm font-bold text-forest-800 transition-all duration-300 hover:-translate-y-0.5 hover:border-forest-700 hover:bg-forest-800 hover:text-gold-300">
                        <x-lucide-settings-2 class="size-4.5" />
                        لوحة تحكم المعرفة
                    </a>
                </div>

                <p class="reveal mt-5 flex items-center gap-2 text-xs font-medium text-slate-400" style="--delay: .5s">
                    <x-lucide-sparkles class="size-3.5 text-gold-600" />
                    جرّب أن تسأل: «ما شروط مسار التميز؟» أو «هل تشمل البعثة مكافأة شهرية؟»
                </p>
            </div>

            <div class="lg:col-span-7">
                <div class="reveal" style="--delay: .2s">
                    <x-chat-panel class="mx-auto h-[620px] max-w-2xl" />
                </div>
            </div>
        </div>
    </div>
</section>
