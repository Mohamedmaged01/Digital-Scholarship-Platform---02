@extends('layouts.admin')
@section('page-title', 'الإحصاءات والأرقام')

@php
    $e = $editing;
    $locations = config('kasp.statistic_locations');
@endphp

@section('admin')
<div class="grid gap-8 lg:grid-cols-[380px_1fr]">
    <form method="POST" action="{{ $e ? route('admin.statistics.update', $e) : route('admin.statistics.store') }}"
          class="h-fit rounded-3xl border border-forest-800/12 bg-white p-5 shadow-sm lg:sticky lg:top-24">
        @csrf
        @if ($e) @method('PUT') @endif
        <fieldset @disabled(auth()->user()->cannot('edit-content')) class="space-y-3.5">
            <h2 class="flex items-center gap-2 text-sm font-bold text-ink">
                @if ($e) <x-lucide-pencil class="size-4 text-gold-600" /> تعديل الرقم
                @else <x-lucide-plus class="size-4 text-gold-600" /> إضافة رقم موثّق @endif
            </h2>
            <div class="grid grid-cols-2 gap-3">
                <div class="col-span-2">
                    <label for="label_ar" class="dash-label">العنوان</label>
                    <input id="label_ar" name="label_ar" value="{{ old('label_ar', $e?->label_ar) }}" class="dash-input" placeholder="مثال: مسارات ابتعاث" required>
                    <x-admin.error name="label_ar" />
                </div>
                <div class="col-span-2">
                    <label for="label_en" class="dash-label">العنوان الإنجليزي</label>
                    <input id="label_en" name="label_en" dir="ltr" value="{{ old('label_en', $e?->label_en) }}" class="dash-input text-left">
                </div>
                <div>
                    <label for="value" class="dash-label">القيمة الرقمية</label>
                    <input id="value" name="value" type="number" min="0" dir="ltr" value="{{ old('value', $e?->value) }}" class="dash-input" required>
                    <x-admin.error name="value" />
                </div>
                <div>
                    <label for="suffix" class="dash-label">اللاحقة</label>
                    <input id="suffix" name="suffix" dir="ltr" value="{{ old('suffix', $e?->suffix) }}" class="dash-input" placeholder="+ أو K أو %">
                </div>
                <div class="col-span-2">
                    <label for="note_ar" class="dash-label">الملاحظة</label>
                    <input id="note_ar" name="note_ar" value="{{ old('note_ar', $e?->note_ar) }}" class="dash-input">
                </div>
                <div>
                    <label for="location" class="dash-label">الموقع في المنصة</label>
                    <select id="location" name="location" class="dash-input appearance-none">
                        @foreach ($locations as $key => $label)
                            <option value="{{ $key }}" @selected(old('location', $e?->location ?? 'general') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="sort" class="dash-label">الترتيب</label>
                    <input id="sort" name="sort" type="number" min="0" dir="ltr" value="{{ old('sort', $e?->sort ?? 0) }}" class="dash-input">
                </div>
                <div class="col-span-2">
                    <label for="source_id" class="dash-label">المصدر الموثّق</label>
                    <select id="source_id" name="source_id" class="dash-input appearance-none" required>
                        <option value="">— اختر المصدر —</option>
                        @foreach ($sources as $s)
                            <option value="{{ $s->id }}" @selected(old('source_id', $e?->source_id) === $s->id)>{{ $s->name_ar }}</option>
                        @endforeach
                    </select>
                    <x-admin.error name="source_id" />
                </div>
            </div>
            <x-admin.form-actions :editing="(bool) $e" create-label="إضافة الرقم" :cancel="route('admin.statistics.index')" />
        </fieldset>
    </form>

    <div class="space-y-6">
        <p class="rounded-2xl border border-blue-200 bg-blue-50 px-5 py-3.5 text-[13px] leading-relaxed text-blue-800">
            <x-lucide-info class="me-1.5 inline size-4 align-[-3px]" />
            لا يُعرض رقم دون مصدر رسمي محدّث. عند خلوّ أي موقع من الأرقام المُدارة، تعرض المنصة تلقائيًا أرقامًا هيكلية يحسبها النظام (عدد المسارات والمحطات والدرجات والمصادر).
        </p>

        @foreach ($locations as $loc => $label)
            <section>
                <h2 class="mb-3 flex items-center gap-2 text-sm font-bold text-forest-800"><x-lucide-list-ordered class="size-4 text-gold-600" /> {{ $label }}</h2>
                @forelse ($items[$loc] ?? [] as $s)
                    <div @class(['mb-2.5 flex items-center gap-3 rounded-2xl border bg-white p-4 shadow-sm', 'border-gold-500 ring-4 ring-gold-500/20' => $e?->id === $s->id, 'border-forest-800/12' => $e?->id !== $s->id])>
                        <span class="min-w-14 rounded-xl bg-forest-50 px-3 py-2 text-center font-plex text-sm font-bold text-forest-800" dir="ltr">{{ number_format($s->value) }}{{ $s->suffix }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-bold text-ink">{{ $s->label_ar }}</p>
                            <p class="mt-0.5 line-clamp-1 text-[11px] text-slate-400">{{ $s->note_ar ?: $s->label_en }}</p>
                        </div>
                        <x-admin.edit-link :href="route('admin.statistics.index', ['edit' => $s->id])" />
                        <x-admin.delete-button :action="route('admin.statistics.destroy', $s)" />
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-forest-800/20 bg-white/70 p-4">
                        <p class="text-xs font-bold text-slate-500">لا توجد أرقام مُدارة — يُعرض حاليًا:</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach ($fallback[$loc] as $f)
                                <span class="rounded-full bg-sand px-3 py-1 text-[11px] font-bold text-slate-600"><span class="font-plex" dir="ltr">{{ $f['value'] }}</span> {{ $f['label'] }}</span>
                            @endforeach
                        </div>
                    </div>
                @endforelse
            </section>
        @endforeach
    </div>
</div>
@endsection
