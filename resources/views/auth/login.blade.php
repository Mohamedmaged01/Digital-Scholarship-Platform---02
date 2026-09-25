@extends('layouts.app')

@section('title', 'الدخول إلى لوحة التحكم | برنامج خادم الحرمين الشريفين للابتعاث')
@section('body-class', 'bg-forest-950 font-sans text-ink antialiased')

@php
    $inputCls = 'w-full rounded-2xl border border-white/15 bg-white/[0.06] py-3.5 pe-11 ps-11 text-left text-sm font-medium text-white outline-none backdrop-blur transition-all placeholder:text-slate-500 focus:border-gold-400/70 focus:bg-white/[0.09] focus:ring-4 focus:ring-gold-500/10';
    $demoAccounts = app()->isLocal() ? [
        ['email' => 'admin@kasp.gov.sa', 'password' => 'Admin@2026', 'role' => 'مدير النظام — صلاحيات كاملة'],
        ['email' => 'editor@kasp.gov.sa', 'password' => 'Editor@2026', 'role' => 'محرّر المحتوى — إنشاء وتعديل'],
        ['email' => 'viewer@kasp.gov.sa', 'password' => 'Viewer@2026', 'role' => 'مطّلع — استعراض فقط'],
    ] : [];
@endphp

@section('content')
<div class="pattern-star-dark grain relative flex min-h-screen items-center justify-center overflow-hidden p-4"
     x-data="{ email: @js(old('email', '')), password: '', show: false, busy: false }">
    <div class="absolute -start-32 top-0 size-[28rem] rounded-full bg-forest-600/20 blur-[140px]" aria-hidden="true"></div>
    <div class="absolute -end-24 bottom-0 size-[24rem] rounded-full bg-gold-500/10 blur-[130px]" aria-hidden="true"></div>

    <a href="{{ route('home') }}" class="absolute end-5 top-5 z-10 inline-flex items-center gap-2 rounded-full border border-white/20 px-5 py-2.5 text-xs font-bold text-white/70 transition-all hover:border-gold-400 hover:text-gold-300">
        <x-lucide-arrow-right class="size-4" />
        العودة إلى المنصة
    </a>

    <div class="animate-enter relative my-16 w-full max-w-md">
        <div class="text-center">
            <span class="mx-auto grid size-16 place-items-center rounded-3xl bg-gold-500 text-forest-950 shadow-[0_20px_50px_-12px_rgba(201,163,56,0.6)]">
                <x-star-emblem class="size-9" />
            </span>
            <h1 class="mt-6 text-2xl font-bold text-white md:text-3xl">الدخول إلى لوحة التحكم</h1>
            <p class="mt-2.5 text-sm leading-relaxed text-slate-400">
                منصّة إدارة برنامج خادم الحرمين الشريفين للابتعاث — الدخول مقيد
                بالمستخدمين المخوّلين وفق الصلاحيات المعتمدة
            </p>
        </div>

        <form method="POST" action="{{ route('login') }}" @submit="busy = true"
              class="mt-8 rounded-3xl border border-white/12 bg-white/[0.05] p-6 shadow-[0_40px_90px_-30px_rgba(0,0,0,0.6)] backdrop-blur-xl md:p-7">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="email" class="mb-2 block text-xs font-bold text-slate-300">البريد الإلكتروني</label>
                    <div class="relative">
                        <x-lucide-mail class="absolute start-4 top-1/2 size-4.5 -translate-y-1/2 text-slate-500" />
                        <input id="email" name="email" type="email" dir="ltr" required autofocus autocomplete="username"
                               x-model="email" placeholder="admin@kasp.gov.sa" class="{{ $inputCls }}">
                    </div>
                </div>
                <div>
                    <label for="password" class="mb-2 block text-xs font-bold text-slate-300">كلمة المرور</label>
                    <div class="relative">
                        <x-lucide-lock class="absolute start-4 top-1/2 size-4.5 -translate-y-1/2 text-slate-500" />
                        <input id="password" name="password" :type="show ? 'text' : 'password'" type="password" dir="ltr" required autocomplete="current-password"
                               x-model="password" placeholder="••••••••" class="{{ $inputCls }}">
                        <button type="button" @click="show = !show" :aria-label="show ? 'إخفاء كلمة المرور' : 'إظهار كلمة المرور'"
                                class="absolute end-3.5 top-1/2 grid size-8 -translate-y-1/2 place-items-center rounded-lg text-slate-500 transition-colors hover:text-gold-300">
                            <span x-show="!show"><x-lucide-eye class="size-4.5" /></span>
                            <span x-show="show" x-cloak><x-lucide-eye-off class="size-4.5" /></span>
                        </button>
                    </div>
                </div>
                <label class="flex cursor-pointer items-center gap-2.5 text-xs font-bold text-slate-400">
                    <input type="checkbox" name="remember" value="1" class="size-4 accent-[#c9a338]">
                    تذكّرني على هذا الجهاز
                </label>
            </div>

            @if ($errors->any())
                <div class="mt-4 flex items-center gap-2.5 rounded-2xl border border-red-400/40 bg-red-500/15 px-4 py-3 text-[13px] font-bold text-red-300" role="alert">
                    <x-lucide-circle-alert class="size-4.5 shrink-0" />
                    {{ $errors->first() }}
                </div>
            @endif

            <button type="submit" :disabled="busy"
                    class="group mt-6 flex w-full items-center justify-center gap-2.5 rounded-full py-4 text-sm font-bold transition-all"
                    :class="busy ? 'bg-white/10 text-slate-400' : 'bg-gold-500 text-forest-950 hover:bg-gold-400 hover:shadow-[0_16px_44px_-10px_rgba(201,163,56,0.65)]'">
                <span x-show="busy" x-cloak class="size-4 animate-spin rounded-full border-2 border-slate-500 border-t-transparent"></span>
                <span x-show="busy" x-cloak>جارٍ التحقق من البيانات…</span>
                <span x-show="!busy" class="inline-flex items-center gap-2.5"><x-lucide-log-in class="size-4.5" />تسجيل الدخول</span>
            </button>

            <p class="mt-4 flex items-center justify-center gap-1.5 text-[11px] font-medium text-slate-500">
                <x-lucide-shield-check class="size-3.5 text-gold-500" />
                كلمات المرور مشفّرة بخوارزمية bcrypt ولا تُخزَّن كنص صريح
            </p>
        </form>

        @if ($demoAccounts)
            <div class="mt-5 rounded-3xl border border-white/10 bg-white/[0.03] p-5">
                <p class="flex items-center gap-2 text-xs font-bold text-gold-300">
                    <x-lucide-info class="size-4" />
                    حسابات تجريبية (بيئة التطوير فقط)
                </p>
                <div class="mt-3.5 space-y-2">
                    @foreach ($demoAccounts as $a)
                        <button type="button" @click="email = @js($a['email']); password = @js($a['password'])"
                                class="group flex w-full items-center justify-between gap-3 rounded-2xl border border-white/10 bg-white/[0.04] px-4 py-3 text-start transition-all hover:border-gold-400/50 hover:bg-white/[0.07]">
                            <span class="min-w-0">
                                <span dir="ltr" class="block truncate font-plex text-xs font-bold text-white">{{ $a['email'] }}</span>
                                <span class="mt-0.5 block text-[10px] text-slate-400">{{ $a['role'] }}</span>
                            </span>
                            <span dir="ltr" class="shrink-0 rounded-lg bg-forest-800 px-2.5 py-1 font-plex text-[10px] font-bold text-gold-300 transition-colors group-hover:bg-gold-500 group-hover:text-forest-950">{{ $a['password'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
