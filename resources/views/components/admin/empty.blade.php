@props(['icon', 'title', 'desc' => null])
<div class="rounded-3xl border border-dashed border-forest-800/20 bg-white/70 p-12 text-center">
    @svg('lucide-'.$icon, 'mx-auto size-10 text-forest-700/30')
    <p class="mt-4 text-lg font-bold text-forest-800">{{ $title }}</p>
    @if ($desc)
        <p class="mt-2 text-sm text-slate-500">{{ $desc }}</p>
    @endif
</div>
