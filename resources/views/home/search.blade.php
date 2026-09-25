@php
    $groupIcons = collect([
        'sections' => 'layout-grid', 'tracks' => 'graduation-cap', 'universities' => 'university',
        'news' => 'newspaper', 'journey' => 'route', 'faq' => 'circle-help', 'kb' => 'bot',
    ])->map(fn ($icon) => svg('lucide-'.$icon, 'size-4.5')->toHtml());
@endphp

<div x-data="searchOverlay(@js(route('search')), @js($quickLinks), @js($groupIcons))"
     x-show="open" x-cloak x-transition.opacity @click.self="open = false" @keydown.escape.window="open = false"
     class="fixed inset-0 z-[75] flex items-start justify-center bg-forest-950/80 px-4 pt-[10vh] backdrop-blur-md">
    <div x-show="open" x-trap="open" role="dialog" aria-modal="true" aria-label="البحث في المنصة"
         x-transition:enter="transition duration-400 ease-out-expo" x-transition:enter-start="-translate-y-6 scale-[.98] opacity-0" x-transition:enter-end="translate-y-0 scale-100 opacity-100"
         class="flex w-full max-w-2xl flex-col overflow-hidden rounded-3xl border border-forest-800/15 bg-white shadow-[0_50px_120px_-30px_rgba(0,0,0,0.6)]">
        {{-- input --}}
        <div class="flex items-center gap-3 border-b border-forest-800/10 px-5 py-4">
            <x-lucide-search class="size-5 shrink-0 text-forest-700" />
            <input x-ref="input" x-model="query" type="search" aria-label="ابحث في المنصة"
                   @keydown.arrow-down.prevent="move(1)" @keydown.arrow-up.prevent="move(-1)" @keydown.enter.prevent="go(flat[active])"
                   placeholder="ابحث في المنصة… مسار، جامعة، خبر، سؤال، محطة"
                   class="min-w-0 flex-1 bg-transparent text-base font-medium text-ink outline-none placeholder:text-slate-400">
            <button type="button" @click="open = false"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-forest-800/15 px-2.5 py-1.5 font-plex text-[10px] font-bold text-slate-400 transition-colors hover:border-gold-500/60 hover:text-gold-600">
                ESC
                <x-lucide-x class="size-3.5" />
            </button>
        </div>

        {{-- results --}}
        <div x-ref="list" class="max-h-[56vh] overflow-y-auto p-3">
            <template x-if="flat.length === 0 && !loading">
                <div class="px-4 py-12 text-center">
                    <x-lucide-search-x class="mx-auto size-9 text-forest-700/30" />
                    <p class="mt-3 text-sm font-bold text-forest-800">لا توجد نتائج مطابقة لـ «<span x-text="query"></span>»</p>
                    <p class="mt-1.5 text-xs text-slate-500">جرّب كلمات مثل: المكافأة، أكسفورد، مسار الروّاد، تأشيرة</p>
                </div>
            </template>
            <template x-for="g in groups" :key="g.name">
                <div class="mb-1.5">
                    <div class="flex items-center gap-2 px-3 pb-1.5 pt-3 text-[11px] font-bold text-gold-600">
                        <span class="[&>svg]:size-3.5" x-html="icon(g.groupId)"></span>
                        <span x-text="g.name"></span>
                    </div>
                    <template x-for="r in g.items" :key="r.id">
                        <button type="button" :data-idx="r.idx" @click="go(r)" @mousemove="active = r.idx"
                                class="group flex w-full items-center gap-3.5 rounded-2xl px-3.5 py-3 text-start transition-colors"
                                :class="active === r.idx ? 'bg-forest-50' : 'bg-transparent'">
                            <span class="grid size-9 shrink-0 place-items-center rounded-xl transition-colors"
                                  :class="active === r.idx ? 'bg-forest-800 text-gold-300' : 'bg-sand text-forest-700'" x-html="icon(r.groupId)"></span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-bold text-ink" x-text="r.title"></span>
                                <span x-show="r.sub" class="mt-0.5 block truncate text-xs text-slate-500" x-text="r.sub"></span>
                            </span>
                            <span x-show="active === r.idx"><x-lucide-corner-down-left class="size-4 shrink-0 text-gold-600" /></span>
                            <span x-show="active !== r.idx"><x-lucide-arrow-up-left class="size-4 shrink-0 text-slate-300 transition-colors group-hover:text-forest-600" /></span>
                        </button>
                    </template>
                </div>
            </template>
        </div>

        {{-- footer hints --}}
        <div class="flex flex-wrap items-center gap-x-5 gap-y-1.5 border-t border-forest-800/10 bg-sand/60 px-5 py-3 text-[10px] font-semibold text-slate-400">
            <span class="flex items-center gap-1.5"><x-lucide-corner-down-left class="size-3" />Enter للانتقال</span>
            <span class="font-plex" dir="ltr">↑↓ للتنقل بين النتائج</span>
            <span class="ms-auto flex items-center gap-1.5 text-gold-600"><x-lucide-sparkles class="size-3" />بحث فوري في كل محتوى المنصة</span>
        </div>
    </div>
</div>
