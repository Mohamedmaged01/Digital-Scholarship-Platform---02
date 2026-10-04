@extends('layouts.admin')
@section('page-title', 'وسائل الاتصال')

@php
    $e = $editing;
    $icons = ['phone' => 'هاتف', 'mail' => 'بريد إلكتروني', 'chat' => 'محادثة / واتساب', 'map-pin' => 'عنوان'];
@endphp

@section('admin')
<div class="grid gap-8 lg:grid-cols-[380px_1fr]">
    <form method="POST" action="{{ $e ? route('admin.contact.update', $e) : route('admin.contact.store') }}"
          class="h-fit rounded-3xl border border-forest-800/12 bg-white p-5 shadow-sm lg:sticky lg:top-24">
        @csrf
        @if ($e) @method('PUT') @endif
        <fieldset @disabled(auth()->user()->cannot('edit-content')) class="space-y-3.5">
            <h2 class="flex items-center gap-2 text-sm font-bold text-ink">
                @if ($e) <x-lucide-pencil class="size-4 text-gold-600" /> تعديل وسيلة الاتصال
                @else <x-lucide-plus class="size-4 text-gold-600" /> إضافة وسيلة اتصال @endif
            </h2>
            <div>
                <label for="label" class="dash-label">المسمى</label>
                <input id="label" name="label" value="{{ old('label', $e?->label) }}" class="dash-input" placeholder="الرقم الموحّد" required>
                <x-admin.error name="label" />
            </div>
            <div>
                <label for="value" class="dash-label">القيمة المعروضة</label>
                <input id="value" name="value" dir="ltr" value="{{ old('value', $e?->value) }}" class="dash-input text-left" placeholder="920001122" required>
                <x-admin.error name="value" />
            </div>
            <div>
                <label for="href" class="dash-label">رابط الاتصال</label>
                <input id="href" name="href" dir="ltr" value="{{ old('href', $e?->href) }}" class="dash-input text-left" placeholder="tel:920001122 أو mailto:…">
                <x-admin.error name="href" />
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="icon" class="dash-label">الأيقونة</label>
                    <select id="icon" name="icon" class="dash-input appearance-none">
                        @foreach ($icons as $key => $label)
                            <option value="{{ $key }}" @selected(old('icon', $e?->icon ?? 'phone') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="sort" class="dash-label">الترتيب</label>
                    <input id="sort" name="sort" type="number" min="0" dir="ltr" value="{{ old('sort', $e?->sort ?? 0) }}" class="dash-input">
                </div>
            </div>
            <x-admin.form-actions :editing="(bool) $e" create-label="إضافة" :cancel="route('admin.contact.index')" />
        </fieldset>
    </form>

    <div class="space-y-3.5">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="flex items-center gap-2 text-sm font-bold text-forest-800"><x-lucide-phone class="size-4 text-gold-600" /> وسائل الاتصال ({{ $items->count() }})</h2>
            @can('delete-content')
                <form method="POST" action="{{ route('admin.contact.reset') }}" onsubmit="return confirm('ستُستبدل وسائل الاتصال الحالية بالافتراضية. متابعة؟')">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-full border border-forest-800/15 px-4 py-2.5 text-xs font-bold text-slate-500 transition-all hover:bg-sand">
                        <x-lucide-rotate-ccw class="size-3.5" /> استعادة الافتراضي
                    </button>
                </form>
            @endcan
        </div>
        <p class="text-[12px] text-slate-500">تظهر في تذييل المنصة وفي ردّ المساعد الذكي حين لا يجد إجابة.</p>
        @forelse ($items as $c)
            <div @class(['flex items-center gap-3 rounded-2xl border bg-white p-4 shadow-sm', 'border-gold-500 ring-4 ring-gold-500/20' => $e?->id === $c->id, 'border-forest-800/12' => $e?->id !== $c->id])>
                <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-forest-800 text-gold-300">@svg('lucide-'.$c->lucideIcon(), 'size-4.5')</span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-bold text-ink">{{ $c->label }}</p>
                    <p class="mt-0.5 text-right font-plex text-[12px] text-slate-400" dir="ltr">{{ $c->value }}</p>
                </div>
                <x-admin.edit-link :href="route('admin.contact.index', ['edit' => $c->id])" />
                <x-admin.delete-button :action="route('admin.contact.destroy', $c)" />
            </div>
        @empty
            <x-admin.empty icon="phone" title="لا توجد وسائل اتصال" />
        @endforelse
    </div>
</div>
@endsection
