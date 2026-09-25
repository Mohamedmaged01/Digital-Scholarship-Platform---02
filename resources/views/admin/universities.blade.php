@extends('layouts.admin')
@section('page-title', 'الجامعات')

@php
    $e = $editing;
    $regions = config('kasp.regions');
@endphp

@section('admin')
<div class="grid gap-8 lg:grid-cols-[380px_1fr]">
    <form method="POST" action="{{ $e ? route('admin.universities.update', $e) : route('admin.universities.store') }}"
          class="h-fit rounded-3xl border border-forest-800/12 bg-white p-5 shadow-sm lg:sticky lg:top-24">
        @csrf
        @if ($e) @method('PUT') @endif
        <fieldset @disabled(auth()->user()->cannot('edit-content')) class="space-y-3.5">
            <h2 class="flex items-center gap-2 text-sm font-bold text-ink">
                @if ($e) <x-lucide-pencil class="size-4 text-gold-600" /> تعديل الجامعة
                @else <x-lucide-plus class="size-4 text-gold-600" /> إضافة جامعة جديدة @endif
            </h2>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="name_en" class="dash-label">الاسم الإنجليزي</label>
                    <input id="name_en" name="name_en" dir="ltr" value="{{ old('name_en', $e?->name_en) }}" class="dash-input" placeholder="MIT" required>
                    <x-admin.error name="name_en" />
                </div>
                <div>
                    <label for="name_ar" class="dash-label">الاسم العربي</label>
                    <input id="name_ar" name="name_ar" value="{{ old('name_ar', $e?->name_ar) }}" class="dash-input" placeholder="معهد ماساتشوستس للتقنية" required>
                    <x-admin.error name="name_ar" />
                </div>
                <div>
                    <label for="rank" class="dash-label">التصنيف العالمي</label>
                    <input id="rank" name="rank" dir="ltr" type="number" min="1" value="{{ old('rank', $e?->rank) }}" class="dash-input" placeholder="1">
                    <x-admin.error name="rank" />
                </div>
                <div>
                    <label for="region" class="dash-label">المنطقة</label>
                    <select id="region" name="region" class="dash-input appearance-none">
                        @foreach ($regions as $key => $label)
                            <option value="{{ $key }}" @selected(old('region', $e?->region ?? 'na') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-admin.error name="region" />
                </div>
                <div>
                    <label for="city" class="dash-label">المدينة</label>
                    <input id="city" name="city" value="{{ old('city', $e?->city) }}" class="dash-input" placeholder="لندن">
                </div>
                <div>
                    <label for="country" class="dash-label">الدولة</label>
                    <input id="country" name="country" value="{{ old('country', $e?->country) }}" class="dash-input" placeholder="المملكة المتحدة">
                </div>
                <div class="col-span-2">
                    <label for="fields" class="dash-label">التخصصات <span class="font-medium text-slate-400">(تفصل بينها فاصلة)</span></label>
                    <input id="fields" name="fields" value="{{ old('fields', $e ? implode('، ', $e->fields) : '') }}" class="dash-input" placeholder="هندسة، حاسب، ذكاء اصطناعي">
                </div>
                <div class="col-span-2">
                    <label for="acceptance" class="dash-label">معدل القبول</label>
                    <input id="acceptance" name="acceptance" value="{{ old('acceptance', $e?->acceptance) }}" class="dash-input" placeholder="٪4">
                </div>
            </div>
            <x-admin.form-actions :editing="(bool) $e" create-label="إضافة الجامعة" :cancel="route('admin.universities.index')" />
        </fieldset>
    </form>

    <div class="space-y-3.5">
        <div class="flex flex-wrap items-start gap-2">
            <div class="flex-1">
                <x-admin.search-bar :q="$q" placeholder="ابحث باسم الجامعة أو الدولة أو التخصص…">
                    <select name="region" onchange="this.form.submit()" aria-label="المنطقة" class="dash-input w-auto appearance-none bg-white">
                        <option value="all">كل المناطق</option>
                        @foreach ($regions as $key => $label)
                            <option value="{{ $key }}" @selected($region === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </x-admin.search-bar>
            </div>
            @can('delete-content')
                <form method="POST" action="{{ route('admin.universities.reset') }}" onsubmit="return confirm('ستُستبدل القائمة الحالية بالجامعات الافتراضية. متابعة؟')">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-full border border-forest-800/15 px-4 py-2.5 text-xs font-bold text-slate-500 transition-all hover:bg-sand">
                        <x-lucide-rotate-ccw class="size-3.5" /> استعادة الافتراضي
                    </button>
                </form>
            @endcan
        </div>
        <h2 class="flex items-center gap-2 text-sm font-bold text-forest-800">
            <x-lucide-globe class="size-4 text-gold-600" /> الجامعات المعتمدة ({{ $items->count() }})
        </h2>
        @forelse ($items as $u)
            <div @class([
                'flex flex-wrap items-center gap-3 rounded-2xl border bg-white p-4 shadow-sm transition-all hover:border-gold-500/40',
                'border-gold-500 ring-4 ring-gold-500/20' => $e?->id === $u->id,
                'border-forest-800/12' => $e?->id !== $u->id,
            ])>
                <span dir="ltr" class="grid size-10 shrink-0 place-items-center rounded-xl bg-forest-50 font-plex text-xs font-bold text-forest-800">#{{ $u->rank }}</span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-bold text-ink"><span dir="ltr" class="font-plex">{{ $u->name_en }}</span> — {{ $u->name_ar }}</p>
                    <p class="mt-0.5 flex items-center gap-1.5 text-[12px] text-slate-400">
                        <x-lucide-map-pin class="size-3.5 shrink-0" /> {{ $u->city }}، {{ $u->country }} • {{ implode('، ', $u->fields) }}
                    </p>
                </div>
                <div class="flex gap-1.5">
                    <x-admin.edit-link :href="route('admin.universities.index', ['edit' => $u->id])" />
                    <x-admin.delete-button :action="route('admin.universities.destroy', $u)" />
                </div>
            </div>
        @empty
            <x-admin.empty icon="globe" title="لا توجد جامعات مطابقة" />
        @endforelse
    </div>
</div>
@endsection
