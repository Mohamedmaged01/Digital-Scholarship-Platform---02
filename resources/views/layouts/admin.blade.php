@extends('layouts.app')

@section('title', trim($__env->yieldContent('page-title', 'لوحة التحكم')).' | لوحة التحكم')
@section('body-class', 'bg-cream font-sans text-ink antialiased')

@php
    $user = auth()->user();
    $sections = collect(config('kasp.admin_sections'))->filter(fn ($s) => empty($s['admin_only']) || $user->can('manage-users'));
    $current = collect($sections)->keys()->first(fn ($route) => request()->routeIs(str_replace('.index', '.*', $route))) ?? 'admin.dashboard';
    $currentSection = $sections[$current];
    $sqlDriver = config('database.default');
@endphp

@section('content')
<div class="flex min-h-screen" x-data="{ nav: false }">
    {{-- ---------------- الشريط الجانبي ---------------- --}}
    <div x-show="nav" x-cloak x-transition.opacity @click="nav = false" class="fixed inset-0 z-30 bg-forest-950/60 lg:hidden"></div>
    <aside class="pattern-star-dark fixed inset-y-0 start-0 z-40 flex w-72 shrink-0 translate-x-full flex-col bg-forest-950 transition-transform duration-400 ease-out-expo lg:sticky lg:top-0 lg:h-screen lg:translate-x-0"
           :class="nav ? 'translate-x-0!' : ''">
        <div class="flex items-center gap-3.5 border-b border-white/10 px-6 py-6">
            <span class="grid size-11 shrink-0 place-items-center rounded-2xl bg-gold-500 text-forest-950">
                <x-star-emblem class="size-6" />
            </span>
            <div class="leading-tight">
                <div class="text-sm font-bold text-white">لوحة التحكم</div>
                <div class="mt-0.5 text-[10px] font-medium text-slate-400">إدارة المنصة بالكامل</div>
            </div>
        </div>

        <div class="px-5 pt-5">
            <div class="flex items-center gap-2.5 rounded-2xl border border-emerald-400/30 bg-emerald-500/10 px-4 py-3 text-[11px] font-bold text-emerald-300">
                <span class="relative flex size-2">
                    <span class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-400 opacity-60"></span>
                    <span class="relative inline-flex size-2 rounded-full bg-emerald-400"></span>
                </span>
                <span>قاعدة البيانات متصلة — <span dir="ltr" class="font-plex">{{ ['pgsql' => 'PostgreSQL', 'mysql' => 'MySQL', 'mariadb' => 'MariaDB', 'sqlite' => 'SQLite'][$sqlDriver] ?? $sqlDriver }}</span></span>
            </div>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-4 py-5" aria-label="أقسام لوحة التحكم">
            @foreach ($sections as $route => $s)
                @php
                    $active = $route === $current;
                    $count = isset($s['count']) ? ($sectionCounts[$s['count']] ?? 0) : 0;
                @endphp
                <a href="{{ route($route) }}" @if ($active) aria-current="page" @endif
                   @class([
                       'group flex w-full items-center gap-3 rounded-2xl px-4 py-3 text-start transition-all',
                       'bg-gold-500 text-forest-950 shadow-lg shadow-gold-500/25' => $active,
                       'text-slate-300 hover:bg-white/[0.07] hover:text-white' => ! $active,
                   ])>
                    @svg('lucide-'.$s['icon'], 'size-5 shrink-0 '.($active ? 'text-forest-950' : 'text-gold-400/80'))
                    <span class="flex-1 text-sm font-bold">{{ $s['label'] }}</span>
                    @if (($s['count'] ?? null) === 'pending' && $count > 0)
                        <span dir="ltr" @class(['grid size-6 place-items-center rounded-full font-plex text-[10px] font-bold', 'bg-forest-950 text-gold-300' => $active, 'bg-red-500/20 text-red-300' => ! $active])>{{ $count }}</span>
                    @elseif ($count > 0)
                        <span dir="ltr" @class(['font-plex text-[10px] font-bold', 'text-forest-900/70' => $active, 'text-white/35' => ! $active])>{{ $count }}</span>
                    @endif
                </a>
            @endforeach
        </nav>

        <div class="space-y-2.5 border-t border-white/10 px-6 py-5">
            <div class="mb-3 flex items-center gap-3">
                <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-forest-800 font-plex text-sm font-bold text-gold-300">{{ $user->initial() }}</span>
                <div class="min-w-0 leading-tight">
                    <div class="truncate text-xs font-bold text-white">{{ $user->name }}</div>
                    <div class="mt-0.5 text-[10px] text-slate-400">{{ $user->roleLabel() }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-full border border-red-400/30 px-5 py-3 text-xs font-bold text-red-300 transition-all hover:border-red-400 hover:bg-red-500/10">
                    <x-lucide-log-out class="size-4" />
                    تسجيل الخروج
                </button>
            </form>
            <a href="{{ route('home') }}" class="flex w-full items-center justify-center gap-2 rounded-full border border-white/20 px-5 py-3 text-xs font-bold text-white transition-all hover:border-gold-400 hover:text-gold-300">
                <x-lucide-arrow-right class="size-4" />
                العودة إلى المنصة
            </a>
            <p class="mt-1 text-center font-plex text-[10px] text-white/25" dir="ltr">KASP Admin • Laravel {{ app()->version() }}</p>
        </div>
    </aside>

    {{-- ---------------- المحتوى الرئيسي ---------------- --}}
    <div class="flex min-w-0 flex-1 flex-col">
        <header class="sticky top-0 z-20 flex items-center gap-3 border-b border-forest-800/10 bg-white/80 px-5 py-3.5 backdrop-blur md:px-7">
            <button type="button" @click="nav = true" aria-label="القائمة" class="grid size-10 place-items-center rounded-xl border border-forest-800/15 text-forest-800 lg:hidden">
                <x-lucide-menu class="size-5" />
            </button>
            <h1 class="flex shrink-0 items-center gap-2.5 text-base font-bold text-ink md:text-lg">
                @svg('lucide-'.$currentSection['icon'], 'size-5 text-gold-600')
                <span class="hidden sm:inline">{{ $currentSection['label'] }}</span>
            </h1>

            {{-- البحث العام --}}
            <div class="relative mx-auto w-full max-w-md" x-data="adminSearch(@js(route('admin.search')))" @click.outside="q = ''">
                <x-lucide-search class="absolute start-4 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                <input x-model="q" type="search" @keydown.enter.prevent="go(hits[0])" @keydown.escape="q = ''" aria-label="بحث عام في لوحة التحكم"
                       placeholder="بحث عام: خبر، إجابة، جامعة، مسار، محطة، ملف…"
                       class="w-full rounded-full border border-forest-800/15 bg-sand/60 py-2.5 pe-4 ps-10 text-sm outline-none transition-all focus:border-forest-600 focus:bg-white focus:ring-4 focus:ring-forest-600/10">
                <div x-show="q.trim().length >= 2" x-cloak x-transition
                     class="absolute inset-x-0 top-full z-30 mt-2 max-h-80 overflow-y-auto rounded-2xl border border-forest-800/12 bg-white p-2 shadow-2xl">
                    <p x-show="hits.length === 0" class="px-4 py-6 text-center text-xs font-bold text-slate-400">لا توجد نتائج مطابقة في اللوحة</p>
                    <template x-for="h in hits" :key="h.url">
                        <a :href="h.url" class="group flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-start transition-colors hover:bg-forest-50">
                            <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-sand text-forest-700 transition-colors group-hover:bg-forest-800 group-hover:text-gold-300" x-html="h.icon"></span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-[13px] font-bold text-ink" x-text="h.title"></span>
                                <span class="block text-[10px] text-slate-400" x-text="h.section + (h.sub ? ` • ${h.sub}` : '')"></span>
                            </span>
                        </a>
                    </template>
                </div>
            </div>

            <a href="{{ route('home') }}" aria-label="إغلاق لوحة التحكم" title="العودة إلى المنصة"
               class="ms-auto grid size-10 shrink-0 place-items-center rounded-xl border border-forest-800/15 text-forest-800 transition-all hover:border-gold-500/60 hover:text-gold-600">
                <x-lucide-x class="size-5" />
            </a>
        </header>

        {{-- تبويبات للجوال --}}
        <div class="no-scrollbar flex gap-2 overflow-x-auto border-b border-forest-800/10 bg-white/60 px-4 py-2.5 lg:hidden">
            @foreach ($sections as $route => $s)
                <a href="{{ route($route) }}" @class([
                    'inline-flex shrink-0 items-center gap-1.5 rounded-full px-4 py-2 text-xs font-bold transition-all',
                    'bg-forest-800 text-gold-300' => $route === $current,
                    'border border-forest-800/15 bg-white text-slate-500' => $route !== $current,
                ])>
                    @svg('lucide-'.$s['icon'], 'size-3.5')
                    {{ $s['label'] }}
                    @if (($s['count'] ?? null) === 'pending' && ($sectionCounts['pending'] ?? 0) > 0)
                        <span class="rounded-full bg-red-500/15 px-1.5 font-plex text-[9px] font-bold text-red-500" dir="ltr">{{ $sectionCounts['pending'] }}</span>
                    @endif
                </a>
            @endforeach
        </div>

        <main class="flex-1 p-5 md:p-8">
            @if (session('status'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3500)" x-transition.opacity role="status"
                     class="mb-6 flex items-center gap-2.5 rounded-2xl border border-emerald-300 bg-emerald-50 px-5 py-3.5 text-sm font-bold text-emerald-700">
                    <x-lucide-circle-check class="size-5 shrink-0" />
                    {{ session('status') }}
                </div>
            @endif

            @cannot('edit-content')
                @unless (request()->routeIs('admin.dashboard'))
                    <div class="mb-6 flex items-center gap-3 rounded-2xl border border-amber-300 bg-amber-50 px-6 py-4 text-sm font-bold text-amber-700">
                        <x-lucide-shield-check class="size-5 shrink-0" />
                        صلاحيتك الحالية «{{ $user->roleLabel() }}» تتيح الاستعراض فقط — تواصل مع مدير النظام لترقية صلاحياتك
                    </div>
                @endunless
            @endcannot

            @yield('admin')
        </main>
    </div>
</div>
@endsection
