<ol class="space-y-2.5">
    @foreach ($items as $n => $item)
        <li class="flex items-start gap-3 text-[14px] leading-loose text-slate-700">
            <span class="mt-1 grid size-6 shrink-0 place-items-center rounded-lg bg-forest-800 font-plex text-[11px] font-bold text-gold-300" dir="ltr">{{ $n + 1 }}</span>
            <div class="min-w-0 flex-1">
                {{ is_array($item) ? $item['text'] : $item }}
                @if (is_array($item) && ! empty($item['sub']))
                    <ul class="mt-2 space-y-1.5 border-s-2 border-gold-500/40 ps-4">
                        @foreach ($item['sub'] as $sub)
                            <li class="text-[13px] leading-relaxed text-slate-600">{{ $sub }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </li>
    @endforeach
</ol>
