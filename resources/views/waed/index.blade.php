@extends('layouts.page')

@section('title', 'مسار واعد — برامج القطاعات الوطنية')

@php
    $filterData = $programs->mapWithKeys(fn ($p) => [$p->id => [
        'sector' => $p->sector,
        'text' => "{$p->name_ar} {$p->company_ar} {$p->major_name}",
    ]]);
    $sectors = ['الكل', ...collect(config('kasp.waed_sectors'))->merge($programs->pluck('sector'))->unique()->values()];
@endphp

@section('page')
<div class="mx-auto max-w-7xl px-5 py-10 md:px-8" x-data="waedPrograms(@js($filterData))">
    <nav class="mb-6 flex flex-wrap items-center gap-1.5 text-[11px] font-bold text-slate-400" aria-label="مسار التنقل">
        <a href="{{ route('home') }}" class="hover:text-forest-700">الرئيسية</a>
        <x-lucide-chevron-left class="size-3" />
        <a href="{{ route('home') }}#tracks" class="hover:text-forest-700">المسارات</a>
        <x-lucide-chevron-left class="size-3" />
        <span class="text-forest-700" aria-current="page">{{ $track->name }}</span>
    </nav>

    <div class="max-w-3xl">
        <span class="inline-flex items-center gap-2 rounded-full border border-forest-700/15 bg-white px-4 py-1.5 text-xs font-bold text-forest-700">
            <x-lucide-satellite class="size-3.5 text-gold-600" /> {{ $track->name }}
        </span>
        <h1 class="mt-4 text-3xl font-bold text-ink md:text-5xl">
            برامج برعاية <span class="text-shimmer-gold">القطاعات الوطنية</span>
        </h1>
        <p class="mt-4 text-base leading-relaxed text-slate-600">
            كل برنامج في مسار واعد مستقل بجهته الراعية ومؤسسته التعليمية وشروطه ومتطلباته وفترة تقديمه.
            اضغط على أي برنامج لاستعراض شروطه الكاملة.
        </p>
        <x-last-updated :date="$programs->max('updated_at')" source="برامج واعد" :version="$track->version" class="mt-5 w-fit" />
    </div>

    {{-- أرقام البرامج --}}
    <div class="mt-8 flex flex-wrap gap-3">
        @foreach ([
            [$programs->where('application_status', 'open')->count(), 'برنامج مفتوح', 'text-emerald-600'],
            [$programs->where('application_status', 'upcoming')->count(), 'برنامج قادم', 'text-amber-600'],
            [$past->count(), 'برنامج سابق', 'text-slate-500'],
            [$programs->pluck('sector')->unique()->count(), 'قطاع مستهدف', 'text-forest-700'],
        ] as [$value, $label, $color])
            <div class="flex items-center gap-2.5 rounded-2xl border border-forest-800/10 bg-white px-4 py-2.5 shadow-sm">
                <span class="font-plex text-xl font-bold {{ $color }}" dir="ltr">{{ $value }}</span>
                <span class="text-xs font-bold text-slate-500">{{ $label }}</span>
            </div>
        @endforeach
    </div>

    {{-- فلاتر --}}
    <div class="mt-8 flex flex-col gap-4 md:flex-row md:items-center">
        <div class="no-scrollbar -mx-5 flex gap-2 overflow-x-auto px-5 md:mx-0 md:px-0" role="group" aria-label="القطاع">
            @foreach ($sectors as $s)
                <button type="button" @click="sector = @js($s)" :aria-pressed="sector === @js($s)"
                        class="shrink-0 rounded-full border px-4 py-2.5 text-xs font-bold transition-all"
                        :class="sector === @js($s) ? 'border-forest-800 bg-forest-800 text-gold-300 shadow-md' : 'border-forest-800/15 bg-white text-slate-600 hover:border-forest-700/50'">
                    {{ $s }}
                </button>
            @endforeach
        </div>
        <div class="relative md:ms-auto md:w-64">
            <x-lucide-search class="absolute start-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
            <input x-model="search" type="search" placeholder="ابحث في البرامج…" aria-label="ابحث في البرامج"
                   class="w-full rounded-full border border-forest-800/15 bg-white py-2.5 pe-4 ps-10 text-sm outline-none focus:border-forest-600 focus:ring-4 focus:ring-forest-600/10">
        </div>
    </div>

    {{-- البرامج الحالية والقادمة --}}
    <section class="mt-10" aria-labelledby="current-title">
        <h2 id="current-title" class="flex items-center gap-2.5 text-xl font-bold text-ink">
            <span class="relative flex size-3">
                <span class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-500 opacity-60"></span>
                <span class="relative inline-flex size-3 rounded-full bg-emerald-500"></span>
            </span>
            البرامج الحالية والقادمة
            <span class="rounded-md bg-forest-800 px-2 py-0.5 font-plex text-xs font-bold text-gold-300" dir="ltr" x-text="count(@js($current->pluck('id')))">{{ $current->count() }}</span>
        </h2>
        <div class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($current as $program)
                @include('waed._card', ['program' => $program])
            @endforeach
        </div>
        <div x-show="count(@js($current->pluck('id'))) === 0" @if ($current->isNotEmpty()) x-cloak @endif
             class="mt-6 rounded-3xl border-2 border-dashed border-forest-800/10 bg-white/60 py-14 text-center">
            <x-lucide-book-open class="mx-auto mb-3 size-9 text-slate-300" />
            <p class="text-sm font-bold text-slate-500">لا توجد برامج مطابقة حاليًا</p>
        </div>
    </section>

    {{-- البرامج السابقة --}}
    @if ($past->isNotEmpty())
        <section class="mt-14">
            <button type="button" @click="showPast = !showPast" :aria-expanded="showPast"
                    class="flex w-full items-center justify-between rounded-2xl border border-slate-200 bg-white px-6 py-4 text-sm font-bold text-slate-600 transition-all hover:border-slate-300">
                <span class="flex items-center gap-2.5">
                    <span class="size-2 rounded-full bg-slate-400"></span>
                    البرامج السابقة — انتهت فترة التقديم (<span x-text="count(@js($past->pluck('id')))">{{ $past->count() }}</span>)
                </span>
                <x-lucide-chevron-down class="size-5 transition-transform duration-300" ::class="showPast && 'rotate-180'" />
            </button>
            <div x-show="showPast" x-collapse>
                <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50/60 p-4">
                    <p class="mb-4 flex items-center gap-2 text-xs font-bold text-slate-500">
                        <x-lucide-info class="size-4" />
                        البرامج التالية انتهت فترة تقديمها — تُحفظ كمرجع وللاطلاع على الشروط عند إطلاق دورات جديدة
                    </p>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                        @foreach ($past as $program)
                            @include('waed._card', ['program' => $program])
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if ($guide)
        <a href="{{ $guide->file ? $guide->file->url() : $guide->url }}" target="_blank" rel="noopener noreferrer"
           class="mt-10 flex items-center gap-4 rounded-2xl border border-forest-700/30 bg-forest-950 p-5 text-white transition-all hover:border-gold-500/50">
            <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-gold-500 text-forest-950"><x-lucide-file-text class="size-5" /></span>
            <span class="min-w-0 flex-1">
                <span class="block text-sm font-bold">{{ $guide->title_ar }}</span>
                @if ($guide->notes)<span class="mt-1 block text-[11px] text-amber-300">{{ $guide->notes }}</span>@endif
            </span>
            <x-lucide-external-link class="size-4 text-gold-300" />
        </a>
    @endif

    <p class="mt-10 rounded-2xl border border-amber-200 bg-amber-50/70 px-5 py-4 text-[12px] leading-loose text-amber-800">
        <x-lucide-info class="me-1 inline size-3.5 align-[-2px]" />
        البيانات المعروضة استرشادية وقد لا تعكس الشروط أو المواعيد النهائية المعتمدة. يُرجى مراجعة المصدر الرسمي لمسار واعد على موقع وزارة التعليم للتحقق من التفاصيل.
    </p>
</div>
@endsection
