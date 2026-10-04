@extends('layouts.admin')
@section('page-title', 'السياسات والشروط')

@php $e = $creating ? null : $editing; @endphp

@section('admin')
<div class="grid gap-8 lg:grid-cols-[260px_1fr]">
    <aside class="space-y-2">
        @foreach ($pages as $p)
            <a href="{{ route('admin.pages.index', ['edit' => $p->id]) }}" @class([
                'flex items-center gap-2.5 rounded-2xl border px-4 py-3 text-sm font-bold transition-all',
                'border-forest-800 bg-forest-800 text-gold-300' => $e?->id === $p->id,
                'border-forest-800/12 bg-white text-ink hover:border-gold-500/50' => $e?->id !== $p->id,
            ])>
                <x-lucide-file-text class="size-4 shrink-0" />{{ $p->title_ar }}
            </a>
        @endforeach
        @can('edit-content')
            <a href="{{ route('admin.pages.index', ['edit' => 'new']) }}" class="flex items-center gap-2.5 rounded-2xl border border-dashed border-forest-800/25 px-4 py-3 text-sm font-bold text-forest-700 transition-all hover:bg-white">
                <x-lucide-plus class="size-4" /> صفحة جديدة
            </a>
        @endcan
        @can('delete-content')
            <form method="POST" action="{{ route('admin.pages.reset') }}" onsubmit="return confirm('ستُعاد نصوص سياسة الخصوصية وشروط الاستخدام إلى النص الافتراضي. متابعة؟')">
                @csrf
                <button type="submit" class="mt-2 inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-forest-700">
                    <x-lucide-rotate-ccw class="size-3.5" /> استعادة النصوص الافتراضية
                </button>
            </form>
        @endcan
    </aside>

    <div class="grid gap-6 xl:grid-cols-2" x-data="{ content: @js(old('content', $e?->content ?? '')) }">
        <form method="POST" action="{{ $e ? route('admin.pages.update', $e) : route('admin.pages.store') }}" class="rounded-3xl border border-forest-800/12 bg-white p-5 shadow-sm">
            @csrf
            @if ($e) @method('PUT') @endif
            <fieldset @disabled(auth()->user()->cannot('edit-content')) class="space-y-3.5">
                <h2 class="flex items-center gap-2 text-sm font-bold text-ink">
                    <x-lucide-pencil class="size-4 text-gold-600" /> {{ $e ? 'تحرير الصفحة' : 'صفحة جديدة' }}
                </h2>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="title_ar" class="dash-label">عنوان الصفحة</label>
                        <input id="title_ar" name="title_ar" value="{{ old('title_ar', $e?->title_ar) }}" class="dash-input" required>
                        <x-admin.error name="title_ar" />
                    </div>
                    <div>
                        <label for="slug" class="dash-label">المعرّف في الرابط</label>
                        <input id="slug" name="slug" dir="ltr" value="{{ old('slug', $e?->slug) }}" class="dash-input text-left" placeholder="privacy" required>
                        <x-admin.error name="slug" />
                    </div>
                </div>
                <div>
                    <label for="content" class="dash-label">المحتوى <span class="font-medium text-slate-400">(**نص** للعريض، وسطر فارغ بين الفقرات)</span></label>
                    <textarea id="content" name="content" rows="18" x-model="content" class="dash-input resize-y leading-relaxed" required></textarea>
                    <x-admin.error name="content" />
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <div class="flex-1"><x-admin.form-actions :editing="(bool) $e" create-label="إضافة الصفحة" /></div>
                    @if ($e)
                        <a href="{{ route('pages.show', $e->slug) }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-full border border-forest-800/15 px-4 py-3 text-xs font-bold text-slate-500 hover:bg-sand">
                            <x-lucide-external-link class="size-3.5" /> عرض
                        </a>
                    @endif
                </div>
            </fieldset>
            @if ($e)
                <div class="mt-4 flex justify-end">
                    <x-admin.delete-button :action="route('admin.pages.destroy', $e)" confirm="ستُحذف الصفحة ويختفي رابطها من التذييل. متابعة؟" />
                </div>
            @endif
        </form>

        {{-- معاينة: نفس قواعد العرض في الواجهة، والنص مُهرَّب قبل التنسيق --}}
        <div class="rounded-3xl border border-forest-800/12 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-xs font-bold text-slate-400">معاينة المحتوى</h3>
            <div class="space-y-4 text-[14px] leading-[2] text-slate-600" x-html="renderLegal(content)"></div>
        </div>
    </div>
</div>
@endsection
