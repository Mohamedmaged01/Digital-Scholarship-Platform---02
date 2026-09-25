@php
    $degrees = config('kasp.degrees');
    $tabs = ['all' => 'كافة المراحل'] + $degrees;
@endphp

<section id="tracks" class="relative overflow-hidden bg-cream py-24 md:py-32"
         x-data="tracksFilter(@js($tracks->mapWithKeys(fn ($t) => [$t->id => $t->degrees])))">
    <x-star-emblem class="animate-spin-slower absolute -end-12 top-20 size-64 text-forest-700/[0.06]" stroke-width="0.6" />
    <x-star-emblem class="animate-spin-slower absolute -start-10 bottom-24 size-44 text-gold-500/[0.08]" stroke-width="0.6" />

    <div class="relative mx-auto max-w-7xl px-5 md:px-8">
        <x-section-heading eyebrow="برنامج تنمية القدرات البشرية"
                           desc="ستة مسارات نوعية منبثقة من برنامج تنمية القدرات البشرية لتحقيق الريادة الوطنية والتنافسية العالمية.">
            مسارات الابتعاث الاستراتيجية —
            <span class="text-shimmer-gold"> اختر المسار المتوافق مع طموحك</span>
        </x-section-heading>

        {{-- degree filter --}}
        <div class="reveal mt-12 flex flex-wrap items-center justify-between gap-4" style="--delay: .1s">
            <div class="flex flex-wrap gap-2" role="group" aria-label="تصفية حسب المرحلة">
                @foreach ($tabs as $key => $label)
                    <button type="button" @click="degree = '{{ $key }}'" :aria-pressed="degree === '{{ $key }}'"
                            class="rounded-full border px-6 py-2.5 text-sm font-bold transition-all duration-300"
                            :class="degree === '{{ $key }}' ? 'border-forest-800 bg-forest-800 text-gold-300 shadow-lg shadow-forest-800/25' : 'border-forest-800/15 bg-white text-slate-600 hover:border-forest-700/50 hover:text-forest-800'">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
            <div class="flex items-center gap-2 text-sm font-semibold text-slate-500">
                <x-lucide-graduation-cap class="size-4.5 text-forest-600" />
                <span dir="ltr" class="rounded-md bg-forest-800 px-2 py-0.5 font-plex text-xs font-bold text-gold-300" x-text="count">{{ $tracks->count() }}</span>
                <span x-text="count === 1 ? 'مسار متاح' : 'مسارات متاحة'">مسارات متاحة</span>
            </div>
        </div>

        {{-- cards --}}
        <div class="mt-10 grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($tracks as $t)
                @php $featured = $t->isFeatured(); @endphp
                <article x-show="shows({{ $t->id }})"
                         x-transition:enter="transition duration-450 ease-out-expo" x-transition:enter-start="scale-95 translate-y-6 opacity-0" x-transition:enter-end="scale-100 translate-y-0 opacity-100"
                         @class([
                             'group relative flex flex-col overflow-hidden rounded-3xl border bg-white transition-all duration-500 hover:-translate-y-2 hover:shadow-[0_36px_70px_-30px_rgba(8,39,29,0.4)]',
                             'border-gold-500/60 shadow-[0_24px_60px_-28px_rgba(176,140,47,0.45)]' => $featured,
                             'border-forest-800/10 hover:border-gold-500/50' => ! $featured,
                         ])>
                    <div class="relative h-48 overflow-hidden">
                        <img src="{{ asset(ltrim($t->image, '/')) }}" alt="{{ $t->name }}" loading="lazy"
                             class="h-full w-full object-cover transition-transform duration-[1.2s] ease-out group-hover:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-forest-950/85 via-forest-950/25 to-transparent"></div>

                        <span class="absolute start-4 top-4 inline-flex items-center gap-1.5 rounded-full border border-gold-400/50 bg-forest-950/60 px-3.5 py-1.5 text-[11px] font-bold text-gold-300 backdrop-blur-md">
                            <x-lucide-trophy class="size-3.5" />
                            {{ $t->badge }}
                        </span>

                        <span dir="ltr" class="text-outline absolute bottom-3 end-5 select-none font-plex text-6xl font-bold leading-none transition-transform duration-500 group-hover:scale-110"
                              style="--stroke: 1.5px rgba(255,255,255,0.35)">{{ $t->code }}</span>

                        <div class="absolute bottom-4 start-4 flex flex-wrap gap-1.5">
                            @foreach ($t->degrees as $d)
                                <span class="rounded-full bg-white/12 px-2.5 py-1 text-[10px] font-bold text-white backdrop-blur-md">{{ $degrees[$d] ?? $d }}</span>
                            @endforeach
                        </div>
                    </div>

                    <div class="relative -mt-7 flex justify-end pe-6">
                        <span class="grid size-14 place-items-center rounded-2xl border-4 border-white bg-forest-800 text-gold-400 shadow-lg shadow-forest-900/30 transition-transform duration-500 group-hover:-rotate-6 group-hover:scale-110">
                            @svg('lucide-'.$t->icon, 'size-6.5')
                        </span>
                    </div>

                    <div class="flex flex-1 flex-col px-6 pb-6 pt-2">
                        <h3 class="text-2xl font-bold text-ink">{{ $t->name }}</h3>
                        <p dir="ltr" class="mt-1.5 text-right font-plex text-[11px] font-semibold tracking-[0.14em] text-gold-600">{{ $t->en_subtitle }}</p>
                        <p class="mt-4 text-sm leading-relaxed text-slate-600">{{ $t->description }}</p>

                        <div class="mt-5 grid grid-cols-2 gap-2.5">
                            <div class="rounded-2xl bg-sand px-4 py-3">
                                <div class="flex items-center gap-1.5 text-[11px] font-bold text-slate-500">
                                    <x-lucide-gauge class="size-3.5 text-gold-600" />
                                    أدنى معدل مطلوب
                                </div>
                                <div class="mt-1.5 font-plex text-sm font-bold text-forest-800" dir="ltr">{{ $t->gpa }}</div>
                            </div>
                            <div class="rounded-2xl bg-sand px-4 py-3">
                                <div class="flex items-center gap-1.5 text-[11px] font-bold text-slate-500">
                                    <x-lucide-globe class="size-3.5 text-gold-600" />
                                    نطاق التصنيف العالمي
                                </div>
                                <div class="mt-1.5 text-sm font-bold text-forest-800">{{ $t->ranking }}</div>
                            </div>
                        </div>

                        <div class="mt-5 flex flex-wrap gap-2">
                            @foreach ($t->fields as $f)
                                <span class="rounded-full bg-forest-50 px-3 py-1.5 text-[11px] font-bold text-forest-700">{{ $f }}</span>
                            @endforeach
                            @if ($t->extra_fields > 0)
                                <span class="rounded-full border border-dashed border-gold-500/60 bg-gold-500/5 px-3 py-1.5 text-[11px] font-bold text-gold-700">+{{ $t->extra_fields }} أخرى</span>
                            @endif
                        </div>

                        <a href="#matcher" @class([
                            'group/cta mt-6 inline-flex items-center justify-center gap-2 rounded-full border py-3.5 text-sm font-bold transition-all duration-300',
                            'border-gold-500/70 bg-gold-500/10 text-forest-900 hover:bg-gold-500 hover:text-forest-950 hover:shadow-[0_14px_36px_-12px_rgba(201,163,56,0.7)]' => $featured,
                            'border-forest-800/20 text-forest-800 hover:bg-forest-800 hover:text-gold-300 hover:shadow-[0_14px_36px_-14px_rgba(8,39,29,0.6)]' => ! $featured,
                        ])>
                            التقديم على هذا المسار
                            <x-lucide-arrow-up-left class="size-4 transition-transform duration-300 group-hover/cta:-translate-x-1 group-hover/cta:-translate-y-1" />
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
