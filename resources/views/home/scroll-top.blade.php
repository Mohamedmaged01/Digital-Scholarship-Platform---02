<button type="button" x-data="scrollTop" x-show="visible" x-cloak
        x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="scale-60 translate-y-6 opacity-0" x-transition:enter-end="scale-100 translate-y-0 opacity-100"
        x-transition:leave="transition duration-200 ease-in" x-transition:leave-start="scale-100 opacity-100" x-transition:leave-end="scale-60 translate-y-6 opacity-0"
        @click="window.scrollTo({ top: 0, behavior: 'smooth' })" aria-label="العودة إلى أعلى الصفحة" title="العودة إلى الأعلى"
        class="fixed bottom-5 right-5 z-[55] grid size-12 place-items-center rounded-full bg-forest-800 text-gold-300 shadow-[0_16px_36px_-10px_rgba(8,39,29,0.6)] transition-colors duration-300 hover:bg-forest-700 hover:text-gold-200">
    <svg class="absolute inset-0 size-full -rotate-90" viewBox="0 0 48 48" fill="none" aria-hidden="true">
        <circle cx="24" cy="24" r="21" stroke="rgba(255,255,255,0.16)" stroke-width="2.5" />
        <circle cx="24" cy="24" r="21" stroke="#c9a338" stroke-width="2.5" stroke-linecap="round"
                stroke-dasharray="131.95" :stroke-dashoffset="dashOffset" />
    </svg>
    <x-lucide-arrow-up class="size-5" />
</button>
