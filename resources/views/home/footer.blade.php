<footer class="relative">
    {{-- CTA banner --}}
    <div class="relative z-10 mx-auto -mb-24 max-w-6xl px-5 md:px-8">
        <div class="reveal pattern-star-dark grain relative overflow-hidden rounded-[2.5rem] border border-gold-500/30 bg-forest-800 px-8 py-14 text-center shadow-[0_50px_100px_-40px_rgba(8,39,29,0.8)] md:px-16">
            <div class="absolute -start-20 -top-20 size-64 rounded-full bg-gold-500/15 blur-[90px]"></div>
            <div class="absolute -bottom-24 -end-16 size-72 rounded-full bg-forest-500/30 blur-[100px]"></div>
            <x-star-emblem class="relative mx-auto size-12 text-gold-400" />
            <h2 class="relative mx-auto mt-6 max-w-2xl text-3xl font-bold leading-snug text-white md:text-5xl md:leading-tight">
                جاهزٌ لكتابة قصّتك
                <span class="text-shimmer-gold"> مع الابتعاث؟</span>
            </h2>
            <p class="relative mx-auto mt-4 max-w-xl text-base leading-relaxed text-slate-300">
                المقاعد محدودة والفرز الذكي قائم الآن. أنشئ ملفك اليوم وكن ضمن دفعة 2026م.
            </p>
            <div class="relative mt-9 flex flex-wrap items-center justify-center gap-4">
                <a href="#matcher" class="group inline-flex items-center gap-2.5 rounded-full bg-gold-500 px-9 py-4 text-sm font-bold text-forest-950 transition-all duration-300 hover:-translate-y-1 hover:bg-gold-400 hover:shadow-[0_16px_44px_-10px_rgba(201,163,56,0.65)]">
                    ابدأ المطابقة الذكية
                    <x-lucide-arrow-up-left class="size-4 transition-transform duration-300 group-hover:-translate-x-1 group-hover:-translate-y-1" />
                </a>
                <a href="#faq" class="inline-flex items-center gap-2.5 rounded-full border border-white/25 px-9 py-4 text-sm font-bold text-white transition-all duration-300 hover:border-gold-400 hover:text-gold-300">
                    الأسئلة الشائعة
                </a>
            </div>
        </div>
    </div>

    {{-- main footer --}}
    <div class="pattern-star-dark grain relative overflow-hidden bg-forest-950 pb-10 pt-44">
        <div class="relative mx-auto max-w-7xl px-5 md:px-8">
            <div class="grid grid-cols-1 gap-12 md:grid-cols-2 lg:grid-cols-12">
                <div class="lg:col-span-4">
                    <div class="flex items-center gap-3.5">
                        <span class="grid size-12 place-items-center rounded-2xl bg-forest-800 text-gold-400">
                            <x-star-emblem class="size-7" />
                        </span>
                        <span class="leading-tight">
                            <span class="block text-base font-bold text-white">برنامج خادم الحرمين الشريفين</span>
                            <span class="block text-xs font-medium text-slate-400">المنصّة الموحّدة للابتعاث الخارجي</span>
                        </span>
                    </div>
                    <p class="mt-6 max-w-sm text-sm leading-loose text-slate-400">
                        منذ عام 1426هـ ونحن نرسل أفضل العقول السعودية إلى أعرق جامعات
                        العالم، لتعود قياداتٍ تصنع مستقبل المملكة ضمن رؤية 2030.
                    </p>
                    <ul class="mt-7 space-y-2.5 text-sm text-slate-400">
                        <li class="flex items-center gap-2.5"><x-lucide-phone class="size-4 text-gold-500" /><span dir="ltr" class="font-plex">+966 920 001 122</span></li>
                        <li class="flex items-center gap-2.5"><x-lucide-mail class="size-4 text-gold-500" /><span dir="ltr" class="font-plex">care@kasp.gov.sa</span></li>
                        <li class="flex items-center gap-2.5"><x-lucide-map-pin class="size-4 text-gold-500" />الرياض، المملكة العربية السعودية</li>
                    </ul>
                </div>

                <div class="lg:col-span-2">
                    <h3 class="text-sm font-bold tracking-wide text-gold-300">المنصّة</h3>
                    <ul class="mt-5 space-y-3">
                        @foreach (config('kasp.nav') as $l)
                            <li><a href="{{ $l['href'] }}" class="text-sm text-slate-400 transition-colors hover:text-gold-300">{{ $l['label'] }}</a></li>
                        @endforeach
                    </ul>
                </div>

                <div class="lg:col-span-3">
                    <h3 class="text-sm font-bold tracking-wide text-gold-300">مسارات الابتعاث</h3>
                    <ul class="mt-5 space-y-3">
                        @foreach ($tracks as $t)
                            <li>
                                <a href="#tracks" class="group flex items-center gap-2 text-sm text-slate-400 transition-colors hover:text-gold-300">
                                    <span class="h-1 w-4 rounded-full bg-white/15 transition-all duration-300 group-hover:w-6 group-hover:bg-gold-400"></span>
                                    {{ $t->name }}
                                    <span class="text-xs text-white/30">— {{ $t->badge }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="lg:col-span-3" id="newsletter">
                    <h3 class="text-sm font-bold tracking-wide text-gold-300">النشرة البريدية</h3>
                    <p class="mt-5 text-sm leading-relaxed text-slate-400">مواعيد الفرز، الجامعات المنضمة حديثًا، وقصص المبتعثين — مباشرة إلى بريدك.</p>
                    @if (session('subscribed'))
                        <div class="mt-5 flex items-center gap-2.5 rounded-2xl border border-gold-500/40 bg-gold-500/10 px-5 py-4 text-sm font-bold text-gold-300" role="status">
                            <x-lucide-circle-check class="size-5" />
                            تم الاشتراك بنجاح، أهلًا بك!
                        </div>
                    @else
                        <form method="POST" action="{{ route('newsletter.subscribe') }}" class="mt-5 flex gap-2">
                            @csrf
                            <input type="email" name="email" required value="{{ old('email') }}" placeholder="بريدك الإلكتروني" aria-label="بريدك الإلكتروني"
                                   class="w-full rounded-full border border-white/15 bg-white/[0.06] py-3 pe-4 ps-5 text-sm text-white outline-none backdrop-blur transition-all placeholder:text-slate-500 focus:border-gold-400/70 focus:ring-4 focus:ring-gold-500/10">
                            <button type="submit" aria-label="اشتراك" class="grid size-12 shrink-0 place-items-center rounded-full bg-gold-500 text-forest-950 transition-all hover:bg-gold-400 hover:shadow-[0_10px_30px_-8px_rgba(201,163,56,0.6)]">
                                <x-lucide-send class="size-4.5 -scale-x-100" />
                            </button>
                        </form>
                        @error('email', 'newsletter')
                            <p class="mt-2 text-xs font-bold text-red-300">{{ $message }}</p>
                        @enderror
                    @endif
                </div>
            </div>

            <div class="mt-14 flex flex-col items-center justify-between gap-4 border-t border-white/10 pt-7 md:flex-row">
                <p class="text-xs text-slate-500">© 1447هـ — 2026م برنامج خادم الحرمين الشريفين للابتعاث الخارجي. جميع الحقوق محفوظة.</p>
                <div class="flex items-center gap-6 text-xs text-slate-500">
                    <a href="#home" class="transition-colors hover:text-gold-300">سياسة الخصوصية</a>
                    <a href="#home" class="transition-colors hover:text-gold-300">شروط الاستخدام</a>
                    <a href="#home" class="transition-colors hover:text-gold-300">الوصول الشامل</a>
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-1.5 transition-colors hover:text-gold-300">
                        <x-lucide-settings-2 class="size-3.5" />
                        لوحة التحكم
                    </a>
                </div>
            </div>
        </div>
    </div>
</footer>
