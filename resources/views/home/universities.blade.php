@php
    $regions = ['all' => 'الكل'] + config('kasp.regions');
    $filterData = $universities->mapWithKeys(fn ($u) => [$u->id => [
        'nameAr' => $u->name_ar, 'nameEn' => $u->name_en, 'country' => $u->country, 'region' => $u->region, 'fields' => $u->fields,
    ]]);
@endphp

<section id="universities" class="relative overflow-hidden bg-cream py-24 md:py-32" x-data="universitiesFilter(@js($filterData))">
    <div class="relative mx-auto max-w-7xl px-5 md:px-8">
        <div class="grid grid-cols-1 items-end gap-10 lg:grid-cols-2">
            <x-section-heading eyebrow="الجامعات الموصى بها"
                               desc="أكثر من 200 جامعة معتمدة ضمن أفضل التصنيفات العالمية. تصفّح عيّنة منها، وابحث باسم الجامعة أو الدولة أو التخصص.">
                شراكات مع
                <span class="text-shimmer-gold"> نخبة جامعات العالم</span>
            </x-section-heading>
            <div class="reveal hidden lg:block" style="--delay: .15s">
                <div class="relative overflow-hidden rounded-3xl shadow-[0_30px_70px_-30px_rgba(8,39,29,0.45)]">
                    <img src="{{ asset('images/campus.jpg') }}" alt="حرم جامعي عريق" loading="lazy" class="h-64 w-full object-cover transition-transform duration-700 hover:scale-105">
                    <div class="absolute inset-0 bg-gradient-to-t from-forest-950/70 via-transparent to-transparent"></div>
                    <div class="absolute bottom-4 start-5 flex items-center gap-2 text-white">
                        <x-lucide-trophy class="size-4 text-gold-400" />
                        <span class="text-sm font-bold">قبول مباشر عبر شراكات البرنامج</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- controls --}}
        <div class="reveal mt-12 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between" style="--delay: .1s">
            <div class="flex flex-wrap gap-2" role="group" aria-label="تصفية حسب المنطقة">
                @foreach ($regions as $key => $label)
                    <button type="button" @click="region = '{{ $key }}'" :aria-pressed="region === '{{ $key }}'"
                            class="rounded-full border px-5 py-2.5 text-sm font-bold transition-all duration-300"
                            :class="region === '{{ $key }}' ? 'border-forest-800 bg-forest-800 text-gold-300 shadow-lg shadow-forest-800/25' : 'border-forest-800/15 bg-white text-slate-600 hover:border-forest-700/50 hover:text-forest-800'">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
            <div class="relative w-full max-w-sm">
                <x-lucide-search class="absolute start-4 top-1/2 size-4.5 -translate-y-1/2 text-slate-400" />
                <input x-model="query" type="search" placeholder="ابحث: هارفارد، طب، اليابان…" aria-label="ابحث في الجامعات"
                       class="w-full rounded-full border border-forest-800/15 bg-white py-3 pe-5 ps-11 text-sm font-medium text-ink outline-none transition-all placeholder:text-slate-400 focus:border-forest-600 focus:ring-4 focus:ring-forest-600/10">
            </div>
        </div>

        <div class="mt-6 flex items-center gap-2 text-sm font-semibold text-slate-500">
            <span class="rounded-md bg-forest-800 px-2 py-0.5 font-plex text-xs font-bold text-gold-300" dir="ltr" x-text="count">{{ $universities->count() }}</span>
            جامعة مطابقة
        </div>

        <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($universities as $u)
                <article x-show="shows({{ $u->id }})"
                         x-transition:enter="transition duration-400 ease-out-expo" x-transition:enter-start="scale-95 translate-y-5 opacity-0" x-transition:enter-end="scale-100 translate-y-0 opacity-100"
                         class="group relative overflow-hidden rounded-3xl border border-forest-800/10 bg-white p-6 transition-all duration-500 hover:-translate-y-1.5 hover:border-gold-500/50 hover:shadow-[0_28px_56px_-26px_rgba(8,39,29,0.3)]">
                    <div class="flex items-center gap-3">
                        <span dir="ltr" class="grid size-11 place-items-center rounded-2xl bg-forest-50 font-plex text-sm font-bold text-forest-800 transition-colors duration-500 group-hover:bg-forest-800 group-hover:text-gold-300">#{{ $u->rank }}</span>
                        <div class="leading-tight">
                            <h3 dir="ltr" class="text-right font-plex text-[15px] font-bold text-ink">{{ $u->name_en }}</h3>
                            <p class="mt-0.5 text-[13px] font-semibold text-slate-500">{{ $u->name_ar }}</p>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center gap-1.5 text-[13px] font-semibold text-forest-700">
                        <x-lucide-map-pin class="size-4 text-gold-600" />
                        {{ $u->city }}، {{ $u->country }}
                    </div>

                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        @foreach ($u->fields as $f)
                            <span class="rounded-full bg-forest-50 px-3 py-1 text-[11px] font-bold text-forest-700">{{ $f }}</span>
                        @endforeach
                    </div>

                    <div class="mt-5 flex items-center justify-between border-t border-dashed border-forest-800/10 pt-4">
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500">
                            <x-lucide-percent class="size-3.5 text-gold-600" />
                            معدل القبول {{ $u->acceptance }}
                        </span>
                        <span class="text-xs font-bold text-forest-600 opacity-0 transition-all duration-300 group-hover:opacity-100">ضمن الجامعات المعتمدة</span>
                    </div>
                </article>
            @endforeach
        </div>

        <div x-show="count === 0" x-cloak class="mt-10 rounded-3xl border border-dashed border-forest-800/20 bg-white/60 p-12 text-center">
            <p class="text-lg font-bold text-forest-800">لا توجد نتائج مطابقة</p>
            <p class="mt-2 text-sm text-slate-500">جرّب توسيع نطاق البحث أو اختيار منطقة أخرى.</p>
        </div>
    </div>
</section>
