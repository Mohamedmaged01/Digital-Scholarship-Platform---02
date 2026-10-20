{{-- تبديل لغة الواجهة: العربية ⇄ English --}}
@props(['dark' => false])
@php
    $target = app()->getLocale() === 'ar' ? 'en' : 'ar';
    $label = $target === 'en' ? 'English' : 'العربية';
@endphp
<a href="{{ route('locale', $target) }}" hreflang="{{ $target }}" lang="{{ $target }}"
   aria-label="{{ $target === 'en' ? 'Switch to English' : 'التبديل إلى العربية' }}"
   {{ $attributes->class([
       'inline-flex h-11 shrink-0 items-center gap-1.5 rounded-xl border px-3.5 text-[13px] font-bold transition-all duration-300 hover:-translate-y-0.5',
       'border-white/15 text-white hover:border-gold-400/60 hover:text-gold-300' => $dark,
       'border-forest-800/15 bg-white/70 text-forest-800 backdrop-blur hover:border-gold-500/60 hover:bg-white hover:text-gold-600' => ! $dark,
   ]) }}>
    <x-lucide-languages class="size-4" />
    <span @class(['font-plex' => $target === 'en'])>{{ $label }}</span>
</a>
