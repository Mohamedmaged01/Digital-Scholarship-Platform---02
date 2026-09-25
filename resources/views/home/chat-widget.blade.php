<div x-data="{ open: false }" @open-chat.window="open = true" @keydown.escape.window="open = false">
    {{-- FAB --}}
    <button type="button" @click="open = !open" :aria-label="open ? 'إغلاق المساعد الذكي' : 'فتح المساعد الذكي'" :aria-expanded="open"
            class="animate-enter fixed bottom-5 left-5 z-[60] grid size-14 place-items-center rounded-full shadow-2xl transition-colors duration-300"
            style="--delay: 1.6s"
            :class="open ? 'bg-forest-950 text-gold-300' : 'bg-forest-800 text-gold-300 hover:bg-forest-700'">
        <span x-show="!open" x-transition:enter="transition duration-200" x-transition:enter-start="rotate-90 opacity-0" x-transition:enter-end="rotate-0 opacity-100">
            <x-lucide-message-circle class="size-6" />
        </span>
        <span x-show="open" x-cloak x-transition:enter="transition duration-200" x-transition:enter-start="-rotate-90 opacity-0" x-transition:enter-end="rotate-0 opacity-100">
            <x-lucide-x class="size-6" />
        </span>
        <span x-show="!open" class="absolute -end-0.5 -top-0.5 grid size-4 place-items-center rounded-full bg-gold-500 font-plex text-[9px] font-bold text-forest-950">AI</span>
    </button>

    {{-- panel --}}
    <div x-show="open" x-cloak
         x-transition:enter="transition duration-350 ease-out-expo" x-transition:enter-start="translate-y-7 scale-95 opacity-0" x-transition:enter-end="translate-y-0 scale-100 opacity-100"
         x-transition:leave="transition duration-250 ease-in" x-transition:leave-start="translate-y-0 scale-100 opacity-100" x-transition:leave-end="translate-y-7 scale-95 opacity-0"
         class="fixed bottom-24 left-5 z-[60] h-[min(72vh,580px)] w-[min(92vw,400px)] origin-bottom-left">
        <x-chat-panel class="h-full" />
    </div>
</div>
