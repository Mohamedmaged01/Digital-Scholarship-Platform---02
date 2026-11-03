@extends('layouts.admin')
@section('page-title', 'تقييم الخدمة')

@php
    $ratings = config('kasp.service_ratings');
    $sources = config('kasp.rating_sources');
    $tone = [
        'emerald' => ['bar' => 'bg-emerald-500', 'chip' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'icon' => 'text-emerald-600'],
        'amber' => ['bar' => 'bg-amber-400', 'chip' => 'bg-amber-50 text-amber-700 border-amber-200', 'icon' => 'text-amber-500'],
        'red' => ['bar' => 'bg-red-500', 'chip' => 'bg-red-50 text-red-700 border-red-200', 'icon' => 'text-red-600'],
    ];
@endphp

@section('admin')
<p class="mb-5 text-[13px] text-slate-500">تقييم المستفيدين لأداء البحث والمساعد الذكي — يظهر النموذج تلقائيًا بعد استخدامهما، ومن زر «قيّم الخدمة» العائم.</p>

{{-- الملخص --}}
<div class="grid gap-4 sm:grid-cols-3">
    @foreach ($ratings as $key => $r)
        @php $pct = $total ? round($counts[$key] * 100 / $total) : 0; @endphp
        <a href="{{ route('admin.ratings.index', $filter === $key ? [] : ['rating' => $key]) }}"
           @class(['rounded-3xl border bg-white p-5 shadow-sm transition-all hover:border-gold-500/60',
                   'border-gold-500 ring-4 ring-gold-500/20' => $filter === $key, 'border-forest-800/12' => $filter !== $key])>
            <div class="flex items-center justify-between gap-2">
                <span class="text-sm font-bold text-ink">{{ $r['label'] }}</span>
                @svg('lucide-'.$r['icon'], 'size-6 '.$tone[$r['tone']]['icon'])
            </div>
            <p class="mt-3 font-plex text-3xl font-bold text-forest-800" dir="ltr">{{ $counts[$key] }}</p>
            <div class="mt-3 h-2 overflow-hidden rounded-full bg-sand">
                <div class="h-full rounded-full {{ $tone[$r['tone']]['bar'] }}" style="width: {{ $pct }}%"></div>
            </div>
            <p class="mt-1.5 font-plex text-[11px] font-bold text-slate-400" dir="ltr">{{ $pct }}%</p>
        </a>
    @endforeach
</div>

{{-- القائمة --}}
<div class="mt-8 flex flex-wrap items-center justify-between gap-2">
    <h2 class="flex items-center gap-2 text-sm font-bold text-forest-800">
        <x-lucide-list class="size-4 text-gold-600" />
        {{ $filter ? 'تقييمات «'.$ratings[$filter]['label'].'»' : 'كل التقييمات' }} ({{ $items->count() }}{{ $total > $items->count() && ! $filter ? ' من '.$total : '' }})
    </h2>
    @if ($filter)
        <a href="{{ route('admin.ratings.index') }}" class="text-xs font-bold text-forest-700 underline underline-offset-4">عرض الكل</a>
    @endif
</div>
<div class="mt-3 space-y-2.5">
    @forelse ($items as $item)
        @php $r = $ratings[$item->rating] ?? null; @endphp
        <div class="rounded-2xl border border-forest-800/12 bg-white p-4 shadow-sm">
            <div class="flex flex-wrap items-center gap-2">
                @if ($r)
                    <span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-[11px] font-bold {{ $tone[$r['tone']]['chip'] }}">
                        @svg('lucide-'.$r['icon'], 'size-3.5') {{ $r['label'] }}
                    </span>
                @endif
                <span class="text-[11px] text-slate-400">{{ $sources[$item->source] ?? $item->source }}</span>
                <span class="ms-auto font-plex text-[11px] text-slate-400" dir="ltr">{{ $item->created_at->format('Y-m-d H:i') }}</span>
            </div>
            @if ($item->notes)
                <p class="mt-2.5 whitespace-pre-line rounded-xl bg-sand/60 px-3.5 py-2.5 text-[13px] leading-relaxed text-slate-700">{{ $item->notes }}</p>
            @endif
        </div>
    @empty
        <x-admin.empty icon="smile" title="لا توجد تقييمات بعد" />
    @endforelse
</div>
@endsection
