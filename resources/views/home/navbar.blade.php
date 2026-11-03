@php
    $actions = [
        ['icon' => 'search', 'label' => __('البحث في المنصة'), 'short' => __('بحث المنصة'), 'event' => 'open-search', 'hotkey' => 'Ctrl+K'],
        ['icon' => 'bot', 'label' => __('مساعد البحث الذكي'), 'short' => __('المساعد الذكي'), 'event' => 'open-chat'],
    ];
@endphp

<div x-data="navbar">
    <header class="animate-enter fixed inset-x-0 top-0 z-50 transition-all duration-500"
            :class="scrolled ? 'bg-cream/85 shadow-[0_8px_40px_-16px_rgba(10,27,20,0.25)] backdrop-blur-xl' : 'bg-transparent'">
        <div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-5 md:px-8">
            {{-- Brand --}}
            <a href="#home" class="flex items-center gap-3" aria-label="{{ __('الرئيسية — برنامج خادم الحرمين الشريفين للابتعاث') }}">
                <x-brand-logo class="h-12 md:h-14" fetchpriority="high" />
                <span class="hidden h-8 w-px bg-forest-800/15 2xl:block"></span>
                <span class="hidden text-[11px] font-semibold leading-tight text-slate-500 2xl:block">{{ __('البوابة التعريفية') }}<br>{{ __('والإرشادية') }}</span>
            </a>

            {{-- Desktop links --}}
            <nav class="hidden items-center gap-5 ltr:gap-4 xl:flex 2xl:gap-6">
                @foreach (\App\Support\SiteNav::links() as $l)
                    <a href="{{ $l['home'] }}"
                       class="relative whitespace-nowrap text-[13px] font-semibold text-slate-600 transition-colors after:absolute after:-bottom-1.5 after:start-0 after:h-0.5 after:w-0 after:rounded-full after:bg-gold-500 after:transition-all after:duration-300 after:content-[''] hover:text-forest-700 hover:after:w-full">
                        {{ $l['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="hidden items-center gap-2 xl:flex">
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
                <x-lang-switch />
                <a href="{{ route('admin.dashboard') }}" aria-label="{{ __('الدخول إلى لوحة التحكم') }}" title="{{ __('الدخول إلى لوحة التحكم') }}"
                   class="group/ib relative grid size-11 place-items-center rounded-xl border border-forest-800/15 bg-white/70 text-forest-800 backdrop-blur transition-all duration-300 hover:-translate-y-0.5 hover:border-gold-500/60 hover:bg-white hover:text-gold-600 hover:shadow-md">
                    <x-lucide-layout-dashboard class="size-4.5" />
                    <span class="pointer-events-none absolute -bottom-9 start-1/2 -translate-x-1/2 whitespace-nowrap rounded-lg bg-forest-950 px-2.5 py-1 text-[10px] font-bold text-gold-300 opacity-0 shadow-lg transition-opacity duration-300 group-hover/ib:opacity-100">{{ __('الدخول إلى لوحة التحكم') }}</span>
                </a>
                <a href="{{ \App\Models\SiteSetting::get('apply_url') }}" target="_blank" rel="noopener noreferrer"
                   class="ms-1 inline-flex items-center gap-2 rounded-full bg-forest-800 px-6 py-3 text-sm font-bold text-white transition-all duration-300 hover:-translate-y-0.5 hover:bg-forest-700 hover:shadow-[0_12px_32px_-10px_rgba(15,70,50,0.6)]">
                    {{ __('ابدأ التقديم') }}
                    <x-lucide-arrow-up-left class="rtl-flip size-4" />
                </a>
            </div>

            {{-- Mobile toggle --}}
            <div class="flex items-center gap-2 xl:hidden">
            <x-lang-switch />
            <button type="button" @click="menu = true" aria-label="{{ __('فتح القائمة') }}"
                    class="grid size-11 place-items-center rounded-xl border border-forest-800/15 bg-white/60 text-forest-800 backdrop-blur xl:hidden">
                <x-lucide-menu class="size-5" />
            </button>
            </div>
        </div>

        {{-- Scroll progress --}}
        <div class="absolute inset-x-0 bottom-0 h-[2.5px] origin-right ltr:origin-left bg-gradient-to-l ltr:bg-gradient-to-r from-gold-500 via-gold-400 to-gold-500"
             :style="`transform: scaleX(${progress})`" style="transform: scaleX(0)"></div>
    </header>

    {{-- Mobile menu --}}
    <div x-show="menu" x-cloak x-transition.opacity.duration.350ms @keydown.escape.window="menu = false"
         class="pattern-star-dark fixed inset-0 z-[60] flex flex-col bg-forest-950 xl:hidden" role="dialog" aria-modal="true" aria-label="{{ __('القائمة') }}">
        <div class="flex h-20 items-center justify-between px-5">
            <x-brand-logo light class="h-10" />
            <button type="button" @click="menu = false" aria-label="{{ __('إغلاق القائمة') }}" class="grid size-11 place-items-center rounded-xl border border-white/15 text-white">
                <x-lucide-x class="size-5" />
            </button>
        </div>
        <nav class="flex flex-1 flex-col justify-center gap-1 px-8">
            @foreach (\App\Support\SiteNav::links() as $i => $l)
                <a href="{{ $l['home'] }}" @click="menu = false" class="group flex items-center justify-between border-b border-white/10 py-5">
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
                    <span class="text-[11px] font-bold">{{ __('لوحة التحكم') }}</span>
                </a>
            </div>
        </div>
    </div>
</div>
