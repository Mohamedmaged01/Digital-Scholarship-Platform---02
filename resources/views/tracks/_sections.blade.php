{{-- قائمة أقسام الشروط: [{title, items: [نص | {text, sub[]}]}] — الأقسام الكثيرة تُعرض مطويّة --}}
@php $collapsible = count($sections) > 3; @endphp
<div @class(['space-y-3' => $collapsible, 'space-y-6' => ! $collapsible]) @if ($collapsible) x-data="{ open: 0 }" @endif>
    @foreach ($sections as $si => $section)
        @if ($collapsible)
            <div class="overflow-hidden rounded-2xl border border-forest-800/10 bg-white">
                <button type="button" @click="open = open === {{ $si }} ? -1 : {{ $si }}" :aria-expanded="open === {{ $si }}"
                        class="flex w-full items-center justify-between gap-3 px-5 py-4 text-start transition-colors hover:bg-sand/40">
                    <span class="text-sm font-bold text-ink">{{ $section['title'] ?: __('الشروط') }}</span>
                    <span class="flex shrink-0 items-center gap-2">
                        <span class="rounded-full bg-forest-50 px-2 py-0.5 font-plex text-[10px] font-bold text-forest-700" dir="ltr">{{ count($section['items']) }}</span>
                        <x-lucide-chevron-down class="size-4 text-slate-400 transition-transform duration-300" ::class="open === {{ $si }} && 'rotate-180'" />
                    </span>
                </button>
                <div x-show="open === {{ $si }}" x-collapse @if ($si !== 0) x-cloak @endif>
                    <div class="border-t border-forest-800/8 px-5 pb-5 pt-4">
                        @include('tracks._items', ['items' => $section['items']])
                    </div>
                </div>
            </div>
        @else
            <div>
                @if ($section['title'] !== '')
                    <h4 class="mb-3 text-sm font-bold text-forest-800">{{ $section['title'] }}</h4>
                @endif
                @include('tracks._items', ['items' => $section['items']])
            </div>
        @endif
    @endforeach
</div>
