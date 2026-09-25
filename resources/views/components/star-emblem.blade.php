{{-- شعار النجمة الثمانية (ربع الحزب) --}}
@props(['strokeWidth' => 1.4])
<svg viewBox="0 0 48 48" fill="none" aria-hidden="true" {{ $attributes }}>
    <rect x="10" y="10" width="28" height="28" stroke="currentColor" stroke-width="{{ $strokeWidth }}" />
    <rect x="10" y="10" width="28" height="28" stroke="currentColor" stroke-width="{{ $strokeWidth }}" transform="rotate(45 24 24)" />
    <circle cx="24" cy="24" r="4.5" fill="currentColor" />
</svg>
