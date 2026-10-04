@php
    $past = $program->isPast();
    $degreeName = config("kasp.degrees.{$program->degree_id}", $program->degree_id);
    $typeName = config("kasp.waed_types.{$program->program_type}", $program->program_type);
@endphp
<article x-show="shows({{ $program->id }})" x-transition.opacity
         @class([
             'group relative flex flex-col rounded-3xl border bg-white transition-all duration-300 hover:-translate-y-1.5',
             'border-slate-200 opacity-85 hover:opacity-100 hover:shadow-md' => $past,
             'border-forest-800/10 hover:border-gold-500/50 hover:shadow-[0_28px_56px_-24px_rgba(8,39,29,0.3)]' => ! $past,
         ])>
    <div @class(['relative overflow-hidden rounded-t-3xl px-5 pb-4 pt-5', 'bg-slate-50' => $past, 'pattern-star-dark bg-forest-900' => ! $past])>
        <div class="flex items-start justify-between gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-[11px] font-bold {{ $program->statusClass() }}">
                <x-lucide-clock class="size-3" />{{ $program->statusLabel() }}
            </span>
            @if ($program->is_featured)
                <span class="inline-flex items-center gap-1 rounded-full bg-gold-500/20 px-2.5 py-1 text-[10px] font-bold text-gold-500">
                    <x-lucide-sparkles class="size-3" /> مميز
                </span>
            @endif
        </div>
        <h3 @class(['mt-3 text-base font-bold leading-snug', 'text-slate-700' => $past, 'text-white' => ! $past])>{{ $program->name_ar }}</h3>
        <p @class(['mt-1 text-right font-plex text-[11px]', 'text-slate-400' => $past, 'text-gold-300/80' => ! $past]) dir="ltr">{{ $program->name_en }}</p>
    </div>

    <div class="flex flex-1 flex-col gap-3 px-5 pb-5 pt-4">
        <div class="flex items-center gap-2 text-xs font-bold text-forest-700">
            <x-lucide-building-2 class="size-3.5 shrink-0 text-gold-600" />{{ $program->company_ar }}
        </div>
        <div class="grid grid-cols-2 gap-x-4 gap-y-1.5 text-[12px] font-semibold text-slate-600">
            <span class="flex items-center gap-1.5"><x-lucide-graduation-cap class="size-3.5 shrink-0 text-gold-600" />{{ $degreeName }}</span>
            <span class="flex items-center gap-1.5"><x-lucide-clock class="size-3.5 shrink-0 text-gold-600" />{{ $program->duration }}</span>
            <span class="flex items-center gap-1.5"><x-lucide-map-pin class="size-3.5 shrink-0 text-gold-600" />{{ $program->city }}، {{ $program->country_name }}</span>
            <span class="flex items-center gap-1.5"><x-lucide-file-text class="size-3.5 shrink-0 text-gold-600" />{{ $typeName }}</span>
        </div>
        <div class="flex flex-wrap gap-1.5">
            <span class="rounded-full bg-forest-50 px-2.5 py-1 text-[10px] font-bold text-forest-700">{{ $program->sector }}</span>
            <span class="rounded-full bg-sand px-2.5 py-1 text-[10px] font-bold text-slate-600">{{ $program->major_name }}</span>
        </div>
        @if (! empty($program->gpa['min']))
            <div class="flex items-center gap-2 rounded-xl bg-forest-50 px-3 py-1.5 text-[11px] font-bold text-forest-700">
                <x-lucide-graduation-cap class="size-3.5 text-gold-600" />
                معدل لا يقل عن <span dir="ltr" class="font-plex">{{ $program->gpa['min'] }} / {{ $program->gpa['scale'] }}</span>
            </div>
        @endif
        <div @class(['flex flex-col gap-1 rounded-xl border p-3', 'border-slate-200 bg-slate-50' => $past, 'border-amber-200 bg-amber-50' => ! $past])>
            <div @class(['flex items-center gap-1.5 text-[11px] font-bold', 'text-slate-500' => $past, 'text-amber-700' => ! $past])>
                <x-lucide-calendar-days class="size-3.5" />حالة وفترة التقديم
            </div>
            <div @class(['text-[13px] font-bold', 'text-red-500' => $past, 'text-emerald-700' => ! $past])>{{ $program->statusLabel() }}</div>
            @if ($program->application_start && $program->application_end)
                <div class="font-plex text-[10px] font-bold text-slate-500" dir="ltr">{{ $program->application_start->format('Y-m-d') }} — {{ $program->application_end->format('Y-m-d') }}</div>
            @endif
        </div>
        <a href="{{ route('waed.show', $program) }}" @class([
            'mt-auto inline-flex items-center justify-center gap-2 rounded-full border py-2.5 text-xs font-bold transition-all after:absolute after:inset-0',
            'border-slate-200 text-slate-500 group-hover:border-slate-400' => $past,
            'border-forest-800/20 text-forest-800 group-hover:border-forest-800 group-hover:bg-forest-800 group-hover:text-gold-300' => ! $past,
        ])>
            {{ $past ? 'عرض تفاصيل البرنامج السابق' : 'عرض الشروط والتفاصيل الكاملة' }}
            <x-lucide-arrow-left class="size-3.5" />
        </a>
    </div>
</article>
