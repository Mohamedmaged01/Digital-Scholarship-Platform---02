@extends('layouts.admin')
@section('page-title', 'خارطة الطريق')

@section('admin')
<div class="mx-auto max-w-4xl space-y-3.5">
    <div class="flex flex-wrap items-start gap-2">
        <div class="flex-1"><x-admin.search-bar :q="$q" placeholder="ابحث في المحطات…" /></div>
        @can('delete-content')
            <form method="POST" action="{{ route('admin.stations.reset') }}" onsubmit="return confirm('ستُستبدل المحطات الحالية بالمحطات الافتراضية. متابعة؟')">
                @csrf
                <button type="submit" class="inline-flex items-center gap-1.5 rounded-full border border-forest-800/15 px-4 py-2.5 text-xs font-bold text-slate-500 transition-all hover:bg-sand">
                    <x-lucide-rotate-ccw class="size-3.5" /> استعادة الافتراضي
                </button>
            </form>
        @endcan
    </div>
    <h2 class="flex items-center gap-2 text-sm font-bold text-forest-800">
        <x-lucide-route class="size-4 text-gold-600" /> محطات الرحلة ({{ $items->count() }})
    </h2>

    @forelse ($items as $s)
        <div @class(['rounded-2xl border bg-white p-4 shadow-sm', 'border-gold-500 ring-4 ring-gold-500/20' => $editingId === $s->id, 'border-forest-800/12' => $editingId !== $s->id])>
            @if ($editingId === $s->id && auth()->user()->can('edit-content'))
                <form method="POST" action="{{ route('admin.stations.update', $s) }}" class="space-y-3">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="title" class="dash-label">عنوان المحطة</label>
                            <input id="title" name="title" value="{{ old('title', $s->title) }}" class="dash-input" required>
                            <x-admin.error name="title" />
                        </div>
                        <div>
                            <label for="duration" class="dash-label">المدة التقديرية</label>
                            <input id="duration" name="duration" value="{{ old('duration', $s->duration) }}" class="dash-input">
                        </div>
                        <div class="col-span-2">
                            <label for="description" class="dash-label">الوصف</label>
                            <textarea id="description" name="description" rows="2" class="dash-input resize-y leading-relaxed" required>{{ old('description', $s->description) }}</textarea>
                            <x-admin.error name="description" />
                        </div>
                        <div class="col-span-2">
                            <label for="detail" class="dash-label">التفاصيل</label>
                            <textarea id="detail" name="detail" rows="2" class="dash-input resize-y leading-relaxed">{{ old('detail', $s->detail) }}</textarea>
                        </div>
                        <div class="col-span-2">
                            <label for="points" class="dash-label">النقاط <span class="font-medium text-slate-400">(تفصل بينها فاصلة)</span></label>
                            <input id="points" name="points" value="{{ old('points', implode('، ', $s->points)) }}" class="dash-input">
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-forest-800 px-6 py-2.5 text-xs font-bold text-gold-300 transition-all hover:bg-forest-700">
                            <x-lucide-save class="size-3.5" /> حفظ المحطة
                        </button>
                        <a href="{{ route('admin.stations.index') }}" class="rounded-full border border-forest-800/15 px-5 py-2.5 text-xs font-bold text-slate-500 hover:bg-sand">إلغاء</a>
                    </div>
                </form>
            @else
                <div class="flex items-center gap-3">
                    <span dir="ltr" class="grid size-9 shrink-0 place-items-center rounded-full bg-forest-800 font-plex text-xs font-bold text-gold-300">{{ $s->code }}</span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-ink">{{ $s->title }} <span class="text-xs font-medium text-slate-400">— {{ $s->duration }}</span></p>
                        <p class="mt-0.5 line-clamp-1 text-[12px] text-slate-400">{{ $s->description }}</p>
                    </div>
                    <x-admin.edit-link :href="route('admin.stations.index', ['edit' => $s->id])" />
                </div>
            @endif
        </div>
    @empty
        <x-admin.empty icon="route" title="لا توجد محطات مطابقة" />
    @endforelse
</div>
@endsection
