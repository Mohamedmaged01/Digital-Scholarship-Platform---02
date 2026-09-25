{{-- أزرار حفظ/إلغاء لنماذج لوحة التحكم --}}
@props(['editing' => false, 'createLabel' => 'إضافة', 'cancel' => null])
@can('edit-content')
    <div class="flex gap-2">
        <button type="submit" class="inline-flex flex-1 items-center justify-center gap-2 rounded-full bg-forest-800 py-3 text-sm font-bold text-gold-300 transition-all hover:bg-forest-700">
            <x-lucide-save class="size-4" />
            {{ $editing ? 'حفظ التعديلات' : $createLabel }}
        </button>
        @if ($editing && $cancel)
            <a href="{{ $cancel }}" class="grid place-items-center rounded-full border border-forest-800/15 px-5 text-sm font-bold text-slate-500 hover:bg-sand">إلغاء</a>
        @endif
    </div>
@endcan
