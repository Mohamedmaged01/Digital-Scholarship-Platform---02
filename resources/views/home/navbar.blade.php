@php
    $actions = [
        ['icon' => 'search', 'label' => 'البحث في المنصة', 'short' => 'بحث المنصة', 'event' => 'open-search', 'hotkey' => 'Ctrl+K'],
        ['icon' => 'bot', 'label' => 'مساعد البحث الذكي', 'short' => 'المساعد الذكي', 'event' => 'open-chat'],
    ];
@endphp

<div x-data="navbar">
    <header class="animate-enter fixed inset-x-0 top-0 z-50 transition-all duration-500"
            :class="scrolled ? 'bg-cream/85 shadow-[0_8px_40px_-16px_rgba(10,27,20,0.25)] backdrop-blur-xl' : 'bg-transparent'">
        <div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-5 md:px-8">
            {{-- Brand --}}
            <a href="#home" class="group flex items-center gap-3.5">
                <span class="grid size-11 place-items-center rounded-2xl bg-forest-800 text-gold-400 shadow-lg shadow-forest-800/20 transition-transform duration-500 group-hover:rotate-45">
                    <x-star-emblem class="size-6" />
                </span>
                <span class="leading-tight">
                    <span class="block text-[15px] font-bold text-ink">برنامج خادم الحرمين الشريفين</span>
                    <span class="block text-[11px] font-medium text-slate-500">المنصّة الموحّدة للابتعاث الخارجي</span>
                </span>
            </a>

            {{-- Desktop links --}}
            <nav class="hidden items-center gap-6 lg:flex">
                @foreach (config('kasp.nav') as $l)
                    <a href="{{ $l['href'] }}"
                       class="relative text-[13px] font-semibold text-slate-600 transition-colors after:absolute after:-bottom-1.5 after:start-0 after:h-0.5 after:w-0 after:rounded-full after:bg-gold-500 after:transition-all after:duration-300 after:content-[''] hover:text-forest-700 hover:after:w-full">
                        {{ $l['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="hidden items-center gap-2 lg:flex">
                @foreach ($actions as $b)
                    <button type="button" @click="window.dispatchEvent(new Event('{{ $b['event'] }}'))"
                            aria-label="{{ $b['label'] }}" title="{{ $b['label'] }}{{ isset($b['hotkey']) ? " ({$b['hotkey']})" : '' }}"
                            class="group/ib relative grid size-11 place-items-center rounded-xl border border-forest-800/15 bg-white/70 text-forest-800 backdrop-blur transition-all duration-300 hover:-translate-y-0.5 hover:border-gold-500/60 hover:bg-white hover:text-gold-600 hover:shadow-md">
                        @svg('lucide-'.$b['icon'], 'size-4.5')
                        <span class="pointer-events-none absolute -bottom-9 start-1/2 -translate-x-1/2 whitespace-nowrap rounded-lg bg-forest-950 px-2.5 py-1 text-[10px] font-bold text-gold-300 opacity-0 shadow-lg transition-opacity duration-300 group-hover/ib:opacity-100">
                            {{ $b['label'] }}
                            @isset($b['hotkey'])<span class="ms-1 font-plex text-white/50" dir="ltr">({{ $b['hotkey'] }})</span>@endisset
                        </span>
                    </button>
                @endforeach
                <a href="{{ route('admin.dashboard') }}" aria-label="الدخول إلى لوحة التحكم" title="الدخول إلى لوحة التحكم"
                   class="group/ib relative grid size-11 place-items-center rounded-xl border border-forest-800/15 bg-white/70 text-forest-800 backdrop-blur transition-all duration-300 hover:-translate-y-0.5 hover:border-gold-500/60 hover:bg-white hover:text-gold-600 hover:shadow-md">
                    <x-lucide-layout-dashboard class="size-4.5" />
                    <span class="pointer-events-none absolute -bottom-9 start-1/2 -translate-x-1/2 whitespace-nowrap rounded-lg bg-forest-950 px-2.5 py-1 text-[10px] font-bold text-gold-300 opacity-0 shadow-lg transition-opacity duration-300 group-hover/ib:opacity-100">الدخول إلى لوحة التحكم</span>
                </a>
                <a href="#matcher"
                   class="ms-1 inline-flex items-center gap-2 rounded-full bg-forest-800 px-6 py-3 text-sm font-bold text-white transition-all duration-300 hover:-translate-y-0.5 hover:bg-forest-700 hover:shadow-[0_12px_32px_-10px_rgba(15,70,50,0.6)]">
                    ابدأ التقديم
                    <x-lucide-arrow-up-left class="size-4" />
                </a>
            </div>

            {{-- Mobile toggle --}}
            <button type="button" @click="menu = true" aria-label="فتح القائمة"
                    class="grid size-11 place-items-center rounded-xl border border-forest-800/15 bg-white/60 text-forest-800 backdrop-blur lg:hidden">
                <x-lucide-menu class="size-5" />
            </button>
        </div>

        {{-- Scroll progress --}}
        <div class="absolute inset-x-0 bottom-0 h-[2.5px] origin-right bg-gradient-to-l from-gold-500 via-gold-400 to-gold-500"
             :style="`transform: scaleX(${progress})`" style="transform: scaleX(0)"></div>
    </header>

    {{-- Mobile menu --}}
    <div x-show="menu" x-cloak x-transition.opacity.duration.350ms @keydown.escape.window="menu = false"
         class="pattern-star-dark fixed inset-0 z-[60] flex flex-col bg-forest-950 lg:hidden" role="dialog" aria-modal="true" aria-label="القائمة">
        <div class="flex h-20 items-center justify-between px-5">
            <span class="flex items-center gap-3">
                <x-star-emblem class="size-7 text-gold-400" />
                <span class="text-sm font-bold text-white">برنامج خادم الحرمين الشريفين</span>
            </span>
            <button type="button" @click="menu = false" aria-label="إغلاق القائمة" class="grid size-11 place-items-center rounded-xl border border-white/15 text-white">
                <x-lucide-x class="size-5" />
            </button>
        </div>
        <nav class="flex flex-1 flex-col justify-center gap-1 px-8">
            @foreach (config('kasp.nav') as $i => $l)
                <a href="{{ $l['href'] }}" @click="menu = false" class="group flex items-center justify-between border-b border-white/10 py-5">
                    <span class="text-3xl font-bold text-white transition-colors group-hover:text-gold-300">{{ $l['label'] }}</span>
                    <span class="font-plex text-xs text-white/40">0{{ $i + 1 }}</span>
                </a>
            @endforeach
        </nav>
        <div class="p-8">
            <div class="mb-4 grid grid-cols-3 gap-2.5">
                @foreach ($actions as $b)
                    <button type="button" @click="menu = false; window.dispatchEvent(new Event('{{ $b['event'] }}'))"
                            class="flex flex-col items-center gap-2 rounded-2xl border border-white/15 bg-white/[0.06] px-2 py-3.5 text-white transition-all hover:border-gold-400/60 hover:text-gold-300">
                        @svg('lucide-'.$b['icon'], 'size-5 text-gold-400')
                        <span class="text-[11px] font-bold">{{ $b['short'] }}</span>
                    </button>
                @endforeach
                <a href="{{ route('admin.dashboard') }}"
                   class="flex flex-col items-center gap-2 rounded-2xl border border-white/15 bg-white/[0.06] px-2 py-3.5 text-white transition-all hover:border-gold-400/60 hover:text-gold-300">
                    <x-lucide-layout-dashboard class="size-5 text-gold-400" />
                    <span class="text-[11px] font-bold">لوحة التحكم</span>
                </a>
            </div>
        </div>
    </div>
</div>
