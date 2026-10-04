@extends('layouts.admin')
@section('page-title', 'برامج واعد')

@php
    $e = $editing;
    $statuses = config('kasp.waed_statuses');
    $types = config('kasp.waed_types');
    $categories = config('kasp.waed_condition_categories');
    $repeaters = [
        'languages' => old('languages', $e?->languages ?? []),
        'tests' => old('tests', $e?->tests ?? []),
        'conditions' => collect(old('conditions', $e?->conditions ?? []))->map(fn ($c) => [
            'category' => $c['category'] ?? 'general',
            'text' => $c['text'] ?? '',
            'required' => filter_var($c['required'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ])->values()->all(),
    ];
@endphp

@section('admin')
<div class="grid gap-8 xl:grid-cols-[520px_1fr]">
    <form method="POST" action="{{ $e ? route('admin.waed.update', $e) : route('admin.waed.store') }}"
          class="h-fit rounded-3xl border border-forest-800/12 bg-white p-5 shadow-sm"
          x-data="{ languages: @js($repeaters['languages']), tests: @js($repeaters['tests']), conditions: @js($repeaters['conditions']) }">
        @csrf
        @if ($e) @method('PUT') @endif
        <fieldset @disabled(auth()->user()->cannot('edit-content')) class="space-y-5">
            <h2 class="flex items-center gap-2 text-sm font-bold text-ink">
                @if ($e) <x-lucide-pencil class="size-4 text-gold-600" /> تعديل البرنامج
                @else <x-lucide-plus class="size-4 text-gold-600" /> إضافة برنامج جديد @endif
            </h2>

            {{-- بيانات البرنامج --}}
            <div class="grid grid-cols-2 gap-3">
                <div class="col-span-2">
                    <label for="name_ar" class="dash-label">اسم البرنامج</label>
                    <input id="name_ar" name="name_ar" value="{{ old('name_ar', $e?->name_ar) }}" class="dash-input" required>
                    <x-admin.error name="name_ar" />
                </div>
                <div class="col-span-2">
                    <label for="name_en" class="dash-label">الاسم الإنجليزي</label>
                    <input id="name_en" name="name_en" dir="ltr" value="{{ old('name_en', $e?->name_en) }}" class="dash-input text-left" required>
                    <x-admin.error name="name_en" />
                </div>
                <div>
                    <label for="company_ar" class="dash-label">الجهة الراعية</label>
                    <input id="company_ar" name="company_ar" value="{{ old('company_ar', $e?->company_ar) }}" class="dash-input" required>
                    <x-admin.error name="company_ar" />
                </div>
                <div>
                    <label for="institution_name" class="dash-label">المؤسسة التعليمية</label>
                    <input id="institution_name" name="institution_name" value="{{ old('institution_name', $e?->institution_name) }}" class="dash-input">
                </div>
                <div>
                    <label for="country_name" class="dash-label">الدولة</label>
                    <input id="country_name" name="country_name" value="{{ old('country_name', $e?->country_name) }}" class="dash-input">
                </div>
                <div>
                    <label for="city" class="dash-label">المدينة</label>
                    <input id="city" name="city" value="{{ old('city', $e?->city) }}" class="dash-input">
                </div>
                <div>
                    <label for="degree_id" class="dash-label">الدرجة العلمية</label>
                    <select id="degree_id" name="degree_id" class="dash-input appearance-none">
                        @foreach (config('kasp.degrees') as $key => $label)
                            <option value="{{ $key }}" @selected(old('degree_id', $e?->degree_id ?? 'master') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="program_type" class="dash-label">نوع البرنامج</label>
                    <select id="program_type" name="program_type" class="dash-input appearance-none">
                        @foreach ($types as $key => $label)
                            <option value="{{ $key }}" @selected(old('program_type', $e?->program_type ?? 'scholarship') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="sector" class="dash-label">القطاع</label>
                    <input id="sector" name="sector" list="sectors" value="{{ old('sector', $e?->sector) }}" class="dash-input" required>
                    <datalist id="sectors">
                        @foreach (config('kasp.waed_sectors') as $s)<option value="{{ $s }}"></option>@endforeach
                    </datalist>
                    <x-admin.error name="sector" />
                </div>
                <div>
                    <label for="duration" class="dash-label">المدة</label>
                    <input id="duration" name="duration" value="{{ old('duration', $e?->duration) }}" class="dash-input" placeholder="24 شهرًا">
                </div>
                <div class="col-span-2">
                    <label for="major_name" class="dash-label">التخصص المدروس</label>
                    <input id="major_name" name="major_name" value="{{ old('major_name', $e?->major_name) }}" class="dash-input">
                </div>
                <div class="col-span-2">
                    <label for="required_majors" class="dash-label">التخصصات المطلوبة للتقديم <span class="font-medium text-slate-400">(سطر لكل تخصص)</span></label>
                    <textarea id="required_majors" name="required_majors" rows="3" class="dash-input resize-y leading-relaxed">{{ old('required_majors', implode("\n", $e?->required_majors ?? [])) }}</textarea>
                </div>
                <div class="col-span-2">
                    <label for="description_ar" class="dash-label">وصف البرنامج</label>
                    <textarea id="description_ar" name="description_ar" rows="3" class="dash-input resize-y leading-relaxed">{{ old('description_ar', $e?->description_ar) }}</textarea>
                </div>
            </div>

            {{-- المعدل --}}
            <div class="rounded-2xl border border-forest-800/10 bg-sand/30 p-4">
                <p class="dash-label">متطلب المعدل <span class="font-medium text-slate-400">(اتركه فارغًا إن لم يُشترط)</span></p>
                <div class="grid grid-cols-3 gap-2">
                    <input name="gpa_min" dir="ltr" value="{{ old('gpa_min', $e?->gpa['min'] ?? '') }}" class="dash-input bg-white" placeholder="الحد الأدنى" aria-label="الحد الأدنى للمعدل">
                    <input name="gpa_scale" dir="ltr" value="{{ old('gpa_scale', $e?->gpa['scale'] ?? '5.0') }}" class="dash-input bg-white" placeholder="من" aria-label="مقياس المعدل">
                    <input name="gpa_notes" value="{{ old('gpa_notes', $e?->gpa['notes'] ?? '') }}" class="dash-input bg-white" placeholder="ملاحظة" aria-label="ملاحظة المعدل">
                </div>
            </div>

            {{-- اللغة --}}
            <div class="rounded-2xl border border-forest-800/10 bg-sand/30 p-4">
                <div class="mb-2 flex items-center justify-between">
                    <p class="dash-label !mb-0">متطلبات اللغة</p>
                    <button type="button" @click="languages.push({ test: '', score: '', notes: '' })" class="inline-flex items-center gap-1 text-xs font-bold text-gold-600 hover:text-gold-700"><x-lucide-plus class="size-3.5" /> إضافة</button>
                </div>
                <template x-for="(l, i) in languages" :key="i">
                    <div class="mb-2 grid grid-cols-[1fr_80px_1fr_auto] gap-2">
                        <input :name="`languages[${i}][test]`" x-model="l.test" dir="ltr" class="dash-input bg-white" placeholder="IELTS" aria-label="الاختبار">
                        <input :name="`languages[${i}][score]`" x-model="l.score" dir="ltr" class="dash-input bg-white" placeholder="6.5" aria-label="الدرجة">
                        <input :name="`languages[${i}][notes]`" x-model="l.notes" class="dash-input bg-white" placeholder="ملاحظة" aria-label="ملاحظة">
                        <button type="button" @click="languages.splice(i, 1)" class="grid size-10 place-items-center rounded-xl text-red-500 hover:bg-red-50" aria-label="حذف"><x-lucide-x class="size-4" /></button>
                    </div>
                </template>
            </div>

            {{-- الاختبارات --}}
            <div class="rounded-2xl border border-forest-800/10 bg-sand/30 p-4">
                <div class="mb-2 flex items-center justify-between">
                    <p class="dash-label !mb-0">الاختبارات <span class="font-medium text-slate-400">(GRE، GMAT…)</span></p>
                    <button type="button" @click="tests.push({ name: '', score: '', notes: '' })" class="inline-flex items-center gap-1 text-xs font-bold text-gold-600 hover:text-gold-700"><x-lucide-plus class="size-3.5" /> إضافة</button>
                </div>
                <template x-for="(t, i) in tests" :key="i">
                    <div class="mb-2 grid grid-cols-[1fr_80px_1fr_auto] gap-2">
                        <input :name="`tests[${i}][name]`" x-model="t.name" dir="ltr" class="dash-input bg-white" placeholder="GRE" aria-label="الاختبار">
                        <input :name="`tests[${i}][score]`" x-model="t.score" dir="ltr" class="dash-input bg-white" placeholder="310" aria-label="الدرجة">
                        <input :name="`tests[${i}][notes]`" x-model="t.notes" class="dash-input bg-white" placeholder="ملاحظة" aria-label="ملاحظة">
                        <button type="button" @click="tests.splice(i, 1)" class="grid size-10 place-items-center rounded-xl text-red-500 hover:bg-red-50" aria-label="حذف"><x-lucide-x class="size-4" /></button>
                    </div>
                </template>
            </div>

            {{-- الشروط --}}
            <div class="rounded-2xl border border-forest-800/10 bg-sand/30 p-4">
                <div class="mb-2 flex items-center justify-between">
                    <p class="dash-label !mb-0">الشروط والضوابط</p>
                    <button type="button" @click="conditions.push({ category: 'general', text: '', required: true })" class="inline-flex items-center gap-1 text-xs font-bold text-gold-600 hover:text-gold-700"><x-lucide-plus class="size-3.5" /> إضافة</button>
                </div>
                <template x-for="(c, i) in conditions" :key="i">
                    <div class="mb-2 grid grid-cols-[120px_1fr_auto_auto] items-center gap-2">
                        <select :name="`conditions[${i}][category]`" x-model="c.category" class="dash-input appearance-none bg-white" aria-label="نوع الشرط">
                            @foreach ($categories as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <input :name="`conditions[${i}][text]`" x-model="c.text" class="dash-input bg-white" placeholder="نص الشرط" aria-label="نص الشرط">
                        <label class="flex items-center gap-1 text-[11px] font-bold text-slate-500">
                            <input type="hidden" :name="`conditions[${i}][required]`" :value="c.required ? 1 : 0">
                            <input type="checkbox" x-model="c.required" class="size-4 accent-[#0f4632]"> إلزامي
                        </label>
                        <button type="button" @click="conditions.splice(i, 1)" class="grid size-10 place-items-center rounded-xl text-red-500 hover:bg-red-50" aria-label="حذف"><x-lucide-x class="size-4" /></button>
                    </div>
                </template>
            </div>

            {{-- التقديم --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="application_status" class="dash-label">حالة التقديم</label>
                    <select id="application_status" name="application_status" class="dash-input appearance-none">
                        @foreach ($statuses as $key => $s)
                            <option value="{{ $key }}" @selected(old('application_status', $e?->application_status ?? 'upcoming') === $key)>{{ $s['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="version" class="dash-label">الإصدار</label>
                    <input id="version" name="version" dir="ltr" value="{{ old('version', $e?->version ?? '2026-2027') }}" class="dash-input">
                </div>
                <div>
                    <label for="application_start" class="dash-label">بداية التقديم</label>
                    <input id="application_start" name="application_start" type="date" dir="ltr" value="{{ old('application_start', $e?->application_start?->format('Y-m-d')) }}" class="dash-input">
                </div>
                <div>
                    <label for="application_end" class="dash-label">نهاية التقديم</label>
                    <input id="application_end" name="application_end" type="date" dir="ltr" value="{{ old('application_end', $e?->application_end?->format('Y-m-d')) }}" class="dash-input">
                    <x-admin.error name="application_end" />
                </div>
                <div>
                    <label for="study_start_date" class="dash-label">بداية الدراسة</label>
                    <input id="study_start_date" name="study_start_date" type="date" dir="ltr" value="{{ old('study_start_date', $e?->study_start_date?->format('Y-m-d')) }}" class="dash-input">
                </div>
                <div>
                    <label for="website" class="dash-label">الصفحة الرسمية</label>
                    <input id="website" name="website" type="url" dir="ltr" value="{{ old('website', $e?->website) }}" class="dash-input text-left" placeholder="https://">
                    <x-admin.error name="website" />
                </div>
                <div class="col-span-2">
                    <label for="notes" class="dash-label">ملاحظات تظهر للزائر</label>
                    <textarea id="notes" name="notes" rows="2" class="dash-input resize-y leading-relaxed">{{ old('notes', $e?->notes) }}</textarea>
                </div>
            </div>

            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-gold-500/40 bg-gold-500/[0.06] px-4 py-3">
                <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $e?->is_featured)) class="size-4 accent-[#b08c2f]">
                <span class="flex items-center gap-1.5 text-[13px] font-bold text-gold-700"><x-lucide-sparkles class="size-4" /> برنامج مميز (يظهر أولًا)</span>
            </label>

            <x-admin.form-actions :editing="(bool) $e" create-label="إضافة البرنامج" :cancel="route('admin.waed.index')" />
        </fieldset>
    </form>

    <div class="space-y-3.5">
        <div class="flex flex-wrap items-start gap-2">
            <div class="flex-1"><x-admin.search-bar :q="$q" placeholder="ابحث في البرامج بالاسم أو الجهة أو القطاع…" /></div>
            @can('delete-content')
                <form method="POST" action="{{ route('admin.waed.reset') }}" onsubmit="return confirm('ستُستبدل البرامج الحالية بالبرامج الافتراضية. متابعة؟')">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-full border border-forest-800/15 px-4 py-2.5 text-xs font-bold text-slate-500 transition-all hover:bg-sand">
                        <x-lucide-rotate-ccw class="size-3.5" /> استعادة الافتراضي
                    </button>
                </form>
            @endcan
        </div>
        <h2 class="flex items-center gap-2 text-sm font-bold text-forest-800">
            <x-lucide-satellite class="size-4 text-gold-600" /> برامج واعد ({{ $items->count() }})
            <a href="{{ route('waed.index') }}" target="_blank" class="ms-auto inline-flex items-center gap-1 text-xs font-bold text-gold-600 hover:text-gold-700">صفحة واعد <x-lucide-external-link class="size-3" /></a>
        </h2>
        @forelse ($items as $p)
            <div @class([
                'flex flex-wrap items-center gap-3 rounded-2xl border bg-white p-4 shadow-sm transition-all hover:border-gold-500/40',
                'border-gold-500 ring-4 ring-gold-500/20' => $e?->id === $p->id,
                'border-forest-800/12' => $e?->id !== $p->id,
                'opacity-75' => $p->isPast(),
            ])>
                <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-forest-800 text-gold-300"><x-lucide-satellite class="size-4.5" /></span>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="text-sm font-bold text-ink">{{ $p->name_ar }}</p>
                        <span class="rounded-full border px-2.5 py-0.5 text-[10px] font-bold {{ $p->statusClass() }}">{{ $p->statusLabel() }}</span>
                        @if ($p->is_featured)<span class="rounded-full bg-gold-500/15 px-2 py-0.5 text-[10px] font-bold text-gold-700">مميز</span>@endif
                    </div>
                    <p class="mt-0.5 text-[12px] text-slate-400">{{ $p->company_ar }} • {{ $p->sector }} • {{ config("kasp.degrees.{$p->degree_id}") }}</p>
                </div>
                <div class="flex gap-1.5">
                    <a href="{{ route('waed.show', $p) }}" target="_blank" aria-label="عرض" title="عرض في المنصة" class="grid size-9 place-items-center rounded-full border border-forest-800/15 text-slate-500 transition-all hover:bg-sand"><x-lucide-external-link class="size-4" /></a>
                    <x-admin.edit-link :href="route('admin.waed.index', ['edit' => $p->id])" />
                    <x-admin.delete-button :action="route('admin.waed.destroy', $p)" />
                </div>
            </div>
        @empty
            <x-admin.empty icon="satellite" title="لا توجد برامج مطابقة" />
        @endforelse
    </div>
</div>
@endsection
