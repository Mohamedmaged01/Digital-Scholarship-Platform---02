@extends('layouts.admin')
@section('page-title', 'مركز الملفات')

@php
    $kindIcons = ['image' => 'image', 'pdf' => 'file-text', 'doc' => 'file-text', 'other' => 'file-archive'];
    $maxMb = round(config('kasp.max_upload_kb') / 1024);
@endphp

@section('admin')
<div class="space-y-6">
    @can('edit-content')
        <form x-data="dropzone" x-ref="form" method="POST" action="{{ route('admin.files.store') }}" enctype="multipart/form-data"
              @dragover.prevent="over = true" @dragleave="over = false" @drop.prevent="drop($event)">
            @csrf
            <label class="flex w-full cursor-pointer flex-col items-center justify-center gap-3 rounded-3xl border-2 border-dashed px-6 py-12 text-center transition-all"
                   :class="over ? 'border-gold-500 bg-gold-500/10' : 'border-forest-800/20 bg-white/60 hover:border-gold-500/60 hover:bg-gold-500/5'">
                <span class="grid size-14 place-items-center rounded-2xl bg-forest-800 text-gold-400 shadow-lg shadow-forest-900/30">
                    <x-lucide-cloud-upload class="size-6" />
                </span>
                <span>
                    <span class="block text-base font-bold text-ink">اسحب الملفات هنا أو اضغط للاختيار</span>
                    <span class="mt-1 block text-xs text-slate-500">صور، PDF، مستندات — حد أقصى {{ $maxMb }}MB للملف الواحد وعشرة ملفات في المرة • تُحفظ في التخزين العام للمنصة</span>
                </span>
                <span class="inline-flex items-center gap-2 rounded-full bg-forest-800 px-6 py-2.5 text-xs font-bold text-gold-300">
                    <x-lucide-file class="size-3.5" /> اختيار ملفات
                </span>
                <input x-ref="input" type="file" name="files[]" multiple class="sr-only"
                       accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt" @change="$refs.form.requestSubmit()">
            </label>
        </form>
    @endcan

    @if ($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-600" role="alert">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <x-admin.search-bar :q="$q" placeholder="ابحث في الملفات بالاسم…" />

    @if ($items->isEmpty())
        <x-admin.empty icon="file-archive" :title="$total === 0 ? 'لا توجد ملفات مرفوعة بعد' : 'لا توجد نتائج مطابقة'"
                       desc="ارفع صور الأخبار أو شعارات الشركاء أو أي مستندات لاستخدامها في المنصة." />
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($items as $f)
                @php $kind = $f->kind(); @endphp
                <div @class([
                    'group overflow-hidden rounded-2xl border bg-white shadow-sm transition-all',
                    'border-gold-500 ring-4 ring-gold-500/25' => $highlight === $f->id,
                    'border-forest-800/12 hover:border-gold-500/40' => $highlight !== $f->id,
                ])>
                    <div class="relative flex h-36 items-center justify-center overflow-hidden bg-sand">
                        @if ($kind === 'image')
                            <img src="{{ $f->url() }}" alt="{{ $f->name }}" loading="lazy" class="size-full object-cover transition-transform duration-500 group-hover:scale-105">
                        @else
                            @svg('lucide-'.$kindIcons[$kind], 'size-12 text-forest-700/40')
                        @endif
                        <span class="absolute start-2.5 top-2.5 rounded-full bg-forest-950/80 px-2.5 py-1 text-[10px] font-bold text-gold-300 backdrop-blur">{{ round($f->size / 1024) }} KB</span>
                    </div>
                    <div class="p-3.5">
                        <p class="truncate text-[13px] font-bold text-ink" dir="ltr" title="{{ $f->name }}">{{ $f->name }}</p>
                        <p class="mt-0.5 text-[11px] text-slate-400">
                            {{ $f->created_at->locale('ar')->translatedFormat('j F Y') }} • {{ strtoupper(\Illuminate\Support\Str::after($f->mime, '/')) ?: 'FILE' }}
                        </p>
                        <div class="mt-3 flex gap-1.5">
                            <a href="{{ $f->url() }}" target="_blank" rel="noopener"
                               class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-full bg-forest-800 px-3 py-2 text-[11px] font-bold text-gold-300 transition-all hover:bg-forest-700">
                                <x-lucide-external-link class="size-3.5" /> عرض
                            </a>
                            <button type="button" x-data="copyText(@js($f->url()))" @click="copy()"
                                    class="inline-flex items-center gap-1 rounded-full border px-3 py-2 text-[11px] font-bold transition-all"
                                    :class="copied ? 'border-emerald-400 bg-emerald-50 text-emerald-600' : 'border-forest-800/15 text-slate-500 hover:bg-sand'">
                                <x-lucide-copy class="size-3.5" />
                                <span x-text="copied ? 'نُسخ' : 'نسخ الرابط'">نسخ الرابط</span>
                            </button>
                            <x-admin.delete-button :action="route('admin.files.destroy', $f)" size="size-8" />
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
