@extends('layouts.admin')
@section('page-title', 'المسارات الدراسية')

@php
    $e = $editing;
    $degrees = config('kasp.degrees');
    $selectedDegrees = old('degrees', $e?->degrees ?? ['bachelor']);
@endphp

@section('admin')
<div class="grid gap-8 lg:grid-cols-[420px_1fr]">
    <form method="POST" action="{{ $e ? route('admin.tracks.update', $e) : route('admin.tracks.store') }}"
          class="h-fit rounded-3xl border border-forest-800/12 bg-white p-5 shadow-sm">
        @csrf
        @if ($e) @method('PUT') @endif
        <fieldset @disabled(auth()->user()->cannot('edit-content')) class="space-y-3.5">
            <h2 class="flex items-center gap-2 text-sm font-bold text-ink">
                @if ($e) <x-lucide-pencil class="size-4 text-gold-600" /> تعديل المسار
                @else <x-lucide-plus class="size-4 text-gold-600" /> إضافة مسار جديد @endif
            </h2>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="name" class="dash-label">اسم المسار</label>
                    <input id="name" name="name" value="{{ old('name', $e?->name) }}" class="dash-input" placeholder="مسار الروّاد" required>
                    <x-admin.error name="name" />
                </div>
                <div>
                    <label for="badge" class="dash-label">الشارة التصنيفية</label>
                    <input id="badge" name="badge" value="{{ old('badge', $e?->badge) }}" class="dash-input" placeholder="أفضل 30 جامعة عالميًا">
                </div>
                <div>
                    <label for="code" class="dash-label">الرمز</label>
                    <input id="code" name="code" dir="ltr" value="{{ old('code', $e?->code) }}" class="dash-input" placeholder="07">
                </div>
                <div>
                    <label for="icon" class="dash-label">الأيقونة</label>
                    <select id="icon" name="icon" class="dash-input appearance-none">
                        @foreach (\App\Models\Track::ICONS as $icon)
                            <option value="{{ $icon }}" @selected(old('icon', $e?->icon ?? 'rocket') === $icon)>{{ $icon }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-2">
                    <label for="en_subtitle" class="dash-label">العنوان الإنجليزي</label>
                    <input id="en_subtitle" name="en_subtitle" dir="ltr" value="{{ old('en_subtitle', $e?->en_subtitle) }}" class="dash-input" placeholder="Leadership • Innovation">
                </div>
                <div class="col-span-2">
                    <label for="description" class="dash-label">الوصف</label>
                    <textarea id="description" name="description" rows="3" class="dash-input resize-y leading-relaxed" required>{{ old('description', $e?->description) }}</textarea>
                    <x-admin.error name="description" />
                </div>
                <div>
                    <label for="gpa" class="dash-label">أدنى معدل</label>
                    <input id="gpa" name="gpa" value="{{ old('gpa', $e?->gpa) }}" class="dash-input" placeholder="4.5 من 5.0">
                </div>
                <div>
                    <label for="ranking" class="dash-label">نطاق التصنيف</label>
                    <input id="ranking" name="ranking" value="{{ old('ranking', $e?->ranking) }}" class="dash-input" placeholder="أفضل 30 عالميًا">
                </div>
                <div class="col-span-2">
                    <label for="fields" class="dash-label">التخصصات <span class="font-medium text-slate-400">(تفصل بينها فاصلة)</span></label>
                    <input id="fields" name="fields" value="{{ old('fields', $e ? implode('، ', $e->fields) : '') }}" class="dash-input">
                </div>
                <div class="col-span-2">
                    <label for="extra_fields" class="dash-label">تخصصات إضافية (+)</label>
                    <input id="extra_fields" name="extra_fields" dir="ltr" type="number" min="0" value="{{ old('extra_fields', $e?->extra_fields ?? 0) }}" class="dash-input">
                </div>
                <div class="col-span-2">
                    <label for="perks" class="dash-label">مزايا المسار <span class="font-medium text-slate-400">(سطر لكل ميزة)</span></label>
                    <textarea id="perks" name="perks" rows="3" class="dash-input resize-y leading-relaxed">{{ old('perks', $e ? implode("\n", $e->perks) : '') }}</textarea>
                </div>
                <div class="col-span-2">
                    <label for="image" class="dash-label">مسار الصورة</label>
                    <input id="image" name="image" dir="ltr" value="{{ old('image', $e?->image) }}" class="dash-input" placeholder="/images/tracks/rowad.jpg أو رابط من مركز الملفات">
                </div>
                <div class="col-span-2">
                    <span class="dash-label">المراحل الدراسية</span>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($degrees as $key => $label)
                            <label class="cursor-pointer">
                                <input type="checkbox" name="degrees[]" value="{{ $key }}" class="peer sr-only" @checked(in_array($key, $selectedDegrees, true))>
                                <span class="block rounded-full border border-forest-800/15 px-4 py-2 text-xs font-bold text-slate-500 transition-all hover:border-forest-700/50 peer-checked:border-forest-800 peer-checked:bg-forest-800 peer-checked:text-gold-300 peer-focus-visible:ring-2 peer-focus-visible:ring-gold-500">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    <x-admin.error name="degrees" />
                </div>
            </div>
            <x-admin.form-actions :editing="(bool) $e" create-label="إضافة المسار" :cancel="route('admin.tracks.index')" />
        </fieldset>
    </form>

    <div class="space-y-3.5">
        <div class="flex flex-wrap items-start gap-2">
            <div class="flex-1"><x-admin.search-bar :q="$q" placeholder="ابحث في المسارات…" /></div>
            @can('delete-content')
                <form method="POST" action="{{ route('admin.tracks.reset') }}" onsubmit="return confirm('ستُستبدل المسارات الحالية بالمسارات الافتراضية. متابعة؟')">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-full border border-forest-800/15 px-4 py-2.5 text-xs font-bold text-slate-500 transition-all hover:bg-sand">
                        <x-lucide-rotate-ccw class="size-3.5" /> استعادة الافتراضي
                    </button>
                </form>
            @endcan
        </div>
        <h2 class="flex items-center gap-2 text-sm font-bold text-forest-800">
            <x-lucide-graduation-cap class="size-4 text-gold-600" /> المسارات ({{ $items->count() }})
        </h2>
        @forelse ($items as $t)
            <div @class([
                'flex items-center gap-3.5 rounded-2xl border bg-white p-4 shadow-sm transition-all hover:border-gold-500/40',
                'border-gold-500 ring-4 ring-gold-500/20' => $e?->id === $t->id,
                'border-forest-800/12' => $e?->id !== $t->id,
            ])>
                <img src="{{ asset(ltrim($t->image, '/')) }}" alt="" class="size-14 shrink-0 rounded-xl object-cover">
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span dir="ltr" class="font-plex text-xs font-bold text-gold-600">{{ $t->code }}</span>
                        <p class="text-sm font-bold text-ink">{{ $t->name }}</p>
                        <span class="rounded-full bg-forest-50 px-2.5 py-0.5 text-[10px] font-bold text-forest-700">{{ $t->badge }}</span>
                    </div>
                    <p class="mt-1 line-clamp-1 text-[12px] text-slate-400">{{ $t->description }}</p>
                </div>
                <div class="flex shrink-0 gap-1.5">
                    <x-admin.edit-link :href="route('admin.tracks.index', ['edit' => $t->id])" />
                    <x-admin.delete-button :action="route('admin.tracks.destroy', $t)" />
                </div>
            </div>
        @empty
            <x-admin.empty icon="graduation-cap" title="لا توجد مسارات مطابقة" />
        @endforelse
    </div>
</div>
@endsection
