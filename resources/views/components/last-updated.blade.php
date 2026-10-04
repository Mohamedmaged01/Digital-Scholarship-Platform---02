{{-- شارة «آخر تحديث • المصدر • الإصدار» لكل محتوى قابل للتغير --}}
@props(['date' => null, 'source' => null, 'version' => null, 'dark' => false, 'compact' => false])
<div {{ $attributes->class([
    'flex flex-wrap items-center gap-x-3 gap-y-1 rounded-xl border px-3.5 py-2 text-[11px] font-semibold',
    'border-white/12 bg-white/[0.06] text-slate-300' => $dark,
    'border-forest-800/10 bg-sand/40 text-slate-500' => ! $dark,
    'rounded-lg px-3 py-1.5 text-[10px]' => $compact,
]) }}>
    <span class="flex items-center gap-1.5">
        <x-lucide-calendar-clock @class(['size-3.5', 'text-gold-300' => $dark, 'text-gold-600' => ! $dark]) />
        آخر تحديث: {{ $date ? \Illuminate\Support\Carbon::parse($date)->locale('ar')->translatedFormat('j F Y') : 'غير محدد' }}
    </span>
    @if ($source)
        <span @class(['rounded-full px-2 py-0.5', 'bg-white/10 text-white/70' => $dark, 'bg-white text-slate-500' => ! $dark])>المصدر: {{ $source }}</span>
    @endif
    @if ($version)
        <span @class(['font-plex', 'text-gold-300/80' => $dark, 'text-forest-700/70' => ! $dark]) dir="ltr">v{{ $version }}</span>
    @endif
</div>
