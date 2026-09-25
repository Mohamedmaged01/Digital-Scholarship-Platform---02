@extends('layouts.admin')
@section('page-title', 'الأخبار والإعلانات')

@php
    $cats = config('kasp.news_categories');
    $e = $editing;
@endphp

@section('admin')
<div class="grid gap-8 lg:grid-cols-[400px_1fr]">
    {{-- -------- form -------- --}}
    <form method="POST" action="{{ $e ? route('admin.news.update', $e) : route('admin.news.store') }}"
          class="h-fit rounded-3xl border border-forest-800/12 bg-white p-5 shadow-sm lg:sticky lg:top-24">
        @csrf
        @if ($e) @method('PUT') @endif
        <fieldset @disabled(auth()->user()->cannot('edit-content')) class="space-y-3.5">
            <h2 class="flex items-center gap-2 text-sm font-bold text-ink">
                @if ($e) <x-lucide-pencil class="size-4 text-gold-600" /> تعديل الخبر
                @else <x-lucide-file-plus-2 class="size-4 text-gold-600" /> رفع وإضافة خبر جديد @endif
            </h2>

            <div>
                <label for="title" class="dash-label">عنوان الخبر</label>
                <input id="title" name="title" value="{{ old('title', $e?->title) }}" placeholder="عنوان رسمي واضح ومختصر…" class="dash-input" required minlength="6">
                <x-admin.error name="title" />
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="category" class="dash-label">التصنيف</label>
                    <select id="category" name="category" class="dash-input appearance-none">
                        @foreach ($cats as $key => $c)
                            <option value="{{ $key }}" @selected(old('category', $e?->category ?? 'announcement') === $key)>{{ $c['tab'] }}</option>
                        @endforeach
                    </select>
                    <x-admin.error name="category" />
                </div>
                <div>
                    <label for="published_on" class="dash-label">التاريخ</label>
                    <input id="published_on" name="published_on" type="date" dir="ltr" value="{{ old('published_on', $e?->published_on?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" class="dash-input">
                    <x-admin.error name="published_on" />
                </div>
                <div>
                    <label for="author" class="dash-label">الجهة المصدرة</label>
                    <input id="author" name="author" value="{{ old('author', $e?->author ?? 'الإدارة العامة للابتعاث') }}" class="dash-input">
                    <x-admin.error name="author" />
                </div>
                <div>
                    <label for="read_minutes" class="dash-label">مدة القراءة (دقائق)</label>
                    <input id="read_minutes" name="read_minutes" type="number" min="1" max="30" dir="ltr" value="{{ old('read_minutes', $e?->read_minutes ?? 3) }}" class="dash-input">
                    <x-admin.error name="read_minutes" />
                </div>
            </div>

            <div>
                <label for="excerpt" class="dash-label">الموجز (يظهر في البطاقات)</label>
                <textarea id="excerpt" name="excerpt" rows="2" placeholder="جملة أو جملتان تلخصان الخبر…" class="dash-input resize-none leading-relaxed" required>{{ old('excerpt', $e?->excerpt) }}</textarea>
                <x-admin.error name="excerpt" />
            </div>

            <div>
                <label for="body" class="dash-label">التفاصيل الكاملة</label>
                <textarea id="body" name="body" rows="7" placeholder="نص الخبر كاملًا…&#10;افصل بين الفقرات بسطر فارغ." class="dash-input resize-y leading-relaxed" required>{{ old('body', $e?->body) }}</textarea>
                <x-admin.error name="body" />
            </div>

            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-gold-500/40 bg-gold-500/[0.06] px-4 py-3">
                <input type="checkbox" name="pinned" value="1" @checked(old('pinned', $e?->pinned)) class="size-4 accent-[#b08c2f]">
                <span class="flex items-center gap-1.5 text-[13px] font-bold text-gold-700">
                    <x-lucide-pin class="size-4" />
                    تثبيت الخبر في صدارة المركز الإعلامي
                </span>
            </label>

            <x-admin.form-actions :editing="(bool) $e" create-label="نشر الخبر" :cancel="route('admin.news.index')" />
        </fieldset>
    </form>

    {{-- -------- list -------- --}}
    <div class="space-y-3.5">
        <x-admin.search-bar :q="$q" placeholder="ابحث في الأخبار بالعنوان أو الجهة…" />
        <h2 class="flex items-center gap-2 text-sm font-bold text-forest-800">
            <x-lucide-newspaper class="size-4 text-gold-600" />
            الأخبار المنشورة ({{ $items->count() }})
        </h2>

        @forelse ($items as $n)
            <div @class([
                'flex flex-wrap items-start gap-3 rounded-2xl border bg-white p-4 shadow-sm transition-all hover:border-gold-500/40',
                'border-gold-500 ring-4 ring-gold-500/20' => $e?->id === $n->id,
                'border-forest-800/12' => $e?->id !== $n->id,
            ])>
                <x-star-emblem class="mt-0.5 size-5 shrink-0 text-gold-500/70" stroke-width="1.2" />
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-[10px] font-bold {{ $cats[$n->category]['chipClass'] }}">
                            <span class="size-1.5 rounded-full {{ $cats[$n->category]['dotClass'] }}"></span>
                            {{ $cats[$n->category]['short'] }}
                        </span>
                        @if ($n->pinned)
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-gold-600"><x-lucide-pin class="size-3" /> مثبّت</span>
                        @endif
                        <span class="font-plex text-[11px] font-semibold text-slate-400" dir="ltr">{{ $n->published_on->format('Y-m-d') }}</span>
                    </div>
                    <p class="mt-1.5 line-clamp-2 text-sm font-bold leading-relaxed text-ink">{{ $n->title }}</p>
                    <p class="mt-1 text-[12px] text-slate-400">{{ $n->author }} • {{ $n->read_minutes }} دقائق قراءة</p>
                </div>
                <div class="flex shrink-0 gap-1.5">
                    @can('edit-content')
                        <form method="POST" action="{{ route('admin.news.pin', $n) }}">
                            @csrf @method('PATCH')
                            <button type="submit" aria-label="تبديل التثبيت" title="تبديل التثبيت" @class([
                                'grid size-9 place-items-center rounded-full border transition-all',
                                'border-gold-500/60 bg-gold-500/15 text-gold-700' => $n->pinned,
                                'border-forest-800/15 text-slate-400 hover:bg-sand' => ! $n->pinned,
                            ])>
                                <x-lucide-pin class="size-4" />
                            </button>
                        </form>
                    @endcan
                    <x-admin.edit-link :href="route('admin.news.index', ['edit' => $n->id])" />
                    <x-admin.delete-button :action="route('admin.news.destroy', $n)" />
                </div>
            </div>
        @empty
            <x-admin.empty icon="newspaper" :title="$q ? 'لا توجد أخبار مطابقة' : 'لا توجد أخبار منشورة'" desc="أضف أول خبر من النموذج المجاور." />
        @endforelse
    </div>
</div>
@endsection
