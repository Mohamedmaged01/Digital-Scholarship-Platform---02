@extends('layouts.admin')
@section('page-title', 'الواجهة الرئيسية')

@php
    $e = $editing;
    $locked = auth()->user()->cannot('edit-content');
@endphp

@section('admin')
{{-- ══════ نصوص الواجهة ورابط التقديم ══════ --}}
<form method="POST" action="{{ route('admin.home.settings') }}" class="rounded-3xl border border-forest-800/12 bg-white p-5 shadow-sm md:p-6" novalidate>
    @csrf
    @method('PUT')
    <fieldset @disabled($locked) class="space-y-5">
        <h2 class="flex items-center gap-2 text-sm font-bold text-ink"><x-lucide-type class="size-4 text-gold-600" /> عنوان الواجهة ونصها</h2>
        <p class="-mt-3 text-[12px] text-slate-500">يظهر الجزء المميَّز بعد العنوان باللون الذهبي. تُعرض النسخة الإنجليزية عند تبديل لغة المنصة.</p>

        <div class="grid gap-5 lg:grid-cols-2">
            @foreach (['ar' => 'العربية', 'en' => 'English'] as $lang => $langLabel)
                <div class="space-y-3.5 rounded-2xl bg-sand/50 p-4" @if ($lang === 'en') dir="ltr" @endif>
                    <p class="text-[12px] font-bold text-forest-700">{{ $langLabel }}</p>
                    <div>
                        <label for="hero_title_{{ $lang }}" class="dash-label">{{ $lang === 'ar' ? 'العنوان' : 'Title' }}</label>
                        <input id="hero_title_{{ $lang }}" name="hero_title_{{ $lang }}" value="{{ old("hero_title_{$lang}", $settings["hero_title_{$lang}"]) }}" class="dash-input" @if ($lang === 'ar') required @endif>
                        <x-admin.error name="hero_title_{{ $lang }}" />
                    </div>
                    <div>
                        <label for="hero_highlight_{{ $lang }}" class="dash-label">{{ $lang === 'ar' ? 'الجزء المميَّز (ذهبي)' : 'Highlighted part (gold)' }}</label>
                        <input id="hero_highlight_{{ $lang }}" name="hero_highlight_{{ $lang }}" value="{{ old("hero_highlight_{$lang}", $settings["hero_highlight_{$lang}"]) }}" class="dash-input">
                        <x-admin.error name="hero_highlight_{{ $lang }}" />
                    </div>
                    <div>
                        <label for="hero_text_{{ $lang }}" class="dash-label">{{ $lang === 'ar' ? 'النص' : 'Text' }}</label>
                        <textarea id="hero_text_{{ $lang }}" name="hero_text_{{ $lang }}" rows="5" class="dash-input resize-y leading-relaxed" @if ($lang === 'ar') required @endif>{{ old("hero_text_{$lang}", $settings["hero_text_{$lang}"]) }}</textarea>
                        <x-admin.error name="hero_text_{{ $lang }}" />
                    </div>
                </div>
            @endforeach
        </div>

        <div class="max-w-xl">
            <label for="apply_url" class="dash-label">رابط زر «ابدأ التقديم»</label>
            <input id="apply_url" name="apply_url" dir="ltr" value="{{ old('apply_url', $settings['apply_url']) }}" class="dash-input text-left" placeholder="https://uap.sa/" required>
            <p class="mt-1.5 text-[11px] text-slate-400">يفتح في نافذة جديدة — منصة قبول افتراضيًا.</p>
            <x-admin.error name="apply_url" />
        </div>

        @can('edit-content')
            <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-forest-800 px-7 py-3 text-sm font-bold text-gold-300 transition-all hover:bg-forest-700">
                <x-lucide-save class="size-4" /> حفظ نصوص الواجهة
            </button>
        @endcan
    </fieldset>
</form>

