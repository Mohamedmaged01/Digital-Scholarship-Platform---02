<section id="faq" class="relative overflow-hidden bg-cream py-24 md:py-32" x-data="{ open: 0 }">
    <div class="relative mx-auto max-w-7xl px-5 md:px-8">
        <div class="grid grid-cols-1 gap-12 lg:grid-cols-12">
            <div class="lg:col-span-5">
                <div class="lg:sticky lg:top-32">
                    <x-section-heading eyebrow="الأسئلة الشائعة"
                                       desc="جمعنا أكثر الأسئلة تكرارًا من آلاف المتقدمين. لم تجد سؤالك؟ فريق الدعم متاح على مدار الساعة عبر قنوات المنصة.">
                        كل ما يدور في ذهنك،
                        <span class="text-shimmer-gold"> بإجابة واحدة</span>
                    </x-section-heading>
                    <div class="reveal mt-8 inline-flex items-center gap-3 rounded-2xl border border-forest-800/10 bg-white px-5 py-4" style="--delay: .25s">
                        <span class="grid size-11 place-items-center rounded-xl bg-forest-700 text-gold-300">
                            <x-lucide-message-circle-question class="size-5" />
                        </span>
                        <span class="leading-tight">
                            <span class="block text-sm font-bold text-ink">الدعم الموحّد 920001122</span>
                            <span class="block text-xs text-slate-500">متاح 24/7 بثلاث لغات</span>
                        </span>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-7">
                <div class="space-y-4">
                    @foreach (config('kasp.faqs') as $i => $f)
                        <div class="reveal" style="--delay: {{ $i * 0.06 }}s">
                            <button type="button" @click="open = open === {{ $i }} ? -1 : {{ $i }}" :aria-expanded="open === {{ $i }}"
                                    class="w-full rounded-3xl border p-6 text-start transition-all duration-500 md:p-7"
                                    :class="open === {{ $i }} ? 'border-forest-700 bg-forest-900 shadow-[0_30px_60px_-28px_rgba(8,39,29,0.6)]' : 'border-forest-800/10 bg-white hover:border-gold-500/50 hover:shadow-[0_20px_45px_-25px_rgba(8,39,29,0.25)]'">
                                <div class="flex items-center justify-between gap-4">
                                    <div class="flex items-center gap-4">
                                        <span dir="ltr" class="font-plex text-sm font-bold transition-colors" :class="open === {{ $i }} ? 'text-gold-400' : 'text-forest-700/40'">0{{ $i + 1 }}</span>
                                        <h3 class="text-base font-bold leading-relaxed md:text-lg" :class="open === {{ $i }} ? 'text-white' : 'text-ink'">{{ $f['q'] }}</h3>
                                    </div>
                                    <span class="grid size-9 shrink-0 place-items-center rounded-full border transition-all duration-500"
                                          :class="open === {{ $i }} ? 'rotate-45 border-gold-400 bg-gold-400 text-forest-950' : 'border-forest-800/15 text-forest-700'">
                                        <x-lucide-plus class="size-4.5" />
                                    </span>
                                </div>
                                <div x-show="open === {{ $i }}" x-collapse @if ($i !== 0) x-cloak @endif>
                                    <p class="pe-13 ps-13 pt-4 text-[15px] leading-loose text-slate-300">{{ $f['a'] }}</p>
                                </div>
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
