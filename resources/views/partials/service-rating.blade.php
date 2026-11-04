{{--
    تقييم أداء الخدمة (نموذج الوكالة): زر عائم بجوار المساعد الذكي، ويظهر النموذج تلقائيًا
    بعد إغلاق المساعد أو نافذة البحث. حقل الملاحظات يظهر (إلزاميًا) عند «راضٍ ويحتاج إلى تطوير» و«غير راضٍ».
--}}
@php
    $ratings = config('kasp.service_ratings');
    $tones = [
        'emerald' => ['dot' => 'bg-emerald-500', 'on' => 'border-emerald-500 bg-emerald-50 ring-2 ring-emerald-500/25', 'icon' => 'text-emerald-600'],
        'amber' => ['dot' => 'bg-amber-400', 'on' => 'border-amber-400 bg-amber-50 ring-2 ring-amber-400/30', 'icon' => 'text-amber-500'],
        'red' => ['dot' => 'bg-red-500', 'on' => 'border-red-500 bg-red-50 ring-2 ring-red-500/25', 'icon' => 'text-red-600'],
    ];
@endphp

<div x-data="serviceRating(@js(route('feedback.store')))" @rate-service.window="show($event.detail ?? {})" @keydown.escape.window="open = false">
    {{-- الزر العائم --}}
    <button type="button" @click="open ? (open = false) : show()" :aria-expanded="open.toString()"
            class="animate-enter fixed bottom-6 left-[5.25rem] z-[59] inline-flex items-center gap-2 rounded-full border border-forest-800/10 bg-white/95 py-2.5 pe-4 ps-3 text-xs font-bold text-forest-800 shadow-xl shadow-forest-900/15 backdrop-blur transition-all hover:-translate-y-0.5 hover:border-gold-500/60"
            style="--delay: 1.8s">
        <x-lucide-star class="size-4 text-gold-500" />
        <span class="hidden sm:inline">{{ __('قيّم الخدمة') }}</span>
        <span class="sr-only sm:hidden">{{ __('قيّم الخدمة') }}</span>
    </button>

    {{-- النموذج --}}
    <div x-show="open" x-cloak role="dialog" aria-modal="false" aria-labelledby="rating-title"
         x-transition:enter="transition duration-300 ease-out-expo" x-transition:enter-start="translate-y-6 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
         x-transition:leave="transition duration-200 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="translate-y-4 opacity-0"
         class="fixed bottom-24 left-5 z-[61] max-h-[calc(100vh-7rem)] w-[min(92vw,390px)] overflow-y-auto rounded-3xl border border-forest-800/10 bg-white shadow-2xl shadow-forest-900/25">
        <div class="flex items-start justify-between gap-3 border-b border-forest-800/8 px-5 pb-4 pt-5">
            <div>
                <h2 id="rating-title" class="flex items-center gap-2 text-[15px] font-bold text-ink">
                    <x-lucide-star class="size-4.5 text-gold-500" /> {{ __('نموذج تقييم مستوى الخدمة') }}
                </h2>
                <p class="mt-1.5 text-[12px] leading-relaxed text-slate-500" x-show="! done">{{ __('يسعدنا معرفة رأيك لضمان تقديم أفضل تجربة بحث ممكنة. يرجى اختيار التقييم المناسب:') }}</p>
            </div>
            <button type="button" @click="open = false" aria-label="{{ __('إغلاق') }}" class="grid size-8 shrink-0 place-items-center rounded-lg text-slate-400 transition-colors hover:bg-sand hover:text-forest-800">
                <x-lucide-x class="size-4.5" />
            </button>
        </div>

        <template x-if="done">
            <div class="px-6 py-10 text-center">
                <x-lucide-circle-check class="mx-auto size-11 text-emerald-500" />
                <p class="mt-3 text-sm font-bold text-forest-800">{{ __('شكرًا لك، تم استلام تقييمك.') }}</p>
                <p class="mt-1 text-[12px] text-slate-500">{{ __('ملاحظاتك تساعدنا على تحسين البحث والمساعد الذكي.') }}</p>
            </div>
        </template>

        <form x-show="! done" @submit.prevent="submit()" class="space-y-3 px-5 py-4" novalidate>
            <fieldset class="space-y-2">
                <legend class="sr-only">{{ __('التقييم') }}</legend>
                @foreach ($ratings as $key => $r)
                    <label class="flex cursor-pointer items-start gap-3 rounded-2xl border px-3.5 py-3 transition-all"
                           :class="rating === @js($key) ? @js($tones[$r['tone']]['on']) : 'border-forest-800/12 hover:border-forest-800/30'">
                        <input type="radio" name="rating" value="{{ $key }}" x-model="rating" class="sr-only">
                        <span class="mt-0.5 size-3 shrink-0 rounded-full {{ $tones[$r['tone']]['dot'] }}" aria-hidden="true"></span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-[13px] font-bold text-ink">{{ __($r['label']) }}</span>
                            <span class="mt-0.5 block text-[11.5px] leading-relaxed text-slate-500">{{ __($r['hint']) }}</span>
                        </span>
                        @svg('lucide-'.$r['icon'], 'size-5 shrink-0 '.$tones[$r['tone']]['icon'])
                    </label>
                @endforeach
            </fieldset>

            <div x-show="notesRequired" x-cloak x-transition.opacity>
                <label for="rating-notes" class="flex items-center gap-1.5 text-[12px] font-bold text-forest-800">
                    <x-lucide-notebook-pen class="size-3.5 text-gold-600" /> {{ __('ملاحظات المستفيد') }}
                    <span class="text-red-500">({{ __('إلزامية') }})</span>
                </label>
                <p class="mt-1 text-[11px] text-slate-400">{{ __('إذا كان لديك أي مقترحات أو تفاصيل أخرى لتحسين محرك البحث، يرجى كتابتها هنا:') }}</p>
                <textarea id="rating-notes" x-model="notes" rows="3" maxlength="2000" :required="notesRequired"
                          class="mt-2 w-full resize-none rounded-xl border border-forest-800/15 bg-sand/40 px-3.5 py-2.5 text-sm text-ink outline-none transition-all focus:border-forest-600 focus:bg-white focus:ring-4 focus:ring-forest-600/10"></textarea>
            </div>

            <p x-show="error" x-text="error" role="alert" class="rounded-xl bg-red-50 px-3 py-2 text-[12px] font-bold text-red-600"></p>

            <button type="submit" :disabled="sending"
                    class="flex w-full items-center justify-center gap-2 rounded-full bg-forest-800 py-3 text-sm font-bold text-gold-300 transition-all hover:bg-forest-700 disabled:opacity-60">
                <x-lucide-send-horizontal class="rtl-flip size-4" /> {{ __('إرسال التقييم') }}
            </button>
        </form>
    </div>
</div>
