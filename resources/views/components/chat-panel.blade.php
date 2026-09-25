{{-- لوحة محادثة المساعد الذكي — تُستخدم في قسم المساعد وفي النافذة العائمة --}}
@php
    $greeting = 'أهلًا بك في مساعِد الابتعاث الذكي. اسألني عن الشروط، المسارات الستة، المعدلات، المستندات، المكافآت، أو أي تفصيلة في رحلتك — وسأجيبك فورًا وفق نظام الابتعاث.';
    $contactIcons = ['phone' => 'phone', 'mail' => 'mail', 'chat' => 'message-circle'];
@endphp

<div
    x-data="chatPanel(@js(route('assistant.ask')), @js($greeting), @js(config('kasp.suggested_questions')))"
    {{ $attributes->class('flex flex-col overflow-hidden rounded-3xl border border-forest-800/12 bg-white shadow-[0_36px_80px_-30px_rgba(8,39,29,0.35)]') }}
>
    {{-- header --}}
    <div class="pattern-star-dark flex items-center gap-3.5 bg-forest-950 px-5 py-4">
        <span class="relative grid size-11 shrink-0 place-items-center rounded-2xl bg-gold-500 text-forest-950">
            <x-lucide-bot class="size-5.5" />
            <span class="absolute -end-0.5 -top-0.5 size-3 rounded-full border-2 border-forest-950 bg-emerald-400"></span>
        </span>
        <div class="min-w-0 flex-1 leading-tight">
            <div class="truncate text-sm font-bold text-white">مساعِد الابتعاث الذكي</div>
            <div class="mt-0.5 flex items-center gap-1.5 text-[11px] font-medium text-emerald-300">
                <span class="size-1.5 rounded-full bg-emerald-400"></span>
                متصل الآن — يرد وفق نظام الابتعاث
            </div>
        </div>
        <a href="{{ route('admin.kb.index') }}" aria-label="لوحة تحكم المعرفة" title="لوحة تحكم المعرفة"
           class="grid size-9 shrink-0 place-items-center rounded-xl border border-white/15 text-white/60 transition-all hover:border-gold-400/60 hover:text-gold-300">
            <x-lucide-settings-2 class="size-4.5" />
        </a>
    </div>

    {{-- messages --}}
    <div x-ref="scroller" class="flex-1 space-y-4 overflow-y-auto bg-sand/40 p-4 md:p-5" aria-live="polite">
        <template x-for="msg in messages" :key="msg.id">
            <div class="flex" :class="msg.role === 'user' ? 'justify-start' : 'justify-end'">
                <div class="max-w-[86%] rounded-2xl px-4 py-3 text-sm leading-relaxed shadow-sm"
                     :class="msg.role === 'user' ? 'rounded-se-md bg-forest-800 text-white' : 'rounded-ee-md border border-forest-800/8 bg-white text-ink'">
                    <template x-if="msg.role === 'bot'">
                        <div class="mb-1.5 flex items-center gap-1.5 text-[10px] font-bold text-gold-600">
                            <x-lucide-sparkles class="size-3" />
                            المساعد الذكي
                        </div>
                    </template>
                    <p class="whitespace-pre-line" x-text="msg.text"></p>

                    <template x-if="msg.contact">
                        <div class="mt-3 space-y-2 rounded-xl border border-gold-500/30 bg-gold-500/[0.06] p-3">
                            <div class="flex items-center gap-1.5 text-[11px] font-bold text-gold-700">
                                <x-lucide-life-buoy class="size-3.5" />
                                قنوات التواصل مع الإدارة
                            </div>
                            @foreach (config('kasp.contact_methods') as $c)
                                <a href="{{ $c['href'] }}" class="group/c flex items-center justify-between gap-3 rounded-lg bg-white px-3 py-2.5 transition-all hover:bg-forest-800 hover:text-white">
                                    <span class="flex items-center gap-2.5">
                                        @svg('lucide-'.$contactIcons[$c['icon']], 'size-4 text-forest-700 group-hover/c:text-gold-300')
                                        <span class="text-xs font-bold">{{ $c['label'] }}</span>
                                    </span>
                                    <span class="font-plex text-xs font-semibold text-slate-500 group-hover/c:text-white/80" dir="ltr">{{ $c['value'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </template>

                    <template x-if="msg.suggestions && msg.suggestions.length">
                        <div class="mt-3 flex flex-wrap gap-1.5">
                            <template x-for="s in msg.suggestions" :key="s">
                                <button type="button" @click="send(s)" x-text="s"
                                        class="rounded-full border border-forest-800/15 bg-sand px-3 py-1.5 text-[11px] font-bold text-forest-800 transition-all hover:border-gold-500/60 hover:bg-gold-500/10 hover:text-gold-700"></button>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        {{-- typing --}}
        <div x-show="typing" x-transition.opacity class="flex justify-end" x-cloak>
            <div class="flex items-center gap-1.5 rounded-2xl rounded-ee-md border border-forest-800/8 bg-white px-4 py-3.5 shadow-sm">
                @foreach ([0, 150, 300] as $d)
                    <span class="size-1.5 animate-bounce rounded-full bg-forest-700/60" style="animation-delay: {{ $d }}ms"></span>
                @endforeach
            </div>
        </div>
    </div>

    {{-- input --}}
    <form @submit.prevent="send()" class="flex items-center gap-2 border-t border-forest-800/10 bg-white p-3">
        <input x-model="input" placeholder="اكتب سؤالك هنا… مثال: ما شروط مسار الروّاد؟" aria-label="سؤالك للمساعد"
               class="min-w-0 flex-1 rounded-full bg-sand px-4.5 py-3 text-sm font-medium text-ink outline-none transition-all placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-forest-600/30">
        <button type="submit" aria-label="إرسال" :disabled="!input.trim() || typing"
                class="grid size-11 shrink-0 place-items-center rounded-full transition-all"
                :class="input.trim() && !typing ? 'bg-forest-800 text-gold-300 hover:bg-forest-700 hover:shadow-lg hover:shadow-forest-800/30' : 'bg-slate-100 text-slate-400'">
            <x-lucide-send class="size-4.5 -scale-x-100" />
        </button>
    </form>

    <div class="flex items-center justify-center gap-1.5 border-t border-forest-800/8 bg-white pb-2.5 pt-1.5 text-[10px] font-medium text-slate-400">
        <x-lucide-circle-check class="size-3 text-forest-600" />
        إجابات استرشادية — تُحدَّث قاعدة المعرفة من لوحة التحكم
    </div>
</div>
