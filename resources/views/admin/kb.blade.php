@extends('layouts.admin')
@section('page-title', 'قاعدة المعرفة')

@php $e = $editing; @endphp

@section('admin')
<div class="grid gap-8 lg:grid-cols-[380px_1fr]">
    {{-- نموذج --}}
    <form method="POST" action="{{ $e ? route('admin.kb.update', $e) : route('admin.kb.store') }}"
          class="h-fit rounded-3xl border border-forest-800/12 bg-white p-5 shadow-sm lg:sticky lg:top-24">
        @csrf
        @if ($e) @method('PUT') @endif
        <fieldset @disabled(auth()->user()->cannot('edit-content')) class="space-y-3.5">
            <h2 class="flex items-center gap-2 text-sm font-bold text-ink">
                @if ($e) <x-lucide-pencil class="size-4 text-gold-600" /> تعديل الإجابة
                @else <x-lucide-plus class="size-4 text-gold-600" /> إضافة سؤال وإجابة جديدة @endif
            </h2>
            @if ($prefill && ! $e)
                <p class="rounded-xl border border-gold-500/40 bg-gold-500/[0.06] px-3.5 py-2.5 text-[12px] font-bold text-gold-700">
                    سؤال منقول من قائمة «أسئلة بلا إجابة» — اكتب إجابته المعتمدة
                </p>
            @endif
            <div>
                <label for="question" class="dash-label">السؤال</label>
                <input id="question" name="question" value="{{ old('question', $e?->question ?? $prefill) }}" placeholder="مثال: هل يشمل الابتعاث المرافقين؟" class="dash-input" required minlength="4">
                <x-admin.error name="question" />
            </div>
            <div>
                <label for="answer" class="dash-label">الإجابة التي يرد بها المساعد</label>
                <textarea id="answer" name="answer" rows="5" placeholder="اكتب الإجابة الرسمية المعتمدة…" class="dash-input resize-y leading-relaxed" required minlength="4" @if ($prefill && ! $e) autofocus @endif>{{ old('answer', $e?->answer) }}</textarea>
                <x-admin.error name="answer" />
            </div>
            <div>
                <label for="keywords" class="dash-label">كلمات مفتاحية <span class="font-medium text-slate-400">(تفصل بينها فاصلة)</span></label>
                <input id="keywords" name="keywords" value="{{ old('keywords', $e ? implode('، ', $e->keywords) : '') }}" placeholder="مرافق، عائلة، أطفال" class="dash-input">
                <x-admin.error name="keywords" />
            </div>
            <x-admin.form-actions :editing="(bool) $e" create-label="إضافة للقاعدة" :cancel="route('admin.kb.index')" />
        </fieldset>
    </form>

    {{-- القوائم --}}
    <div class="space-y-3.5">
        <x-admin.search-bar :q="$q" placeholder="ابحث في الأسئلة والإجابات والكلمات المفتاحية…" />

        <h2 class="flex items-center gap-2 text-sm font-bold text-forest-800">
            <x-lucide-wand-sparkles class="size-4 text-gold-600" /> الإجابات المضافة من الإدارة ({{ $customs->count() }})
        </h2>
        @forelse ($customs as $entry)
            @include('admin.partials.kb-entry', ['entry' => $entry, 'active' => $e?->id === $entry->id])
        @empty
            <p class="rounded-2xl border border-dashed border-forest-800/20 bg-white/70 p-6 text-center text-sm font-bold text-slate-500">لا توجد إجابات مضافة بعد</p>
        @endforelse

        <h2 class="flex items-center gap-2 pt-2 text-sm font-bold text-forest-800">
            <x-lucide-book-open-text class="size-4 text-gold-600" /> الإجابات الأساسية للنظام ({{ $core->count() }})
        </h2>
        @foreach ($core as $entry)
            @include('admin.partials.kb-entry', ['entry' => $entry, 'active' => false])
        @endforeach
    </div>
</div>
@endsection
