{{-- قالب الصفحات الفرعية: صفحة المسار، واعد، الصفحات القانونية --}}
@extends('layouts.app')

@section('content')
    <header class="sticky top-0 z-50 border-b border-forest-800/10 bg-white/90 backdrop-blur-xl">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-5 md:px-8">
            <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3" aria-label="الرئيسية — برنامج خادم الحرمين الشريفين للابتعاث">
                <x-brand-logo class="h-10" />
                <span class="hidden h-7 w-px bg-forest-800/15 xl:block"></span>
                <span class="hidden text-[10px] font-semibold leading-tight text-slate-500 xl:block">البوابة التعريفية<br>والإرشادية</span>
            </a>

            <nav class="hidden items-center gap-5 lg:flex" aria-label="أقسام المنصة">
                @foreach (config('kasp.nav') as $l)
                    <a href="{{ url('/').$l['href'] }}" class="text-[13px] font-semibold text-slate-600 transition-colors hover:text-forest-700">{{ $l['label'] }}</a>
                @endforeach
            </nav>

            <a href="{{ route('home') }}"
               class="inline-flex shrink-0 items-center gap-2 rounded-full border border-forest-800/20 px-5 py-2.5 text-sm font-bold text-forest-800 transition-all hover:bg-forest-800 hover:text-gold-300">
                <x-lucide-arrow-right class="size-4" />
                <span class="hidden sm:inline">العودة إلى المنصة</span>
            </a>
        </div>
    </header>

    <main class="pb-40">
        @yield('page')
    </main>

    @include('partials.site-footer')
    @include('home.scroll-top')
    @include('home.chat-widget')
@endsection
