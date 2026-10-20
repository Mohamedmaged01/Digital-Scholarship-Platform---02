{{-- شارة «العام الأكاديمي • المصدر» — ملاحظة 37: لا تاريخ «آخر تحديث»، فالقوائم مرتبطة بالعام الأكاديمي --}}
@props(['version' => null, 'source' => null, 'dark' => false, 'compact' => false])
@php $year = $version ?: (\App\Models\AcademicYear::where('is_current', true)->value('id') ?? '2026-2027'); @endphp
<div {{ $attributes->class([
    'flex flex-wrap items-center gap-x-3 gap-y-1 rounded-xl border px-3.5 py-2 text-[11px] font-semibold',
    'border-white/12 bg-white/[0.06] text-slate-300' => $dark,
    'border-forest-800/10 bg-sand/40 text-slate-500' => ! $dark,
    'rounded-lg px-3 py-1.5 text-[10px]' => $compact,
]) }}>
    <span class="flex items-center gap-1.5">
        <x-lucide-calendar-range @class(['size-3.5', 'text-gold-300' => $dark, 'text-gold-600' => ! $dark]) />
        {{ __('العام الأكاديمي:') }} <span dir="ltr" class="font-plex">{{ str_replace('-', '–', $year) }}</span>
    </span>
    @if ($source)
        <span @class(['rounded-full px-2 py-0.5', 'bg-white/10 text-white/70' => $dark, 'bg-white text-slate-500' => ! $dark])>{{ __('المصدر: :source', ['source' => __($source)]) }}</span>
    @endif
</div>
