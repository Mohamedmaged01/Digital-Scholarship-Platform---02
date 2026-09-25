<div class="relative overflow-hidden border-y border-gold-500/25 bg-forest-950 py-5" aria-label="الجامعات الشريكة">
    <div class="pointer-events-none absolute inset-y-0 start-0 z-10 w-24 bg-gradient-to-l from-forest-950 to-transparent"></div>
    <div class="pointer-events-none absolute inset-y-0 end-0 z-10 w-24 bg-gradient-to-r from-forest-950 to-transparent"></div>
    <div class="animate-marquee flex w-max items-center gap-10">
        @foreach ([...config('kasp.marquee'), ...config('kasp.marquee')] as $i => $u)
            <span class="flex items-center gap-10" @if ($i >= count(config('kasp.marquee'))) aria-hidden="true" @endif>
                <span dir="ltr" class="whitespace-nowrap font-plex text-sm font-semibold tracking-[0.22em] text-white/60 transition-colors hover:text-gold-300">{{ $u }}</span>
                <x-star-emblem class="size-4 shrink-0 text-gold-500/70" stroke-width="1.2" />
            </span>
        @endforeach
    </div>
</div>
