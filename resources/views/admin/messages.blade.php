@extends('layouts.admin')
@section('page-title', 'رسائل التواصل')

@section('admin')
<div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.3fr)]">
    <div class="space-y-3">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="flex items-center gap-2 text-sm font-bold text-forest-800"><x-lucide-inbox class="size-4 text-gold-600" /> الرسائل ({{ $items->count() }})</h2>
            <form method="GET" class="relative">
                <x-lucide-search class="pointer-events-none absolute start-3 top-1/2 size-3.5 -translate-y-1/2 text-slate-400" />
                <input type="search" name="q" value="{{ $q }}" placeholder="بحث بالاسم أو الموضوع…" class="dash-input w-56 py-2 ps-9 text-xs">
            </form>
        </div>
        <p class="text-[12px] text-slate-500">ترِد من صفحة «اتصل بنا» ومن زر «تواصل مع الموظف المسؤول» في المساعد الذكي.</p>

        @forelse ($items as $m)
            @php $isNew = ! $since || $m->created_at->gt($since); @endphp
            <a href="{{ route('admin.messages.index', ['open' => $m->id, 'q' => $q ?: null]) }}"
               @class(['block rounded-2xl border bg-white p-4 shadow-sm transition-all hover:border-gold-500/60',
                       'border-gold-500 ring-4 ring-gold-500/20' => $open?->id === $m->id,
                       'border-forest-800/12' => $open?->id !== $m->id])>
                <div class="flex items-center gap-2">
                    @unless ($m->read_at)
                        <span class="size-2 shrink-0 rounded-full bg-red-500" title="غير مقروءة"></span>
                    @endunless
                    <p @class(['min-w-0 flex-1 truncate text-sm text-ink', 'font-bold' => ! $m->read_at, 'font-semibold' => $m->read_at])>{{ $m->subject }}</p>
                    @if ($isNew)
                        <span class="shrink-0 rounded-full bg-red-500 px-2 py-0.5 text-[10px] font-bold text-white">جديدة</span>
                    @endif
                </div>
                <p class="mt-1 truncate text-[12px] text-slate-500">{{ $m->name }} — {{ \Illuminate\Support\Str::limit($m->message, 80) }}</p>
                <p class="mt-1.5 flex items-center gap-3 text-[11px] text-slate-400">
                    <span dir="ltr" class="font-plex">{{ $m->created_at->format('Y-m-d H:i') }}</span>
                    @if ($m->source === 'assistant')
                        <span class="inline-flex items-center gap-1 text-gold-700"><x-lucide-bot class="size-3" /> من المساعد الذكي</span>
                    @endif
                </p>
            </a>
        @empty
            <x-admin.empty icon="inbox" title="لا توجد رسائل بعد" desc="تظهر هنا رسائل نموذج «اتصل بنا» فور إرسالها." />
        @endforelse
    </div>

    <div>
        @if ($open)
            <article class="rounded-3xl border border-forest-800/12 bg-white shadow-sm lg:sticky lg:top-24">
                <header class="flex items-start justify-between gap-3 border-b border-forest-800/8 p-5 md:p-6">
                    <div class="min-w-0">
                        <h2 class="text-lg font-bold text-ink">{{ $open->subject }}</h2>
                        <p class="mt-1 text-[12px] text-slate-400"><span dir="ltr" class="font-plex">{{ $open->created_at->format('Y-m-d H:i') }}</span></p>
                    </div>
                    <x-admin.delete-button :action="route('admin.messages.destroy', $open)" />
                </header>
                <dl class="grid gap-3 border-b border-forest-800/8 p-5 text-sm sm:grid-cols-3 md:px-6">
                    <div><dt class="text-[11px] font-bold text-slate-400">الاسم</dt><dd class="mt-0.5 font-bold text-ink">{{ $open->name }}</dd></div>
                    <div><dt class="text-[11px] font-bold text-slate-400">رقم الهاتف</dt><dd class="mt-0.5 font-plex font-bold text-ink" dir="ltr">{{ $open->phone ?: '—' }}</dd></div>
                    <div class="min-w-0"><dt class="text-[11px] font-bold text-slate-400">البريد الإلكتروني</dt><dd class="mt-0.5 truncate font-plex font-bold text-ink" dir="ltr">{{ $open->email ?: '—' }}</dd></div>
                </dl>
                <div class="whitespace-pre-line p-5 text-[15px] leading-loose text-slate-700 md:p-6">{{ $open->message }}</div>
                <footer class="flex flex-wrap gap-2 border-t border-forest-800/8 p-5 md:px-6">
                    @if ($open->email)
                        <a href="mailto:{{ $open->email }}?subject={{ rawurlencode('رد: '.$open->subject) }}"
                           class="inline-flex items-center gap-2 rounded-full bg-forest-800 px-5 py-2.5 text-xs font-bold text-gold-300 transition-all hover:bg-forest-700">
                            <x-lucide-reply class="size-4" /> الرد بالبريد الإلكتروني
                        </a>
                    @endif
                    @if ($open->phone)
                        <a href="tel:{{ preg_replace('/[^\d+]/', '', $open->phone) }}"
                           class="inline-flex items-center gap-2 rounded-full border border-forest-800/20 px-5 py-2.5 text-xs font-bold text-forest-800 transition-all hover:bg-sand">
                            <x-lucide-phone class="size-4" /> اتصال
                        </a>
                    @endif
                </footer>
            </article>
        @elseif ($items->isNotEmpty())
            <div class="grid h-64 place-items-center rounded-3xl border border-dashed border-forest-800/20 bg-white/60 text-center">
                <p class="text-sm font-bold text-slate-400"><x-lucide-mail-open class="mx-auto mb-2 size-8 text-forest-700/30" /> اختر رسالة لعرضها</p>
            </div>
        @endif
    </div>
</div>
@endsection
