<section class="pattern-star-dark grain relative overflow-hidden bg-forest-900 py-24 md:py-28">
    <div class="absolute -top-32 start-1/3 h-96 w-96 rounded-full bg-forest-600/25 blur-[120px]" aria-hidden="true"></div>
    <div class="relative mx-auto max-w-7xl px-5 md:px-8">
        <div class="flex flex-wrap items-end justify-between gap-6">
            <h2 class="reveal max-w-md text-3xl font-bold leading-snug text-white md:text-4xl">
                أرقامٌ تصنع جيلًا
                <span class="text-shimmer-gold"> ينافس العالم</span>
            </h2>
            <p class="reveal max-w-sm text-sm leading-relaxed text-slate-400" style="--delay: .1s">
                منذ انطلاق البرنامج عام 1426هـ، تحوّل الابتعاث إلى أكبر استثمار
                وطني في رأس المال البشري على مستوى المنطقة.
            </p>
        </div>
        <div class="mt-12 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach (config('kasp.stats') as $i => $s)
                <div class="reveal relative" style="--delay: {{ $i * 0.08 }}s" x-data="countUp({{ $s['value'] }})" x-intersect.once.margin.-120px="start()">
                    <div class="group relative overflow-hidden rounded-3xl border border-white/10 bg-white/[0.04] p-7 backdrop-blur-sm transition-all duration-500 hover:border-gold-500/40 hover:bg-white/[0.07]">
                        <div class="absolute -end-6 -top-6 size-24 rounded-full bg-gold-500/10 blur-2xl transition-all duration-500 group-hover:bg-gold-500/20"></div>
                        <div class="flex items-baseline gap-1 font-plex" dir="ltr">
                            <span class="text-5xl font-bold tracking-tight text-white md:text-6xl" x-text="display">{{ number_format($s['value']) }}</span>
                            @if ($s['suffix'])
                                <span class="text-2xl font-bold text-gold-400">{{ $s['suffix'] }}</span>
                            @endif
                        </div>
                        <div class="mt-3 text-base font-bold text-gold-200">{{ $s['label'] }}</div>
                        <div class="mt-1 text-sm text-slate-400">{{ $s['note'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
