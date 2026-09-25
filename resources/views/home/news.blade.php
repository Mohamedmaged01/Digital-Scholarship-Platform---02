@php
    $cats = config('kasp.news_categories');
    $tabs = ['all' => 'جميع الأخبار والإعلانات'] + collect($cats)->map(fn ($c) => $c['tab'])->all();
@endphp

<section id="news" class="relative overflow-hidden bg-cream py-24 md:py-32"
         x-data="newsCenter(@js($news->map->toPublicArray()->values()))">
    <x-star-emblem class="animate-spin-slower absolute -start-12 top-24 size-56 text-forest-700/[0.05]" stroke-width="0.6" />
    <div class="relative mx-auto max-w-7xl px-5 md:px-8">
        <div class="flex flex-wrap items-end justify-between gap-8">
            <x-section-heading eyebrow="المركز الإعلامي والمستجدات"
                               desc="متابعة حية لقرارات الابتعاث، مواعيد فتح المسارات، وتحديثات القوائم الأكاديمية والشراكات الدولية.">
                الأخبار والإعلانات
                <span class="text-shimmer-gold"> الرسمية</span>
            </x-section-heading>
            <div class="reveal flex flex-col items-start gap-3" style="--delay: .15s">
                <span class="inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-4 py-2 text-xs font-bold text-emerald-700">
                    <span class="relative flex size-2">
                        <span class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-500 opacity-60"></span>
                        <span class="relative inline-flex size-2 rounded-full bg-emerald-500"></span>
                    </span>
                    متابعة حيّة — تُحدَّث من الإدارة لحظيًا
                </span>
                <a href="{{ route('admin.news.index') }}"
                   class="group inline-flex items-center gap-2 rounded-full bg-forest-800 px-6 py-3.5 text-sm font-bold text-gold-300 transition-all duration-300 hover:-translate-y-0.5 hover:bg-forest-700 hover:shadow-[0_14px_36px_-12px_rgba(8,39,29,0.55)]">
                    <x-lucide-file-plus-2 class="size-4.5 transition-transform group-hover:scale-110" />
                    رفع وإضافة خبر جديد
                </a>
            </div>
        </div>

        {{-- filter tabs --}}
        <div class="reveal mt-12 flex flex-wrap gap-2" style="--delay: .1s" role="group" aria-label="تصنيف الأخبار">
            @foreach ($tabs as $key => $label)
                <button type="button" @click="filter = '{{ $key }}'" :aria-pressed="filter === '{{ $key }}'"
                        class="rounded-full border px-5 py-2.5 text-sm font-bold transition-all duration-300"
                        :class="filter === '{{ $key }}' ? 'border-forest-800 bg-forest-800 text-gold-300 shadow-lg shadow-forest-800/25' : 'border-forest-800/15 bg-white text-slate-600 hover:border-forest-700/50 hover:text-forest-800'">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div x-show="featuredId !== null" class="mt-10 grid grid-cols-1 gap-6 lg:grid-cols-12">
            {{-- featured --}}
            <div class="lg:col-span-5">
                @foreach ($news as $n)
                    <article x-show="featuredId === {{ $n->id }}" @if (! $loop->first) x-cloak @endif
                             x-transition:enter="transition duration-500 ease-out-expo" x-transition:enter-start="translate-y-7 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
                             class="pattern-star-dark grain group relative flex h-full flex-col overflow-hidden rounded-[2rem] bg-forest-950 p-8 md:p-10">
                        <x-star-emblem class="animate-spin-slower absolute -bottom-10 -start-10 size-56 text-white/[0.06]" stroke-width="0.6" />
                        <div class="relative flex h-full flex-col">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 rounded-full border border-gold-500/50 px-3.5 py-1.5 text-[11px] font-bold text-gold-200">
                                    <x-lucide-radio class="size-3.5" />
                                    {{ $cats[$n->category]['short'] }}
                                </span>
                                @if ($n->pinned)
                                    <span class="inline-flex items-center gap-1.5 rounded-full border border-gold-400/60 bg-gold-500 px-3.5 py-1.5 text-[11px] font-bold text-forest-950">
                                        <x-lucide-pin class="size-3.5" />
                                        خبر مثبّت
                                    </span>
                                @endif
                            </div>
                            <h3 class="mt-6 text-2xl font-bold leading-snug text-white md:text-[1.75rem] md:leading-snug">{{ $n->title }}</h3>
                            <p class="mt-4 text-sm leading-loose text-slate-300">{{ $n->excerpt }}</p>

                            <div class="mt-auto pt-8">
                                <div class="flex flex-wrap items-center gap-x-5 gap-y-2 border-t border-white/10 pt-5 text-[12px] font-medium text-slate-400">
                                    <span class="flex items-center gap-1.5"><x-lucide-calendar-days class="size-3.5 text-gold-400" /><span dir="ltr" class="font-plex">{{ $n->published_on->format('Y-m-d') }}</span></span>
                                    <span class="flex items-center gap-1.5"><x-lucide-landmark class="size-3.5 text-gold-400" />{{ $n->author }}</span>
                                    <span class="flex items-center gap-1.5"><x-lucide-clock-3 class="size-3.5 text-gold-400" />{{ $n->read_minutes }} دقائق قراءة</span>
                                </div>
                                <button type="button" @click="open({{ $n->id }})"
                                        class="group/btn mt-6 inline-flex items-center gap-2 rounded-full bg-gold-500 px-7 py-3.5 text-sm font-bold text-forest-950 transition-all hover:bg-gold-400 hover:shadow-[0_14px_36px_-10px_rgba(201,163,56,0.6)]">
                                    التفاصيل الكاملة
                                    <x-lucide-arrow-left class="size-4 transition-transform group-hover/btn:-translate-x-1" />
                                </button>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- rest --}}
            <div class="flex flex-col gap-4 lg:col-span-7">
                @foreach ($news as $n)
                    <article x-show="inList({{ $n->id }})" @if ($loop->first) x-cloak @endif
                             x-transition:enter="transition duration-450 ease-out-expo" x-transition:enter-start="-translate-x-7 opacity-0" x-transition:enter-end="translate-x-0 opacity-100"
                             @click="open({{ $n->id }})" @keydown.enter="open({{ $n->id }})" tabindex="0" role="button"
                             class="group flex flex-1 cursor-pointer flex-col justify-between rounded-3xl border border-forest-800/10 bg-white p-6 transition-all duration-300 hover:-translate-y-1 hover:border-gold-500/50 hover:shadow-[0_24px_50px_-26px_rgba(8,39,29,0.3)] md:p-7">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-[11px] font-bold {{ $cats[$n->category]['chipClass'] }}">
                                    <span class="size-1.5 rounded-full {{ $cats[$n->category]['dotClass'] }}"></span>
                                    {{ $cats[$n->category]['short'] }}
                                </span>
                                @if ($n->pinned)
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-gold-600"><x-lucide-pin class="size-3.5" />خبر مثبّت</span>
                                @endif
                                <span class="ms-auto font-plex text-xs font-semibold text-slate-400" dir="ltr">{{ $n->published_on->format('Y-m-d') }}</span>
                            </div>
                            <h3 class="mt-3 text-lg font-bold leading-relaxed text-ink transition-colors group-hover:text-forest-700 md:text-xl md:leading-relaxed">{{ $n->title }}</h3>
                            <p class="mt-2 line-clamp-2 text-sm leading-relaxed text-slate-500">{{ $n->excerpt }}</p>
                        </div>
                        <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-1.5 border-t border-dashed border-forest-800/10 pt-3.5 text-[12px] font-medium text-slate-500">
                            <span class="flex items-center gap-1.5"><x-lucide-landmark class="size-3.5 text-forest-600" />{{ $n->author }}</span>
                            <span class="flex items-center gap-1.5"><x-lucide-clock-3 class="size-3.5 text-forest-600" />{{ $n->read_minutes }} دقائق قراءة</span>
                            <span class="ms-auto inline-flex items-center gap-1.5 text-[12px] font-bold text-forest-700 transition-all group-hover:gap-2.5 group-hover:text-gold-600">
                                التفاصيل الكاملة
                                <x-lucide-arrow-left class="size-3.5" />
                            </span>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>

        <div x-show="featuredId === null" @if ($news->isNotEmpty()) x-cloak @endif class="mt-12 rounded-3xl border border-dashed border-forest-800/20 bg-white/60 p-14 text-center">
            <p class="text-lg font-bold text-forest-800">لا توجد أخبار في هذا التصنيف حاليًا</p>
            <p class="mt-2 text-sm text-slate-500">يمكن للإدارة إضافة أخبار جديدة من لوحة التحكم.</p>
        </div>
    </div>

    {{-- detail modal --}}
    <div x-show="selected" x-cloak x-transition.opacity @click.self="selected = null" @keydown.escape.window="selected = null"
         class="fixed inset-0 z-[70] flex items-center justify-center bg-forest-950/80 p-4 backdrop-blur-md">
        <template x-if="selected">
            <article x-trap.noscroll="selected" role="dialog" aria-modal="true" :aria-label="selected.title"
                     class="animate-enter max-h-[86vh] w-full max-w-3xl overflow-y-auto rounded-3xl bg-white shadow-2xl">
                <div class="pattern-star-dark relative bg-forest-950 px-7 py-8 md:px-10">
                    <button type="button" @click="selected = null" aria-label="إغلاق"
                            class="absolute end-5 top-5 grid size-10 place-items-center rounded-xl border border-white/15 text-white/70 transition-all hover:border-gold-400/60 hover:text-gold-300">
                        <x-lucide-x class="size-5" />
                    </button>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-gold-500/50 bg-gold-500/15 px-3.5 py-1.5 text-[11px] font-bold text-gold-300">
                            <x-lucide-tag class="size-3.5" />
                            <span x-text="@js(collect($cats)->map(fn ($c) => $c['short']))[selected.category]"></span>
                        </span>
                        <span x-show="selected.pinned" class="inline-flex items-center gap-1.5 rounded-full bg-gold-500 px-3 py-1.5 text-[11px] font-bold text-forest-950">
                            <x-lucide-pin class="size-3.5" />
                            خبر مثبّت
                        </span>
                    </div>
                    <h3 class="mt-5 max-w-xl text-xl font-bold leading-relaxed text-white md:text-2xl md:leading-relaxed" x-text="selected.title"></h3>
                    <div class="mt-5 flex flex-wrap items-center gap-x-6 gap-y-2 text-[12px] font-medium text-slate-400">
                        <span class="flex items-center gap-1.5"><x-lucide-calendar-days class="size-4 text-gold-400" /><span dir="ltr" class="font-plex" x-text="selected.date"></span></span>
                        <span class="flex items-center gap-1.5"><x-lucide-landmark class="size-4 text-gold-400" /><span x-text="selected.author"></span></span>
                        <span class="flex items-center gap-1.5"><x-lucide-clock-3 class="size-4 text-gold-400" /><span x-text="`${selected.readMinutes} دقائق قراءة`"></span></span>
                    </div>
                </div>

                <div class="px-7 py-8 md:px-10">
                    <p class="rounded-2xl border-s-2 border-gold-500 bg-sand/70 p-5 text-[15px] font-semibold leading-loose text-forest-900" x-text="selected.excerpt"></p>
                    <div class="mt-6 space-y-5">
                        <template x-for="(p, i) in paragraphs" :key="i">
                            <p class="text-[15px] leading-loose text-slate-600" x-text="p"></p>
                        </template>
                    </div>
                    <div class="mt-8 flex items-center justify-between rounded-2xl border border-forest-800/10 bg-sand/50 px-5 py-4">
                        <span class="flex items-center gap-2 text-xs font-bold text-slate-500">
                            <x-star-emblem class="size-4 text-gold-500" />
                            <span x-text="`إعلان موثّق — ${selected.author}`"></span>
                        </span>
                        <button type="button" @click="selected = null"
                                class="inline-flex items-center gap-2 rounded-full bg-forest-800 px-5 py-2.5 text-xs font-bold text-gold-300 transition-all hover:bg-forest-700">
                            العودة للمركز الإعلامي
                            <x-lucide-arrow-left class="size-3.5" />
                        </button>
                    </div>
                </div>
            </article>
        </template>
    </div>
</section>
