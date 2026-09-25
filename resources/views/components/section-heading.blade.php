@props(['eyebrow', 'desc' => null, 'dark' => false, 'center' => false])

<div {{ $attributes->class(['max-w-3xl', 'mx-auto text-center' => $center]) }}>
    <div @class(['reveal flex items-center gap-3', 'justify-center' => $center])>
        <x-star-emblem @class(['size-5', 'text-gold-400' => $dark, 'text-gold-500' => ! $dark]) />
        <span @class(['text-sm font-semibold tracking-wide', 'text-gold-300' => $dark, 'text-forest-600' => ! $dark])>{{ $eyebrow }}</span>
        <span @class(['h-px w-16', 'bg-white/20' => $dark, 'bg-forest-200' => ! $dark])></span>
    </div>
    <h2 @class(['reveal mt-5 text-4xl font-bold leading-[1.15] tracking-tight md:text-5xl md:leading-[1.12]', 'text-white' => $dark, 'text-ink' => ! $dark]) style="--delay: .08s">
        {{ $slot }}
    </h2>
    @if ($desc)
        <p @class(['reveal mt-5 text-lg leading-relaxed', 'text-slate-300' => $dark, 'text-slate-600' => ! $dark]) style="--delay: .16s">{{ $desc }}</p>
    @endif
</div>