{{-- ══════ معرض الصور ══════ --}}
<div class="mt-8 grid gap-8 lg:grid-cols-[380px_1fr]">
    <form method="POST" enctype="multipart/form-data" action="{{ $e ? route('admin.home.slides.update', $e) : route('admin.home.slides.store') }}"
          class="h-fit rounded-3xl border border-forest-800/12 bg-white p-5 shadow-sm lg:sticky lg:top-24" novalidate>
        @csrf
        @if ($e) @method('PUT') @endif
        <fieldset @disabled($locked) class="space-y-3.5">
            <h2 class="flex items-center gap-2 text-sm font-bold text-ink">
                @if ($e) <x-lucide-pencil class="size-4 text-gold-600" /> تعديل صورة المعرض
                @else <x-lucide-image-plus class="size-4 text-gold-600" /> إضافة صورة للمعرض @endif
            </h2>

            @if ($e)
                <img src="{{ $e->imageUrl() }}" alt="" class="aspect-[4/3] w-full rounded-2xl border border-forest-800/10 object-cover">
            @endif
            <div>
                <label for="image_file" class="dash-label">{{ $e ? 'استبدال الصورة' : 'الصورة' }}</label>
                <input id="image_file" name="image_file" type="file" accept="image/jpeg,image/png,image/webp"
                       class="block w-full cursor-pointer rounded-xl border border-dashed border-forest-800/25 bg-sand/40 p-3 text-xs text-slate-500 file:me-3 file:rounded-full file:border-0 file:bg-forest-800 file:px-4 file:py-2 file:text-xs file:font-bold file:text-gold-300">
                <p class="mt-1.5 text-[11px] text-slate-400">JPG أو PNG أو WebP حتى 5 ميجابايت — يُفضَّل بنسبة 4:3 (مثل 1600×1200).</p>
                <x-admin.error name="image_file" />
            </div>
            <details class="text-[12px]" @if ($errors->has('image')) open @endif>
                <summary class="cursor-pointer font-bold text-slate-500">أو استخدم صورة من صور المنصة</summary>
                <input id="image" name="image" dir="ltr" value="{{ old('image', $e && ! $e->hasUploadedImage() ? $e->image : '') }}" class="dash-input mt-2 text-left" placeholder="images/tracks/rowad.jpg">
                <x-admin.error name="image" />
            </details>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="title_ar" class="dash-label">العنوان</label>
                    <input id="title_ar" name="title_ar" value="{{ old('title_ar', $e?->title_ar) }}" class="dash-input" placeholder="مسار الروّاد" required>
                    <x-admin.error name="title_ar" />
                </div>
                <div>
                    <label for="title_en" class="dash-label">Title</label>
                    <input id="title_en" name="title_en" dir="ltr" value="{{ old('title_en', $e?->title_en) }}" class="dash-input" placeholder="Rowad Track">
                </div>
                <div>
                    <label for="subtitle_ar" class="dash-label">النص الفرعي</label>
                    <input id="subtitle_ar" name="subtitle_ar" value="{{ old('subtitle_ar', $e?->subtitle_ar) }}" class="dash-input">
                    <x-admin.error name="subtitle_ar" />
                </div>
                <div>
                    <label for="subtitle_en" class="dash-label">Subtitle</label>
                    <input id="subtitle_en" name="subtitle_en" dir="ltr" value="{{ old('subtitle_en', $e?->subtitle_en) }}" class="dash-input">
                </div>
            </div>
            <div>
                <label for="link" class="dash-label">الرابط عند الضغط</label>
                <input id="link" name="link" dir="ltr" value="{{ old('link', $e?->link) }}" class="dash-input text-left" placeholder="tracks/rowad أو #tracks أو https://…">
                <x-admin.error name="link" />
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="sort" class="dash-label">الترتيب</label>
                    <input id="sort" name="sort" type="number" min="0" dir="ltr" value="{{ old('sort', $e?->sort ?? ($slides->max('sort') + 1)) }}" class="dash-input">
                </div>
                <div class="space-y-2 pt-6">
                    <label class="flex items-center gap-2 text-[12px] font-bold text-slate-600">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $e?->is_active ?? true)) class="size-4 accent-[#b08c2f]"> ظاهرة في المعرض
                    </label>
                    <label class="flex items-center gap-2 text-[12px] font-bold text-slate-600">
                        <input type="checkbox" name="overlay" value="1" @checked(old('overlay', $e?->overlay ?? true)) class="size-4 accent-[#b08c2f]"> تدرّج داكن (للصور)
                    </label>
                </div>
            </div>
            <x-admin.form-actions :editing="(bool) $e" create-label="إضافة إلى المعرض" :cancel="route('admin.home.index')" />
        </fieldset>
    </form>

    <div class="space-y-3.5">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="flex items-center gap-2 text-sm font-bold text-forest-800"><x-lucide-images class="size-4 text-gold-600" /> معرض الواجهة ({{ $slides->count() }})</h2>
            @can('delete-content')
                <form method="POST" action="{{ route('admin.home.slides.reset') }}" onsubmit="return confirm('سيُستبدل المعرض الحالي بالافتراضي (شعار البرنامج وصور المسارات). متابعة؟')">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-full border border-forest-800/15 px-4 py-2.5 text-xs font-bold text-slate-500 transition-all hover:bg-sand">
                        <x-lucide-rotate-ccw class="size-3.5" /> استعادة الافتراضي
                    </button>
                </form>
            @endcan
        </div>
        <p class="text-[12px] text-slate-500">تتبدّل الصور تلقائيًا كل 5 ثوانٍ بالترتيب المحدد. الصورة المضافة هنا مستقلة عن صورة بطاقة المسار.</p>
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
            @forelse ($slides as $s)
                <div @class(['overflow-hidden rounded-2xl border bg-white shadow-sm', 'border-gold-500 ring-4 ring-gold-500/20' => $e?->id === $s->id, 'border-forest-800/12' => $e?->id !== $s->id, 'opacity-60' => ! $s->is_active])>
                    <div class="relative">
                        <img src="{{ $s->imageUrl() }}" alt="" loading="lazy" class="aspect-[4/3] w-full object-cover">
                        <span class="absolute start-2 top-2 rounded-md bg-forest-900/85 px-2 py-0.5 font-plex text-[11px] font-bold text-gold-300" dir="ltr">#{{ $s->sort }}</span>
                        @unless ($s->is_active)
                            <span class="absolute end-2 top-2 rounded-md bg-white/90 px-2 py-0.5 text-[10px] font-bold text-slate-500">مخفية</span>
                        @endunless
                    </div>
                    <div class="flex items-center gap-2 p-3">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-[13px] font-bold text-ink">{{ $s->title_ar ?: '—' }}</p>
                            <p class="truncate text-[11px] text-slate-400">{{ $s->subtitle_ar }}</p>
                        </div>
                        <x-admin.edit-link :href="route('admin.home.index', ['edit' => $s->id])" />
                        <x-admin.delete-button :action="route('admin.home.slides.destroy', $s)" />
                    </div>
                </div>
            @empty
                <div class="sm:col-span-2 xl:col-span-3"><x-admin.empty icon="images" title="لا توجد صور في المعرض — تظهر صورة شعار البرنامج وحدها" /></div>
            @endforelse
        </div>
    </div>
</div>
@endsection
