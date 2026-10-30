{{-- عنوان القسم: سطر واحد في الشاشات الكبيرة (العربية)، وخط مناسب. compact للأعمدة الضيقة --}}
@props(['eyebrow', 'desc' => null, 'dark' => false, 'center' => false, 'compact' => false])

<div {{ $attributes->class(['mx-auto text-center' => $center]) }}>
    <div @class(['reveal flex items-center gap-3', 'justify-center' => $center])>
        <x-star-emblem @class(['size-5', 'text-gold-400' => $dark, 'text-gold-500' => ! $dark]) />
        <span @class(['text-sm font-semibold tracking-wide', 'text-gold-300' => $dark, 'text-forest-600' => ! $dark])>{{ $eyebrow }}</span>
        <span @class(['h-px w-16', 'bg-white/20' => $dark, 'bg-forest-200' => ! $dark])></span>
    </div>
    <h2 @class([
        'reveal mt-5 text-balance font-bold leading-[1.25] tracking-tight',
        'text-3xl md:text-[2.4rem] lg:rtl:whitespace-nowrap' => ! $compact,
        'text-[1.75rem] md:text-[2rem] xl:rtl:whitespace-nowrap' => $compact,
        'text-white' => $dark,
        'text-ink' => ! $dark,
    ]) style="--delay: .08s">
        {{ $slot }}
    </h2>
    @if ($desc)
        <p @class(['reveal mt-5 max-w-3xl text-lg leading-relaxed', 'mx-auto' => $center, 'text-slate-300' => $dark, 'text-slate-600' => ! $dark]) style="--delay: .16s">{{ $desc }}</p>
    @endif
</div>
