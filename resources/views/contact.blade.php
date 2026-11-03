@extends('layouts.page')

@section('title', __('اتصل بنا').' | '.__('برنامج خادم الحرمين الشريفين للابتعاث'))

@php
    $inputClass = 'w-full rounded-xl border border-forest-800/15 bg-sand/40 px-4 py-3 text-sm text-ink outline-none transition-all placeholder:text-slate-400 focus:border-forest-600 focus:bg-white focus:ring-4 focus:ring-forest-600/10';
@endphp

@section('page')
<div class="mx-auto max-w-6xl px-5 py-10 md:px-8">
    <header class="mb-8">
        <p class="flex items-center gap-2 text-xs font-bold text-gold-600"><x-lucide-headset class="size-4" /> {{ __('قنوات الدعم') }}</p>
        <h1 class="mt-2 text-3xl font-bold text-ink md:text-4xl">{{ __('اتصل بنا') }}</h1>
        <p class="mt-3 max-w-2xl text-sm leading-relaxed text-slate-500">{{ __('يسعد فريق البرنامج بالرد على استفساراتك. تواصل معنا عبر القنوات التالية، أو أرسل رسالتك من خلال النموذج وسيتواصل معك الموظف المسؤول.') }}</p>
    </header>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.5fr)]">
        {{-- معلومات التواصل --}}
        <aside class="space-y-4">
            <div class="rounded-3xl border border-forest-800/10 bg-white p-6 shadow-sm">
                <h2 class="flex items-center gap-2 text-base font-bold text-ink"><x-lucide-phone class="size-4.5 text-gold-600" /> {{ __('معلومات التواصل') }}</h2>
                <ul class="mt-4 space-y-2.5">
                    @foreach ($methods as $c)
                        <li>
                            <a href="{{ $c->href ?: '#' }}" @if (str_starts_with($c->href, 'http')) target="_blank" rel="noopener noreferrer" @endif
                               class="group flex items-center gap-3 rounded-2xl bg-sand/60 px-4 py-3 transition-all hover:bg-forest-800">
                                <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-forest-800 text-gold-300 transition-colors group-hover:bg-gold-500 group-hover:text-forest-950">@svg('lucide-'.$c->lucideIcon(), 'size-4.5')</span>
                                <span class="min-w-0">
                                    <span class="block text-[12px] font-bold text-slate-500 group-hover:text-white/70">{{ __($c->label) }}</span>
                                    <span class="block truncate font-plex text-sm font-bold text-forest-800 group-hover:text-white" dir="ltr">{{ $c->value }}</span>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
                @if ($socials->isNotEmpty())
                    <div class="mt-5 flex flex-wrap gap-2.5 border-t border-forest-800/8 pt-5" aria-label="{{ __('حسابات التواصل الاجتماعي') }}">
                        @foreach ($socials as $s)
                            <a href="{{ $s->url }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $s->name }}" title="{{ $s->name }}"
                               class="grid size-10 place-items-center rounded-xl border border-forest-800/15 text-forest-800 transition-all hover:-translate-y-0.5 hover:border-forest-800 hover:bg-forest-800 hover:text-gold-300">
                                <x-social-icon :platform="$s->platform" class="size-4" />
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
            <p class="rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-[12px] leading-relaxed text-blue-700">
                <x-lucide-info class="me-1 inline size-3.5 align-[-2px]" />
                {{ __('للاستفسارات العاجلة يُرجى التواصل مع مركز الاتصال الموحد. لا تُرسل بيانات حساسة مثل كلمات المرور عبر النموذج.') }}
            </p>
        </aside>

        {{-- نموذج التواصل --}}
        <section class="rounded-3xl border border-forest-800/10 bg-white p-6 shadow-sm md:p-8" aria-labelledby="form-title">
            @if (session('sent'))
                <div class="py-12 text-center" role="status">
                    <x-lucide-circle-check class="mx-auto size-14 text-emerald-500" />
                    <h2 class="mt-4 text-xl font-bold text-forest-800">{{ __('تم إرسال رسالتك بنجاح') }}</h2>
                    <p class="mt-2 text-sm text-slate-500">{{ __('شكرًا لتواصلك معنا، سيطّلع الموظف المسؤول على رسالتك ويتواصل معك في أقرب وقت.') }}</p>
                    <div class="mt-7 flex flex-wrap justify-center gap-3">
                        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 rounded-full bg-forest-800 px-6 py-3 text-sm font-bold text-gold-300 transition-all hover:bg-forest-700">
                            <x-lucide-house class="size-4" /> {{ __('العودة إلى الرئيسية') }}
                        </a>
                        <a href="{{ route('contact.show') }}" class="inline-flex items-center gap-2 rounded-full border border-forest-800/20 px-6 py-3 text-sm font-bold text-forest-800 transition-all hover:bg-sand">
                            {{ __('إرسال رسالة أخرى') }}
                        </a>
                    </div>
                </div>
            @else
                <h2 id="form-title" class="flex items-center gap-2 text-base font-bold text-ink"><x-lucide-mail-plus class="size-4.5 text-gold-600" /> {{ __('نموذج تواصل معنا') }}</h2>
                @if ($fromAssistant)
                    <p class="mt-2 rounded-xl bg-gold-500/10 px-4 py-2.5 text-[12px] font-bold text-gold-700">{{ __('أرسل سؤالك للموظف المسؤول وسيتم الرد عليك عبر بيانات التواصل التي تدخلها.') }}</p>
                @endif
                @if ($errors->any())
                    <div role="alert" class="mt-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-[13px] text-red-700">
                        <p class="font-bold">{{ __('يرجى مراجعة الحقول التالية:') }}</p>
                        <ul class="mt-1.5 list-disc space-y-0.5 ps-5 text-[12px]">@foreach ($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('contact.store') }}" class="mt-5 grid gap-4 sm:grid-cols-2" novalidate>
                    @csrf
                    <input type="hidden" name="source" value="{{ $fromAssistant ? 'assistant' : 'page' }}">
                    {{-- حقل مخفي لاصطياد الرسائل الآلية --}}
                    <div class="hidden" aria-hidden="true"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>

                    <div class="sm:col-span-2">
                        <label for="name" class="mb-1.5 block text-[12px] font-bold text-forest-800">{{ __('الاسم') }} <span class="text-red-500">*</span></label>
                        <input id="name" name="name" value="{{ old('name') }}" autocomplete="name" required maxlength="120" class="{{ $inputClass }}" @error('name') aria-invalid="true" @enderror>
                    </div>
                    <div>
                        <label for="phone" class="mb-1.5 block text-[12px] font-bold text-forest-800">{{ __('رقم الهاتف') }}</label>
                        <input id="phone" name="phone" type="tel" dir="ltr" value="{{ old('phone') }}" autocomplete="tel" maxlength="30" placeholder="05xxxxxxxx" class="{{ $inputClass }} rtl:text-right" @error('phone') aria-invalid="true" @enderror>
                    </div>
                    <div>
                        <label for="email" class="mb-1.5 block text-[12px] font-bold text-forest-800">{{ __('البريد الإلكتروني') }}</label>
                        <input id="email" name="email" type="email" dir="ltr" value="{{ old('email') }}" autocomplete="email" maxlength="160" placeholder="name@example.com" class="{{ $inputClass }} rtl:text-right" @error('email') aria-invalid="true" @enderror>
                    </div>
                    <p class="-mt-2 text-[11px] text-slate-400 sm:col-span-2">{{ __('أدخل رقم الهاتف أو البريد الإلكتروني على الأقل ليتمكن الموظف من الرد عليك.') }}</p>
                    <div class="sm:col-span-2">
                        <label for="subject" class="mb-1.5 block text-[12px] font-bold text-forest-800">{{ __('عنوان الموضوع') }} <span class="text-red-500">*</span></label>
                        <input id="subject" name="subject" value="{{ old('subject') }}" required maxlength="200" class="{{ $inputClass }}" @error('subject') aria-invalid="true" @enderror>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="message" class="mb-1.5 block text-[12px] font-bold text-forest-800">{{ __('الرسالة') }} <span class="text-red-500">*</span></label>
                        <textarea id="message" name="message" rows="6" required maxlength="5000" class="{{ $inputClass }} resize-y leading-relaxed" @error('message') aria-invalid="true" @enderror>{{ old('message') }}</textarea>
                    </div>
                    <div class="sm:col-span-2">
                        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-gold-500 px-8 py-3.5 text-sm font-bold text-forest-950 transition-all hover:bg-gold-400 hover:shadow-[0_14px_36px_-12px_rgba(201,163,56,0.6)] sm:w-auto">
                            <x-lucide-send-horizontal class="rtl-flip size-4" /> {{ __('إرسال الرسالة') }}
                        </button>
                    </div>
                </form>
            @endif
        </section>
    </div>
</div>
@endsection
