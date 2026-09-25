<div x-data="{ expanded: false }" @class([
    'rounded-2xl border bg-white p-4 shadow-sm transition-all hover:border-gold-500/40',
    'border-gold-500 ring-4 ring-gold-500/20' => $active,
    'border-forest-800/12' => ! $active,
])>
    <div class="flex items-start gap-3">
        <x-star-emblem class="mt-0.5 size-5 shrink-0 text-gold-500/70" stroke-width="1.2" />
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <p class="text-sm font-bold text-ink">{{ $entry->question }}</p>
                <span @class(['rounded-full px-2.5 py-0.5 text-[10px] font-bold', 'bg-gold-500/15 text-gold-700' => $entry->is_custom, 'bg-forest-50 text-forest-700' => ! $entry->is_custom])>
                    {{ $entry->is_custom ? 'من الإدارة' : 'أساسي' }}
                </span>
            </div>
            <p class="mt-1.5 text-[13px] leading-relaxed text-slate-500" :class="expanded ? '' : 'line-clamp-2'">{{ $entry->answer }}</p>
            @if ($entry->keywords)
                <div class="mt-2 flex flex-wrap gap-1.5">
                    @foreach ($entry->keywords as $k)
                        <span class="rounded-full bg-sand px-2.5 py-1 text-[10px] font-bold text-slate-500" @if ($loop->index >= 5) x-show="expanded" x-cloak @endif>{{ $k }}</span>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="flex shrink-0 flex-col gap-1.5">
            <button type="button" @click="expanded = !expanded" class="rounded-full border border-forest-800/15 px-3 py-1.5 text-[11px] font-bold text-slate-500 transition-all hover:bg-sand"
                    x-text="expanded ? 'إخفاء' : 'عرض'">عرض</button>
            @if ($entry->is_custom)
                <x-admin.edit-link :href="route('admin.kb.index', ['edit' => $entry->id])" />
                <x-admin.delete-button :action="route('admin.kb.destroy', $entry)" />
            @endif
        </div>
    </div>
</div>
