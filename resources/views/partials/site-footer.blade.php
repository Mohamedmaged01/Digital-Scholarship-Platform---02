<footer class="relative">
    {{-- CTA banner --}}
    <div class="relative z-10 mx-auto -mb-24 max-w-6xl px-5 md:px-8">
        <div class="reveal pattern-star-dark grain relative overflow-hidden rounded-[2.5rem] border border-gold-500/30 bg-forest-800 px-8 py-14 text-center shadow-[0_50px_100px_-40px_rgba(8,39,29,0.8)] md:px-16">
            <div class="absolute -start-20 -top-20 size-64 rounded-full bg-gold-500/15 blur-[90px]"></div>
            <div class="absolute -bottom-24 -end-16 size-72 rounded-full bg-forest-500/30 blur-[100px]"></div>
            <x-brand-logo emblem light class="relative mx-auto h-16" />
            <h2 class="relative mx-auto mt-6 max-w-2xl text-3xl font-bold leading-snug text-white md:text-5xl md:leading-tight">
                {{ __('تعرّف على مسارك') }}
                <span class="text-shimmer-gold"> {{ __('قبل التقديم') }}</span>
            </h2>
            <p class="relative mx-auto mt-4 max-w-xl text-base leading-relaxed text-slate-300">
                {{ __('راجع الشروط العامة والخاصة بكل مسار والمؤسسات التعليمية المحددة له، ثم قدّم طلبك عبر المنصة الرسمية خلال فترة التقديم المعلنة.') }}
            </p>
            <div class="relative mt-9 flex flex-wrap items-center justify-center gap-4">
                <a href="{{ url('/') }}#matcher" class="group inline-flex items-center gap-2.5 rounded-full bg-gold-500 px-9 py-4 text-sm font-bold text-forest-950 transition-all duration-300 hover:-translate-y-1 hover:bg-gold-400 hover:shadow-[0_16px_44px_-10px_rgba(201,163,56,0.65)]">
                    {{ __('محرك التوجيه') }}
                    <x-lucide-arrow-up-left class="rtl-flip size-4 transition-transform duration-300 group-hover:-translate-x-1 group-hover:-translate-y-1" />
                </a>
                <a href="{{ url('/') }}#faq" class="inline-flex items-center gap-2.5 rounded-full border border-white/25 px-9 py-4 text-sm font-bold text-white transition-all duration-300 hover:border-gold-400 hover:text-gold-300">
                    {{ __('الأسئلة الشائعة') }}
                </a>
            </div>
        </div>
    </div>

    {{-- main footer --}}
    <div class="pattern-star-dark grain relative overflow-hidden bg-forest-950 pb-10 pt-44">
        <div class="relative mx-auto max-w-7xl px-5 md:px-8">
            <div class="grid grid-cols-1 gap-12 md:grid-cols-2 lg:grid-cols-12">
                <div class="lg:col-span-4">
                    <x-brand-logo light class="h-16" loading="lazy" />
                    <p class="mt-6 max-w-sm text-sm leading-loose text-slate-400">
                        {{ __('بوابة تعريفية وإرشادية لمسارات برنامج خادم الحرمين الشريفين للابتعاث الخارجي، تجمع الشروط والضوابط ورحلة المستفيد في مكان واحد.') }}
                    </p>
                    <ul class="mt-7 space-y-2.5 text-sm text-slate-400">
                        @foreach ($contactMethods as $c)
                            <li class="flex items-center gap-2.5">
                                @svg('lucide-'.$c->lucideIcon(), 'size-4 shrink-0 text-gold-500')
                                @if ($c->href)
                                    <a href="{{ $c->href }}" class="transition-colors hover:text-gold-300" @if (in_array($c->icon, ['phone', 'chat', 'globe'], true)) dir="ltr" @endif @if (str_starts_with($c->href, 'http')) target="_blank" rel="noopener noreferrer" @endif>{{ $c->value }}</a>
                                @else
                                    <span>{{ $c->value }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                    {{-- حسابات التواصل الاجتماعي — تُدار من «وسائل الاتصال» في اللوحة --}}
                    @if ($socialLinks->isNotEmpty())
                        <ul class="mt-6 flex flex-wrap gap-2.5" aria-label="{{ __('حسابات التواصل الاجتماعي') }}">
                            @foreach ($socialLinks as $s)
                                <li>
                                    <a href="{{ $s->url }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $s->name }}" title="{{ $s->name }}"
                                       class="grid size-10 place-items-center rounded-xl border border-white/10 text-slate-300 transition-all duration-300 hover:-translate-y-0.5 hover:border-gold-400/60 hover:text-gold-300">
                                        <x-social-icon :platform="$s->platform" class="size-4" />
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <div class="lg:col-span-2">
                    <h3 class="text-sm font-bold tracking-wide text-gold-300">{{ __('المنصّة') }}</h3>
                    <ul class="mt-5 space-y-3">
                        @foreach (\App\Support\SiteNav::links() as $l)
                            <li><a href="{{ $l['url'] }}" class="text-sm text-slate-400 transition-colors hover:text-gold-300">{{ $l['label'] }}</a></li>
                        @endforeach
                    </ul>
                </div>

                <div class="lg:col-span-3">
                    <h3 class="text-sm font-bold tracking-wide text-gold-300">{{ __('مسارات الابتعاث') }}</h3>
                    <ul class="mt-5 space-y-3">
                        @foreach ($footerTracks as $t)
                            <li>
                                <a href="{{ $t->url() }}" class="group flex items-center gap-2 text-sm text-slate-400 transition-colors hover:text-gold-300">
                                    <span class="h-1 w-4 rounded-full bg-white/15 transition-all duration-300 group-hover:w-6 group-hover:bg-gold-400"></span>
                                    {{ $t->tr('name') }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="lg:col-span-3" id="newsletter">
                    <h3 class="text-sm font-bold tracking-wide text-gold-300">{{ __('النشرة البريدية') }}</h3>
                    <p class="mt-5 text-sm leading-relaxed text-slate-400">{{ __('تنبيهات بالتحديثات المنشورة على المنصة — مباشرة إلى بريدك.') }}</p>
                    @if (session('subscribed'))
                        <div class="mt-5 flex items-center gap-2.5 rounded-2xl border border-gold-500/40 bg-gold-500/10 px-5 py-4 text-sm font-bold text-gold-300" role="status">
                            <x-lucide-circle-check class="size-5" />
                            {{ __('تم الاشتراك بنجاح، أهلًا بك!') }}
                        </div>
                    @else
                        <form method="POST" action="{{ route('newsletter.subscribe') }}" class="mt-5 flex gap-2">
                            @csrf
                            <input type="email" name="email" required value="{{ old('email') }}" placeholder="{{ __('بريدك الإلكتروني') }}" aria-label="{{ __('بريدك الإلكتروني') }}"
                                   class="w-full rounded-full border border-white/15 bg-white/[0.06] py-3 pe-4 ps-5 text-sm text-white outline-none backdrop-blur transition-all placeholder:text-slate-500 focus:border-gold-400/70 focus:ring-4 focus:ring-gold-500/10">
                            <button type="submit" aria-label="{{ __('اشتراك') }}" class="grid size-12 shrink-0 place-items-center rounded-full bg-gold-500 text-forest-950 transition-all hover:bg-gold-400 hover:shadow-[0_10px_30px_-8px_rgba(201,163,56,0.6)]">
                                <x-lucide-send class="rtl-flip size-4.5 -scale-x-100" />
                            </button>
                        </form>
                        @error('email', 'newsletter')
                            <p class="mt-2 text-xs font-bold text-red-300">{{ $message }}</p>
                        @enderror
                    @endif
                </div>
            </div>

            {{-- مركز المعرفة ورحلة المبتعث والمساعدة والتواصل (ملاحظة الوكالة: تكون في ذيل الصفحة) --}}
            <div class="mt-14 grid gap-4 md:grid-cols-3" x-data>
                <a href="{{ url('/') }}#faq" class="group rounded-2xl border border-white/10 bg-white/[0.04] p-5 transition-all duration-300 hover:-translate-y-0.5 hover:border-gold-400/50 hover:bg-white/[0.07]">
                    <h3 class="flex items-center gap-2.5 text-sm font-bold text-gold-300">
                        <x-lucide-library-big class="size-5 shrink-0" /> {{ __('مركز المعرفة') }}
                    </h3>
                    <p class="mt-3 flex items-center gap-1.5 text-sm text-slate-300 transition-colors group-hover:text-white">
                        {{ __('الأسئلة الشائعة') }} <x-lucide-arrow-left class="rtl-flip size-3.5 text-gold-500" />
                    </p>
                </a>
                <a href="{{ url('/') }}#journey" class="group rounded-2xl border border-white/10 bg-white/[0.04] p-5 transition-all duration-300 hover:-translate-y-0.5 hover:border-gold-400/50 hover:bg-white/[0.07]">
                    <h3 class="flex items-center gap-2.5 text-sm font-bold text-gold-300">
                        <x-lucide-route class="size-5 shrink-0" /> {{ __('رحلة المبتعث') }}
                    </h3>
                    <p class="mt-3 flex items-center gap-1.5 text-sm leading-relaxed text-slate-300 transition-colors group-hover:text-white">
                        {{ __('ملخص للمراحل من التقديم حتى بدء الدراسة.') }} <x-lucide-arrow-left class="rtl-flip size-3.5 shrink-0 text-gold-500" />
                    </p>
                </a>
                <div class="rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                    <h3 class="flex items-center gap-2.5 text-sm font-bold text-gold-300">
                        <x-lucide-life-buoy class="size-5 shrink-0" /> {{ __('المساعدة والتواصل') }}
                    </h3>
                    <ul class="mt-3 flex flex-wrap gap-x-4 gap-y-2 text-sm text-slate-300">
                        <li><button type="button" @click="$dispatch('open-chat')" class="inline-flex items-center gap-1.5 transition-colors hover:text-gold-300"><x-lucide-bot class="size-4 text-gold-500" /> {{ __('المساعد الذكي') }}</button></li>
                        <li><a href="{{ route('contact.show') }}" class="inline-flex items-center gap-1.5 transition-colors hover:text-gold-300"><x-lucide-headset class="size-4 text-gold-500" /> {{ __('خدمات المستفيدين') }}</a></li>
                        <li><button type="button" @click="$dispatch('rate-service', { source: 'widget' })" class="inline-flex items-center gap-1.5 transition-colors hover:text-gold-300"><x-lucide-message-square-warning class="size-4 text-gold-500" /> {{ __('الإبلاغ عن الملاحظات') }}</button></li>
                    </ul>
                </div>
            </div>

            {{-- إخلاء مسؤولية المحتوى (§F): المنصة تعريفية، والمصدر المعتمد هو المرجع. --}}
            <div class="mt-4 rounded-2xl border border-white/10 bg-white/[0.04] px-6 py-4">
                <p class="text-xs leading-loose text-slate-400">
                    {{ __('المعلومات الواردة في هذه المنصة تعريفية وإرشادية، ويجب الرجوع إلى المصدر المعتمد المحدّث للتحقق من الشروط والضوابط قبل اتخاذ أي إجراء. لا تمثل المنصة جهة قبول أو قرار نهائي.') }}
                </p>
            </div>

            <div class="mt-6 flex flex-col items-center justify-between gap-4 border-t border-white/10 pt-7 md:flex-row">
                <p class="text-xs text-slate-500">{{ __('© 1447هـ — 2026م برنامج خادم الحرمين الشريفين للابتعاث الخارجي. جميع الحقوق محفوظة.') }}</p>
                <div class="flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-xs text-slate-500">
                    @foreach ($legalPages as $p)
                        <a href="{{ route('pages.show', $p->slug) }}" class="transition-colors hover:text-gold-300">{{ __($p->title_ar) }}</a>
                    @endforeach
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-1.5 transition-colors hover:text-gold-300">
                        <x-lucide-settings-2 class="size-3.5" />
                        {{ __('لوحة التحكم') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</footer>
