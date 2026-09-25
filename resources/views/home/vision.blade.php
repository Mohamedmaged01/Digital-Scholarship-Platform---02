@php
    $pillars = [
        ['icon' => 'heart-pulse', 'label' => 'مجتمع حيوي', 'desc' => 'إنسانٌ معافى وممكّن'],
        ['icon' => 'eye', 'label' => 'اقتصاد مزدهر', 'desc' => 'معرفةٌ تُنتج قيمة'],
        ['icon' => 'landmark', 'label' => 'وطن طموح', 'desc' => 'كفاءاتٌ تقود العالم'],
    ];
@endphp

<section class="relative flex min-h-[92vh] items-center overflow-hidden" x-data="parallax">
    <div class="absolute inset-0 scale-[1.25] will-change-transform" :style="`transform: translateY(${y}%) scale(1.25)`">
        <img src="{{ asset('images/vision-riyadh.jpg') }}" alt="أفق الرياض المستقبلي" loading="lazy" class="h-full w-full object-cover">
    </div>
    <div class="absolute inset-0 bg-gradient-to-b from-forest-950/85 via-forest-950/55 to-forest-950/90"></div>
    <div class="pattern-star-dark absolute inset-0 opacity-60"></div>

    <div class="relative mx-auto w-full max-w-7xl px-5 py-28 md:px-8">
        <div class="max-w-3xl">
            <div class="reveal inline-flex items-center gap-3 rounded-full border border-gold-500/40 bg-forest-950/50 px-5 py-2.5 backdrop-blur">
                <x-star-emblem class="size-5 text-gold-400" />
                <span dir="ltr" class="font-plex text-sm font-bold tracking-[0.25em] text-gold-300">SAUDI VISION 2030</span>
            </div>

            <h2 class="reveal mt-8 text-4xl font-bold leading-[1.25] text-white md:text-6xl md:leading-[1.2]" style="--delay: .1s">
                «ثروتُنا الأولى
                <br>
                ليست النفط…»
                <span class="mt-2 block text-2xl font-semibold text-gold-300 md:text-3xl">بل عقولُ أبنائها وبناتها</span>
            </h2>

            <p class="reveal mt-7 max-w-xl text-lg leading-relaxed text-slate-200" style="--delay: .2s">
                يُعد الابتعاث الخارجي ذراعًا استراتيجية لرؤية المملكة 2030: نرسل
                أفضل العقول إلى أفضل الجامعات، لتعود كفاءاتٍ تقود التحول الوطني
                في الصحة والطاقة والتقنية والثقافة.
            </p>

            <div class="reveal mt-10 grid grid-cols-1 gap-3 sm:grid-cols-3" style="--delay: .3s">
                @foreach ($pillars as $p)
                    <div class="group flex items-center gap-4 rounded-2xl border border-white/12 bg-white/[0.06] p-4 backdrop-blur transition-all duration-500 hover:border-gold-400/60 hover:bg-white/[0.1]">
                        <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-gold-500/15 text-gold-300 transition-transform duration-500 group-hover:scale-110">
                            @svg('lucide-'.$p['icon'], 'size-5.5')
                        </span>
                        <span class="leading-tight">
                            <span class="block text-[15px] font-bold text-white">{{ $p['label'] }}</span>
                            <span class="mt-1 block text-xs text-slate-300">{{ $p['desc'] }}</span>
                        </span>
                    </div>
                @endforeach
            </div>

            <a href="#matcher" class="reveal group mt-10 inline-flex items-center gap-3 text-base font-bold text-gold-300 transition-colors hover:text-gold-200" style="--delay: .4s">
                <span class="border-b-2 border-gold-500/60 pb-1 transition-colors group-hover:border-gold-300">كن جزءًا من قصة التحول الوطني</span>
                <x-lucide-arrow-up-left class="size-5 transition-transform duration-300 group-hover:-translate-x-1.5 group-hover:-translate-y-1.5" />
            </a>
        </div>
    </div>
</section>
