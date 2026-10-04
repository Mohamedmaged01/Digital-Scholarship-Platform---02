@extends('layouts.page')

@section('title', "{$program->name_ar} | مسار واعد")

@php
    $p = $program;
    $past = $p->isPast();
    $categories = config('kasp.waed_condition_categories');
    $rows = [
        'الجهة / الشركة الراعية' => $p->company_ar,
        'المؤسسة التعليمية' => $p->institution_name,
        'الدولة والمدينة' => "{$p->city}، {$p->country_name}",
        'الدرجة العلمية' => config("kasp.degrees.{$p->degree_id}", $p->degree_id),
        'نوع البرنامج' => config("kasp.waed_types.{$p->program_type}", $p->program_type),
        'مدة البرنامج' => $p->duration,
        'التخصص المدروس' => $p->major_name,
        'القطاع' => $p->sector,
        'إصدار البرنامج' => $p->version,
    ];
@endphp

@section('page')
<div class="mx-auto max-w-4xl px-5 py-10 md:px-8">
    <nav class="mb-6 flex flex-wrap items-center gap-1.5 text-[11px] font-bold text-slate-400" aria-label="مسار التنقل">
        <a href="{{ route('home') }}" class="hover:text-forest-700">الرئيسية</a>
        <x-lucide-chevron-left class="size-3" />
        <a href="{{ route('waed.index') }}" class="hover:text-forest-700">مسار واعد</a>
        <x-lucide-chevron-left class="size-3" />
        <span class="text-forest-700" aria-current="page">{{ $p->name_ar }}</span>
    </nav>

    <article class="overflow-hidden rounded-[2rem] border border-forest-800/10 bg-white shadow-sm">
        <header @class(['pattern-star-dark relative px-7 py-8 md:px-10', 'bg-slate-800' => $past, 'bg-forest-950' => ! $past])>
            <span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-[11px] font-bold {{ $p->statusClass() }}">
                <x-lucide-clock class="size-3" />{{ $p->statusLabel() }}
            </span>
            <h1 class="mt-3 text-2xl font-bold text-white md:text-3xl">{{ $p->name_ar }}</h1>
            <p class="mt-1 text-right font-plex text-[13px] text-white/60" dir="ltr">{{ $p->name_en }}</p>
            @if ($p->description_ar)
                <p class="mt-4 max-w-2xl text-sm leading-loose text-slate-300">{{ $p->description_ar }}</p>
            @endif
        </header>

        <div class="divide-y divide-forest-800/8 px-7 pb-8 md:px-10">
            <section class="py-6">
                <h2 class="mb-4 flex items-center gap-2 text-sm font-bold text-slate-500"><x-lucide-building-2 class="size-4 text-gold-600" /> بيانات البرنامج</h2>
                <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3">
                    @foreach ($rows as $label => $value)
                        @continue(blank($value))
                        <div class="flex flex-col gap-0.5 rounded-xl border border-forest-800/8 bg-sand/40 px-4 py-3">
                            <dt class="text-[10px] font-bold text-slate-500">{{ $label }}</dt>
                            <dd class="text-[13px] font-bold text-ink">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>

            @if ($p->required_majors)
                <section class="py-6">
                    <h2 class="mb-4 flex items-center gap-2 text-sm font-bold text-slate-500"><x-lucide-graduation-cap class="size-4 text-gold-600" /> المؤهل والتخصصات المطلوبة</h2>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($p->required_majors as $m)
                            <span class="rounded-full border border-forest-700/20 bg-forest-50 px-3.5 py-1.5 text-[12px] font-bold text-forest-700">{{ $m }}</span>
                        @endforeach
                    </div>
                </section>
            @endif

            @if (! empty($p->gpa['min']))
                <section class="py-6">
                    <h2 class="mb-4 flex items-center gap-2 text-sm font-bold text-slate-500"><x-lucide-shield-check class="size-4 text-gold-600" /> متطلبات المعدل</h2>
                    <div class="rounded-2xl border border-forest-700/20 bg-forest-50 px-5 py-4">
                        <div class="flex items-baseline gap-2" dir="ltr">
                            <span class="font-plex text-3xl font-bold text-forest-800">{{ $p->gpa['min'] }}</span>
                            <span class="font-plex text-sm font-bold text-slate-500">/ {{ $p->gpa['scale'] }}</span>
                        </div>
                        @if (! empty($p->gpa['notes']))
                            <p class="mt-2 text-[12px] text-slate-500"><x-lucide-info class="me-1 inline size-3.5 align-[-2px]" />{{ $p->gpa['notes'] }}</p>
                        @endif
                    </div>
                </section>
            @endif

            @if ($p->languages)
                <section class="py-6">
                    <h2 class="mb-4 flex items-center gap-2 text-sm font-bold text-slate-500"><x-lucide-languages class="size-4 text-gold-600" /> متطلبات اللغة</h2>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        @foreach ($p->languages as $l)
                            <div class="rounded-2xl border border-forest-700/20 bg-forest-50 px-5 py-4">
                                <div class="flex items-baseline justify-end gap-2" dir="ltr">
                                    <span class="font-plex text-xl font-bold text-forest-800">{{ $l['test'] }}</span>
                                    <span class="font-plex text-2xl font-bold text-gold-600">{{ $l['score'] }}+</span>
                                </div>
                                @if (! empty($l['notes']))<p class="mt-1 text-[12px] text-slate-500">{{ $l['notes'] }}</p>@endif
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($p->tests)
                <section class="py-6">
                    <h2 class="mb-4 flex items-center gap-2 text-sm font-bold text-slate-500"><x-lucide-file-text class="size-4 text-gold-600" /> الاختبارات</h2>
                    <div class="space-y-2">
                        @foreach ($p->tests as $t)
                            <div class="flex flex-wrap items-center gap-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
                                <span class="font-plex text-base font-bold text-amber-700" dir="ltr">{{ $t['name'] }}</span>
                                <span class="font-plex text-lg font-bold text-amber-600" dir="ltr">{{ $t['score'] }}+</span>
                                @if (! empty($t['notes']))<span class="ms-auto text-[11px] text-amber-700">{{ $t['notes'] }}</span>@endif
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($p->conditions)
                <section class="py-6">
                    <h2 class="mb-4 flex items-center gap-2 text-sm font-bold text-slate-500"><x-lucide-shield-check class="size-4 text-gold-600" /> الشروط والضوابط</h2>
                    <ul class="space-y-2.5">
                        @foreach ($p->conditions as $c)
                            <li @class(['flex items-start gap-3 rounded-xl border p-3.5', 'border-forest-800/10 bg-white' => $c['required'], 'border-dashed border-slate-200 bg-slate-50/50' => ! $c['required']])>
                                <span @class(['mt-0.5 grid size-5 shrink-0 place-items-center rounded-full text-[9px] font-bold', 'bg-forest-800 text-white' => $c['required'], 'bg-slate-200 text-slate-500' => ! $c['required']])>{{ $c['required'] ? '●' : '○' }}</span>
                                <div class="min-w-0 flex-1">
                                    <span class="text-[10px] font-bold text-slate-400">{{ $categories[$c['category']] ?? $c['category'] }}</span>
                                    <p class="mt-0.5 text-sm font-semibold text-ink">{{ $c['text'] }}</p>
                                </div>
                                @unless ($c['required'])
                                    <span class="shrink-0 rounded-full bg-slate-100 px-2 py-0.5 text-[9px] font-bold text-slate-400">يُفضَّل</span>
                                @endunless
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <section class="py-6">
                <h2 class="mb-4 flex items-center gap-2 text-sm font-bold text-slate-500"><x-lucide-calendar-days class="size-4 text-gold-600" /> فترة التقديم وحالته</h2>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div @class(['rounded-2xl border p-4 text-center', 'border-slate-200 bg-slate-50' => $past, 'border-forest-700/20 bg-forest-50' => ! $past])>
                        <div class="text-[10px] font-bold text-slate-500">بداية التقديم</div>
                        <div class="mt-1.5 font-plex text-base font-bold text-ink" dir="ltr">{{ $p->application_start?->format('Y-m-d') ?? '—' }}</div>
                    </div>
                    <div @class(['rounded-2xl border p-4 text-center', 'border-red-200 bg-red-50' => $past, 'border-amber-200 bg-amber-50' => ! $past])>
                        <div @class(['text-[10px] font-bold', 'text-red-500' => $past, 'text-amber-700' => ! $past])>{{ $past ? 'انتهى التقديم' : 'آخر موعد' }}</div>
                        <div class="mt-1.5 font-plex text-base font-bold text-ink" dir="ltr">{{ $p->application_end?->format('Y-m-d') ?? 'يُعلن لاحقًا' }}</div>
                    </div>
                    <div class="rounded-2xl border border-forest-700/20 bg-forest-50 p-4 text-center">
                        <div class="text-[10px] font-bold text-slate-500">بداية الدراسة</div>
                        <div class="mt-1.5 font-plex text-base font-bold text-ink" dir="ltr">{{ $p->study_start_date?->format('Y-m-d') ?? 'يُعلن لاحقًا' }}</div>
                    </div>
                </div>
                @if ($past)
                    <p class="mt-4 flex items-start gap-2.5 rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm font-bold text-amber-700">
                        <x-lucide-circle-x class="mt-0.5 size-5 shrink-0 text-amber-500" />
                        انتهت فترة التقديم على هذا البرنامج. تابع الإعلانات الرسمية لمتابعة الدورات القادمة.
                    </p>
                @endif
                @if ($p->notes)
                    <p class="mt-4 flex items-start gap-2 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-[12px] text-blue-700">
                        <x-lucide-info class="mt-0.5 size-4 shrink-0" />{{ $p->notes }}
                    </p>
                @endif
                <x-last-updated :date="$p->last_verified_at ?? $p->updated_at" source="برنامج واعد" :version="$p->version" class="mt-4 w-fit" />
            </section>

            <div class="flex flex-wrap gap-3 pt-6">
                @if ($p->website)
                    <a href="{{ $p->website }}" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-2 rounded-full bg-forest-800 px-7 py-3 text-sm font-bold text-gold-300 hover:bg-forest-700 hover:shadow-lg">
                        <x-lucide-external-link class="size-4" /> الصفحة الرسمية
                    </a>
                @endif
                <a href="{{ route('waed.index') }}"
                   class="inline-flex items-center gap-2 rounded-full border border-forest-800/20 px-7 py-3 text-sm font-bold text-forest-800 hover:bg-forest-800 hover:text-gold-300">
                    <x-lucide-arrow-right class="size-4" /> العودة إلى برامج واعد
                </a>
            </div>
        </div>
    </article>
</div>
@endsection
