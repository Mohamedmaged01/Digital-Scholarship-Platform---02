@extends('layouts.admin')
@section('page-title', 'أسئلة بلا إجابة')

@section('admin')
<div class="mx-auto max-w-3xl space-y-3.5">
    <x-admin.search-bar :q="$q" placeholder="ابحث في الأسئلة المعلّقة…" />

    @forelse ($items as $u)
        <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-forest-800/12 bg-white p-4 shadow-sm">
            <x-lucide-inbox class="size-5 shrink-0 text-gold-600" />
            <div class="min-w-0 flex-1">
                <p class="text-sm font-bold text-ink">{{ $u->question }}</p>
                <p class="mt-0.5 text-[11px] text-slate-400">
                    سُئل أول مرة {{ $u->first_asked_at->locale('ar')->translatedFormat('j F Y') }}
                    @if ($u->asked_count > 1)
                        • <span class="font-bold text-gold-700">تكرر {{ $u->asked_count }} مرات</span>
                    @endif
                </p>
            </div>
            @can('edit-content')
                <div class="flex gap-2">
                    <form method="POST" action="{{ route('admin.pending.answer', $u) }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-full bg-forest-800 px-4 py-2 text-xs font-bold text-gold-300 transition-all hover:bg-forest-700">
                            <x-lucide-wand-sparkles class="size-3.5" /> إنشاء إجابة
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.pending.destroy', $u) }}">
                        @csrf @method('DELETE')
                        <button type="submit" aria-label="تجاهل" title="تجاهل" class="grid size-9 place-items-center rounded-full border border-red-200 text-red-500 transition-all hover:bg-red-50">
                            <x-lucide-trash-2 class="size-4" />
                        </button>
                    </form>
                </div>
            @endcan
        </div>
    @empty
        <x-admin.empty icon="inbox" title="لا توجد أسئلة معلّقة" desc="أي سؤال يسأله الزائر ولا يجد له المساعد إجابة سيظهر هنا تلقائيًا." />
    @endforelse
</div>
@endsection
