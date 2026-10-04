@extends('layouts.admin')
@section('page-title', 'الأدلة الاسترشادية')

@php
    $e = $editing;
    $types = config('kasp.guide_types');
    $statuses = config('kasp.guide_statuses');
@endphp

@section('admin')
<div class="grid gap-8 lg:grid-cols-[400px_1fr]">
    <form method="POST" action="{{ $e ? route('admin.guides.update', $e) : route('admin.guides.store') }}"
          class="h-fit rounded-3xl border border-forest-800/12 bg-white p-5 shadow-sm lg:sticky lg:top-24">
        @csrf
        @if ($e) @method('PUT') @endif
        <fieldset @disabled(auth()->user()->cannot('edit-content')) class="space-y-3.5">
            <h2 class="flex items-center gap-2 text-sm font-bold text-ink">
                @if ($e) <x-lucide-pencil class="size-4 text-gold-600" /> تعديل الدليل
                @else <x-lucide-plus class="size-4 text-gold-600" /> إضافة دليل استرشادي @endif
            </h2>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="path_id" class="dash-label">المسار</label>
                    <select id="path_id" name="path_id" class="dash-input appearance-none" required>
                        @foreach ($tracks as $t)
                            <option value="{{ $t->id }}" @selected((int) old('path_id', $e?->path_id ?? $preselectTrack) === $t->id)>{{ $t->name }}</option>
                        @endforeach
                    </select>
                    <x-admin.error name="path_id" />
                </div>
                <div>
                    <label for="guide_type" class="dash-label">نوع الدليل</label>
                    <select id="guide_type" name="guide_type" class="dash-input appearance-none">
                        @foreach ($types as $key => $label)
                            <option value="{{ $key }}" @selected(old('guide_type', $e?->guide_type ?? 'link') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-2">
                    <label for="title_ar" class="dash-label">عنوان الدليل</label>
                    <input id="title_ar" name="title_ar" value="{{ old('title_ar', $e?->title_ar) }}" class="dash-input" placeholder="الدليل الاسترشادي — مسار الروّاد 2026–2027" required>
                    <x-admin.error name="title_ar" />
                </div>
                <div class="col-span-2">
                    <label for="title_en" class="dash-label">العنوان الإنجليزي</label>
                    <input id="title_en" name="title_en" dir="ltr" value="{{ old('title_en', $e?->title_en) }}" class="dash-input text-left">
                </div>
                <div class="col-span-2">
                    <label for="url" class="dash-label">رابط الدليل</label>
                    <input id="url" name="url" dir="ltr" type="url" value="{{ old('url', $e?->url) }}" class="dash-input text-left" placeholder="https://…">
                    <x-admin.error name="url" />
                </div>
                <div class="col-span-2">
                    <label for="file_id" class="dash-label">أو ملف من مركز الملفات <span class="font-medium text-slate-400">(يُقدَّم على الرابط)</span></label>
                    <select id="file_id" name="file_id" class="dash-input appearance-none">
                        <option value="">— بلا ملف —</option>
                        @foreach ($files as $f)
                            <option value="{{ $f->id }}" @selected((int) old('file_id', $e?->file_id) === $f->id)>{{ $f->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="version" class="dash-label">الإصدار</label>
                    <input id="version" name="version" dir="ltr" value="{{ old('version', $e?->version ?? '2026-2027') }}" class="dash-input">
                </div>
                <div>
                    <label for="status" class="dash-label">الحالة</label>
                    <select id="status" name="status" class="dash-input appearance-none">
                        @foreach ($statuses as $key => $label)
                            <option value="{{ $key }}" @selected(old('status', $e?->status ?? 'active') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="publication_date" class="dash-label">تاريخ النشر</label>
                    <input id="publication_date" name="publication_date" type="date" dir="ltr" value="{{ old('publication_date', $e?->publication_date?->format('Y-m-d')) }}" class="dash-input">
                </div>
                <div>
                    <label for="effective_date" class="dash-label">تاريخ السريان</label>
                    <input id="effective_date" name="effective_date" type="date" dir="ltr" value="{{ old('effective_date', $e?->effective_date?->format('Y-m-d')) }}" class="dash-input">
                </div>
                <div class="col-span-2">
                    <label for="source_id" class="dash-label">المصدر</label>
                    <select id="source_id" name="source_id" class="dash-input appearance-none">
                        <option value="">— غير محدد —</option>
                        @foreach ($sources as $s)
                            <option value="{{ $s->id }}" @selected(old('source_id', $e?->source_id) === $s->id)>{{ $s->name_ar }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-2">
                    <label for="notes" class="dash-label">ملاحظات تظهر للزائر</label>
                    <textarea id="notes" name="notes" rows="2" class="dash-input resize-y leading-relaxed">{{ old('notes', $e?->notes) }}</textarea>
                </div>
            </div>

            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-forest-800/12 bg-sand/40 px-4 py-3">
                <input type="checkbox" name="is_current" value="1" @checked(old('is_current', $e?->is_current ?? true)) class="size-4 accent-[#0f4632]">
                <span class="text-[13px] font-bold text-ink">الإصدار الحالي للمسار <span class="font-medium text-slate-400">(يحوّل الإصدار السابق إلى أرشيف)</span></span>
            </label>

            <x-admin.form-actions :editing="(bool) $e" create-label="إضافة الدليل" :cancel="route('admin.guides.index')" />
        </fieldset>
    </form>

    <div class="space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="flex items-center gap-2 text-sm font-bold text-forest-800">
                <x-lucide-file-text class="size-4 text-gold-600" /> الأدلة بحسب المسار
            </h2>
            @can('delete-content')
                <form method="POST" action="{{ route('admin.guides.reset') }}" onsubmit="return confirm('ستُستبدل الأدلة الحالية بالأدلة الافتراضية. متابعة؟')">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-full border border-forest-800/15 px-4 py-2.5 text-xs font-bold text-slate-500 transition-all hover:bg-sand">
                        <x-lucide-rotate-ccw class="size-3.5" /> استعادة الافتراضي
                    </button>
                </form>
            @endcan
        </div>

        @foreach ($tracks as $t)
            <div class="rounded-3xl border border-forest-800/12 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="text-base font-bold text-ink">{{ $t->name }}</h3>
                    <a href="{{ $t->slug === 'waed' ? route('waed.index') : route('tracks.show', $t) }}" target="_blank" class="inline-flex items-center gap-1 text-xs font-bold text-gold-600 hover:text-gold-700">
                        صفحة المسار <x-lucide-external-link class="size-3" />
                    </a>
                </div>
                <div class="mt-3 space-y-2">
                    @forelse ($guides[$t->id] ?? [] as $g)
                        <div @class(['flex flex-wrap items-center gap-3 rounded-2xl border p-3.5', 'border-gold-500 ring-4 ring-gold-500/20' => $e?->id === $g->id, 'border-forest-800/10' => $e?->id !== $g->id])>
                            <x-lucide-file-text @class(['size-5 shrink-0', 'text-gold-600' => $g->is_current, 'text-slate-300' => ! $g->is_current]) />
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-sm font-bold text-ink">{{ $g->title_ar }}</p>
                                    @if ($g->is_current)
                                        <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700">حالي</span>
                                    @endif
                                    <span class="rounded-full bg-sand px-2 py-0.5 text-[10px] font-bold text-slate-500">{{ $statuses[$g->status] ?? $g->status }}</span>
                                </div>
                                <p class="mt-0.5 truncate text-[11px] text-slate-400" dir="ltr">v{{ $g->version }} • {{ $g->file?->name ?? $g->url }}</p>
                            </div>
                            <div class="flex gap-1.5">
                                <x-admin.edit-link :href="route('admin.guides.index', ['edit' => $g->id])" />
                                <x-admin.delete-button :action="route('admin.guides.destroy', $g)" />
                            </div>
                        </div>
                    @empty
                        <p class="rounded-2xl border border-dashed border-forest-800/15 p-4 text-center text-xs font-bold text-slate-400">
                            لم يُرفق دليل لهذا المسار بعد
                            @can('edit-content')
                                — <a href="{{ route('admin.guides.index', ['track' => $t->id]) }}" class="text-gold-600 hover:underline">أضف دليلًا</a>
                            @endcan
                        </p>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
