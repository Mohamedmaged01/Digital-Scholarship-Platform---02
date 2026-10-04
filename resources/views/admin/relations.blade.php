@extends('layouts.admin')
@section('page-title', 'إدارة العلاقات')

@php
    $majorsByField = $majors->groupBy('field_id');
    $fieldNames = $fields->pluck('name_ar', 'id');
    $majorNames = $majors->pluck('name_ar', 'id');
    $degreeNames = $degrees->pluck('name_ar', 'id');
    $selected = $pathMajors->groupBy('degree_id')->map(fn ($g) => $g->pluck('major_id')->all());
@endphp

@section('admin')
<div class="space-y-6">
    {{-- اختيار المسار --}}
    <div class="flex flex-wrap items-center gap-2">
        @foreach ($tracks as $t)
            <a href="{{ route('admin.relations.index', ['track' => $t->slug]) }}" @class([
                'rounded-full px-5 py-2.5 text-sm font-bold transition-all',
                'bg-forest-800 text-gold-300 shadow-lg shadow-forest-800/20' => $track?->id === $t->id,
                'border border-forest-800/15 bg-white text-slate-600 hover:border-forest-700/50' => $track?->id !== $t->id,
            ])>{{ $t->name }}</a>
        @endforeach
        <form method="POST" action="{{ route('admin.relations.reset') }}" class="ms-auto" onsubmit="return confirm('ستُستبدل كل العلاقات (الدرجات والتخصصات والجامعات والقيود) بالقيم الافتراضية لكل المسارات. متابعة؟')">
            @csrf
            <button type="submit" class="inline-flex items-center gap-1.5 rounded-full border border-forest-800/15 px-4 py-2.5 text-xs font-bold text-slate-500 transition-all hover:bg-sand">
                <x-lucide-rotate-ccw class="size-3.5" /> استعادة الافتراضي
            </button>
        </form>
    </div>

    <p class="rounded-2xl border border-amber-300 bg-amber-50 px-5 py-3.5 text-[13px] font-semibold leading-relaxed text-amber-800">
        <x-lucide-shield-alert class="me-1.5 inline size-4 align-[-3px]" />
        لا تخمين: أي ربط بين مسار وتخصص وجامعة يجب أن يستند إلى الدليل الاسترشادي المعتمد. الروابط الجديدة تُسجَّل «بحاجة إلى تحقق» حتى تُعتمد.
        مسار واعد يُدار من «برامج واعد» لأنه يعمل بنظام البرامج المستقلة.
    </p>

    @if ($track)
        {{-- ① الدرجات والتخصصات --}}
        <form method="POST" action="{{ route('admin.relations.majors', $track) }}" class="rounded-3xl border border-forest-800/12 bg-white p-6 shadow-sm"
              x-data="{ degrees: @js($linkedDegrees) }">
            @csrf @method('PUT')
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="flex items-center gap-2 text-base font-bold text-ink">
                    <x-lucide-graduation-cap class="size-5 text-gold-600" />
                    ① الدرجات العلمية والتخصصات — {{ $track->name }}
                </h2>
                <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-forest-800 px-5 py-2.5 text-xs font-bold text-gold-300 transition-all hover:bg-forest-700">
                    <x-lucide-save class="size-4" /> حفظ الدرجات والتخصصات
                </button>
            </div>

            <div class="mt-5 flex flex-wrap gap-2" role="group" aria-label="الدرجات العلمية">
                @foreach ($degrees as $d)
                    <label class="cursor-pointer">
                        <input type="checkbox" name="degrees[]" value="{{ $d->id }}" x-model="degrees" class="peer sr-only" @checked(in_array($d->id, $linkedDegrees, true))>
                        <span class="block rounded-full border border-forest-800/15 px-4 py-2 text-xs font-bold text-slate-500 transition-all peer-checked:border-forest-800 peer-checked:bg-forest-800 peer-checked:text-gold-300 peer-focus-visible:ring-2 peer-focus-visible:ring-gold-500">{{ $d->name_ar }}</span>
                    </label>
                @endforeach
            </div>

            <div class="mt-5 space-y-4">
                @foreach ($degrees as $d)
                    <details x-show="degrees.includes(@js($d->id))" class="group rounded-2xl border border-forest-800/10 bg-sand/30" @if (! empty($selected[$d->id])) open @endif>
                        <summary class="flex cursor-pointer items-center justify-between gap-3 px-4 py-3 text-sm font-bold text-gold-700">
                            <span>تخصصات {{ $d->name_ar }} <span class="font-plex text-xs text-slate-400">({{ count($selected[$d->id] ?? []) }})</span></span>
                            <x-lucide-chevron-down class="size-4 text-slate-400 transition-transform group-open:rotate-180" />
                        </summary>
                        <div class="space-y-3 border-t border-forest-800/10 p-4">
                            @foreach ($majorsByField as $fieldId => $items)
                                <div>
                                    <p class="mb-2 text-[11px] font-bold text-slate-500">{{ $fieldNames[$fieldId] ?? 'أخرى' }}</p>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach ($items as $m)
                                            <label class="cursor-pointer">
                                                <input type="checkbox" name="majors[{{ $d->id }}][]" value="{{ $m->id }}" class="peer sr-only" @checked(in_array($m->id, $selected[$d->id] ?? [], true))>
                                                <span class="block rounded-lg border border-forest-800/10 bg-white px-2.5 py-1.5 text-[11px] font-medium text-slate-500 transition-all peer-checked:border-forest-700 peer-checked:bg-forest-700 peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-gold-500">{{ $m->name_ar }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </details>
                @endforeach
            </div>
            <p class="mt-4 text-[11px] text-slate-400">إزالة تخصص أو درجة تزيل روابط جامعاتها في هذا المسار.</p>
        </form>

        {{-- ② الجامعات --}}
        <form method="POST" action="{{ route('admin.relations.institutions', $track) }}" class="rounded-3xl border border-forest-800/12 bg-white p-6 shadow-sm">
            @csrf @method('PUT')
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="flex items-center gap-2 text-base font-bold text-ink">
                    <x-lucide-university class="size-5 text-gold-600" />
                    ② الجامعات المتاحة لكل تخصص
                </h2>
                <div class="flex flex-wrap items-center gap-2">
                    <label for="link-status" class="text-[11px] font-bold text-slate-500">حالة الروابط الجديدة</label>
                    <select id="link-status" name="status" class="dash-input w-auto appearance-none py-2 text-xs">
                        <option value="needs_verification">بحاجة إلى تحقق</option>
                        <option value="verified">موثّق من الدليل</option>
                    </select>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-forest-800 px-5 py-2.5 text-xs font-bold text-gold-300 transition-all hover:bg-forest-700">
                        <x-lucide-save class="size-4" /> حفظ روابط الجامعات
                    </button>
                </div>
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-2 text-[11px] font-bold">
                <span class="rounded-full bg-emerald-50 px-3 py-1 text-emerald-700">موثّق: {{ $statuses['verified'] ?? 0 }}</span>
                <span class="rounded-full bg-amber-50 px-3 py-1 text-amber-700">بحاجة إلى تحقق: {{ $statuses['needs_verification'] ?? 0 }}</span>
            </div>

            <div class="mt-5 space-y-3">
                @forelse ($pathMajors->sortBy(fn ($pm) => [$pm->degree_id, $majorNames[$pm->major_id] ?? '']) as $pm)
                    @php
                        $key = "{$pm->degree_id}|{$pm->major_id}";
                        $linked = $links[$key] ?? [];
                    @endphp
                    <details class="group rounded-2xl border border-forest-800/10 bg-sand/20">
                        <summary class="flex cursor-pointer items-center justify-between gap-3 px-4 py-3">
                            <span class="text-[13px] font-bold text-ink">
                                {{ $majorNames[$pm->major_id] ?? $pm->major_id }}
                                <span class="mx-1.5 text-slate-300">|</span>
                                <span class="text-gold-700">{{ $degreeNames[$pm->degree_id] ?? $pm->degree_id }}</span>
                            </span>
                            <span class="flex items-center gap-2">
                                <span @class(['rounded-full px-2.5 py-0.5 font-plex text-[10px] font-bold', 'bg-forest-800 text-gold-300' => $linked, 'bg-red-50 text-red-500' => ! $linked])>{{ count($linked) }}</span>
                                <x-lucide-chevron-down class="size-4 text-slate-400 transition-transform group-open:rotate-180" />
                            </span>
                        </summary>
                        <div class="grid grid-cols-2 gap-1.5 border-t border-forest-800/10 p-4 md:grid-cols-3 xl:grid-cols-4">
                            @foreach ($universities as $u)
                                <label class="cursor-pointer">
                                    <input type="checkbox" name="links[{{ $key }}][]" value="{{ $u->id }}" class="peer sr-only" @checked(in_array($u->id, $linked, true))>
                                    <span class="block rounded-lg border border-forest-800/10 bg-white px-2.5 py-1.5 text-start text-[11px] font-bold leading-tight text-slate-500 transition-all peer-checked:border-emerald-600 peer-checked:bg-emerald-600 peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-gold-500">{{ $u->name_ar }}</span>
                                </label>
                            @endforeach
                        </div>
                    </details>
                @empty
                    <x-admin.empty icon="link-2" title="لا توجد تخصصات مرتبطة بهذا المسار" desc="اختر الدرجات والتخصصات في الخطوة ① ثم احفظ." />
                @endforelse
            </div>
        </form>

        {{-- اعتماد جماعي --}}
        <div class="flex flex-wrap items-center gap-3 rounded-3xl border border-forest-800/12 bg-white p-5 shadow-sm">
            <x-lucide-badge-check class="size-5 text-gold-600" />
            <p class="flex-1 text-sm font-bold text-ink">اعتماد كل روابط {{ $track->name }} بعد مطابقتها مع الدليل الاسترشادي</p>
            @foreach (['verified' => 'اعتماد الكل كموثّق', 'needs_verification' => 'إعادة الكل إلى «بحاجة إلى تحقق»'] as $status => $label)
                <form method="POST" action="{{ route('admin.relations.verify', $track) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="{{ $status }}">
                    <button type="submit" @class([
                        'rounded-full px-4 py-2 text-xs font-bold transition-all',
                        'bg-emerald-600 text-white hover:bg-emerald-700' => $status === 'verified',
                        'border border-forest-800/15 text-slate-500 hover:bg-sand' => $status !== 'verified',
                    ])>{{ $label }}</button>
                </form>
            @endforeach
        </div>
    @endif

    {{-- إضافة تخصص --}}
    <form method="POST" action="{{ route('admin.relations.majors.store') }}" class="rounded-3xl border border-forest-800/12 bg-white p-6 shadow-sm">
        @csrf
        <h2 class="flex items-center gap-2 text-base font-bold text-ink"><x-lucide-plus class="size-5 text-gold-600" /> إضافة تخصص إلى القائمة المرجعية</h2>
        <div class="mt-4 grid gap-3 md:grid-cols-[1fr_1fr_1fr_auto] md:items-end">
            <div>
                <label for="major-ar" class="dash-label">اسم التخصص</label>
                <input id="major-ar" name="name_ar" value="{{ old('name_ar') }}" class="dash-input" required>
                <x-admin.error name="name_ar" />
            </div>
            <div>
                <label for="major-en" class="dash-label">الاسم الإنجليزي</label>
                <input id="major-en" name="name_en" dir="ltr" value="{{ old('name_en') }}" class="dash-input text-left">
            </div>
            <div>
                <label for="major-field" class="dash-label">المجال المعرفي</label>
                <select id="major-field" name="field_id" class="dash-input appearance-none">
                    @foreach ($fields as $f)
                        <option value="{{ $f->id }}" @selected(old('field_id') === $f->id)>{{ $f->name_ar }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-full bg-forest-800 px-6 py-3 text-xs font-bold text-gold-300 transition-all hover:bg-forest-700">
                <x-lucide-plus class="size-4" /> إضافة
            </button>
        </div>
    </form>
</div>
@endsection
