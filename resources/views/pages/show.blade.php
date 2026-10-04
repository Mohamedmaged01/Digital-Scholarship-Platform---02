@extends('layouts.page')

@section('title', "{$page->title_ar} | برنامج خادم الحرمين الشريفين للابتعاث")

@section('page')
<div class="mx-auto max-w-3xl px-5 py-10 md:px-8">
    <article class="overflow-hidden rounded-3xl border border-forest-800/10 bg-white shadow-sm">
        <header class="flex items-center gap-3 border-b border-forest-800/10 px-7 py-5 md:px-10">
            <span class="grid size-10 place-items-center rounded-xl bg-forest-800 text-gold-300"><x-lucide-file-text class="size-5" /></span>
            <div>
                <h1 class="text-lg font-bold text-ink md:text-xl">{{ $page->title_ar }}</h1>
                <p class="mt-0.5 flex items-center gap-1.5 text-[11px] text-slate-400">
                    <x-lucide-calendar-clock class="size-3" />
                    آخر تحديث: {{ $page->updated_at->locale('ar')->translatedFormat('j F Y') }}
                </p>
            </div>
        </header>
        <div class="space-y-5 px-7 py-8 text-[15px] leading-[2] text-slate-600 md:px-10">
            {{ $page->renderedContent() }}
        </div>
        <footer class="flex flex-wrap items-center justify-between gap-3 border-t border-forest-800/10 px-7 py-5 md:px-10">
            <p class="text-[11px] text-slate-400">البوابة التعريفية والإرشادية لبرنامج خادم الحرمين الشريفين للابتعاث</p>
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 rounded-full border border-forest-800/20 px-6 py-2.5 text-sm font-bold text-forest-800 transition-all hover:bg-forest-800 hover:text-gold-300">
                <x-lucide-arrow-right class="size-4" /> العودة
            </a>
        </footer>
    </article>
</div>
@endsection
