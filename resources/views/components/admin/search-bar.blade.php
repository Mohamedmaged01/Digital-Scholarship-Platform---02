{{-- شريط بحث/تصفية بطلب GET — يمكن تمرير عناصر تحكم إضافية داخل الـ slot --}}
@props(['q' => '', 'placeholder' => 'ابحث…', 'extra' => null])
<form method="GET" class="flex flex-wrap items-center gap-2">
    <div class="relative min-w-52 flex-1">
        <x-lucide-search class="absolute start-4 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
        <input type="search" name="q" value="{{ $q }}" placeholder="{{ $placeholder }}" aria-label="{{ $placeholder }}"
               class="dash-input bg-white ps-10" onsearch="this.form.submit()">
    </div>
    {{ $slot }}
    <button type="submit" class="inline-flex items-center gap-1.5 rounded-full bg-forest-800 px-4 py-2.5 text-xs font-bold text-gold-300 transition-all hover:bg-forest-700">
        <x-lucide-search class="size-3.5" /> بحث
    </button>
    @if ($q !== '' || request()->except('q', 'page'))
        <a href="{{ url()->current() }}" class="rounded-full border border-forest-800/15 px-4 py-2.5 text-xs font-bold text-slate-500 transition-all hover:bg-sand">مسح</a>
    @endif
</form>
