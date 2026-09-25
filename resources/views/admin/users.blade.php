@extends('layouts.admin')
@section('page-title', 'المستخدمون')

@php
    $e = $editing;
    $me = auth()->user();
    $roles = config('kasp.roles');
    $selectedRole = old('role', $e?->role ?? 'editor');
@endphp

@section('admin')
<div class="grid gap-8 lg:grid-cols-[380px_1fr]">
    {{-- نموذج الإضافة/التعديل --}}
    <form method="POST" action="{{ $e ? route('admin.users.update', $e) : route('admin.users.store') }}"
          class="h-fit space-y-3.5 rounded-3xl border border-forest-800/12 bg-white p-5 shadow-sm lg:sticky lg:top-24" x-data="{ role: @js($selectedRole) }">
        @csrf
        @if ($e) @method('PUT') @endif
        <h2 class="flex items-center gap-2 text-sm font-bold text-ink">
            @if ($e) <x-lucide-pencil class="size-4 text-gold-600" /> تعديل بيانات المستخدم
            @else <x-lucide-plus class="size-4 text-gold-600" /> إضافة مستخدم جديد @endif
        </h2>

        <div>
            <label for="name" class="dash-label">الاسم الكامل</label>
            <input id="name" name="name" value="{{ old('name', $e?->name) }}" placeholder="مثال: نورة العبدالله" class="dash-input" required minlength="3">
            <x-admin.error name="name" />
        </div>

        <div>
            <label for="email" class="dash-label">البريد الإلكتروني</label>
            <input id="email" name="email" type="email" dir="ltr" value="{{ old('email', $e?->email) }}" placeholder="user@kasp.gov.sa" class="dash-input text-left" required>
            <x-admin.error name="email" />
        </div>

        <div>
            <span class="dash-label">الصلاحية</span>
            <div class="space-y-2">
                @foreach ($roles as $key => $r)
                    <label class="flex w-full cursor-pointer items-start gap-3 rounded-2xl border p-3 text-start transition-all"
                           :class="role === '{{ $key }}' ? 'border-forest-800 bg-forest-50' : 'border-forest-800/12 bg-sand/40 hover:border-forest-700/40'">
                        <input type="radio" name="role" value="{{ $key }}" x-model="role" class="sr-only">
                        <span class="mt-0.5 grid size-8 shrink-0 place-items-center rounded-xl"
                              :class="role === '{{ $key }}' ? 'bg-forest-800 text-gold-300' : 'bg-white text-forest-700'">
                            @svg('lucide-'.$r['icon'], 'size-4')
                        </span>
                        <span class="min-w-0">
                            <span class="block text-[13px] font-bold text-ink">{{ $r['label'] }}</span>
                            <span class="mt-0.5 block text-[11px] leading-relaxed text-slate-500">{{ $r['description'] }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
            <x-admin.error name="role" />
        </div>

        <div>
            <label for="password" class="dash-label">
                {{ $e ? 'كلمة مرور جديدة' : 'كلمة المرور' }}
                @if ($e) <span class="ms-1.5 font-medium text-slate-400">(اتركها فارغة للإبقاء على الحالية)</span> @endif
            </label>
            <div class="relative">
                <x-lucide-key-round class="absolute start-4 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                <input id="password" name="password" type="password" dir="ltr" autocomplete="new-password" minlength="8" @required(! $e)
                       placeholder="{{ $e ? '••••••••' : '8 أحرف على الأقل' }}" class="dash-input ps-10 text-left">
            </div>
            <x-admin.error name="password" />
        </div>

        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-forest-800/12 bg-sand/40 px-4 py-3">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $e?->is_active ?? true)) class="size-4 accent-[#0f4632]">
            <span class="text-[13px] font-bold text-ink">الحساب مُفعَّل ويمكنه تسجيل الدخول</span>
        </label>

        <x-admin.form-actions :editing="(bool) $e" create-label="إضافة المستخدم" :cancel="route('admin.users.index')" />
    </form>

    {{-- القائمة --}}
    <div class="space-y-3.5">
        <x-admin.search-bar :q="$q" placeholder="ابحث في المستخدمين بالاسم أو البريد أو الصلاحية…" />

        <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-5">
            @foreach ($stats as $label => $value)
                <div class="rounded-2xl border border-forest-800/12 bg-white p-3 text-center shadow-sm">
                    <div class="font-plex text-xl font-bold text-ink" dir="ltr">{{ $value }}</div>
                    <div class="mt-0.5 text-[10px] font-bold text-slate-500">{{ $label }}</div>
                </div>
            @endforeach
        </div>

        <h2 class="flex items-center gap-2 text-sm font-bold text-forest-800">
            <x-lucide-users class="size-4 text-gold-600" />
            حسابات المستخدمين ({{ $items->count() }})
        </h2>

        @forelse ($items as $u)
            @php $isMe = $u->is($me); @endphp
            <div @class([
                'flex flex-wrap items-center gap-3 rounded-2xl border bg-white p-4 shadow-sm transition-all hover:border-gold-500/40',
                'border-gold-500 ring-4 ring-gold-500/20' => $e?->id === $u->id,
                'border-forest-700/50' => $isMe && $e?->id !== $u->id,
                'border-forest-800/12' => ! $isMe && $e?->id !== $u->id,
                'opacity-70' => ! $u->is_active,
            ])>
                <span @class(['grid size-11 shrink-0 place-items-center rounded-2xl font-plex text-sm font-bold', 'bg-forest-800 text-gold-300' => $u->is_active, 'bg-slate-200 text-slate-500' => ! $u->is_active])>{{ $u->initial() }}</span>

                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="text-sm font-bold text-ink">{{ $u->name }}</p>
                        @if ($isMe)
                            <span class="rounded-full bg-forest-50 px-2 py-0.5 text-[10px] font-bold text-forest-700">حسابك الحالي</span>
                        @endif
                        <span class="rounded-full border px-2.5 py-0.5 text-[10px] font-bold {{ $roles[$u->role]['badge'] }}">{{ $u->roleLabel() }}</span>
                        @unless ($u->is_active)
                            <span class="rounded-full border border-red-200 bg-red-50 px-2.5 py-0.5 text-[10px] font-bold text-red-500">موقوف</span>
                        @endunless
                    </div>
                    <p dir="ltr" class="mt-1 flex items-center justify-end gap-1.5 font-plex text-[12px] text-slate-400">
                        {{ $u->email }}
                        <x-lucide-mail class="size-3.5" />
                    </p>
                </div>

                <div class="flex shrink-0 gap-1.5">
                    @unless ($isMe)
                        <form method="POST" action="{{ route('admin.users.toggle', $u) }}">
                            @csrf @method('PATCH')
                            <button type="submit" title="{{ $u->is_active ? 'إيقاف الحساب' : 'تفعيل الحساب' }}" @class([
                                'rounded-full border px-3.5 py-2 text-[11px] font-bold transition-all',
                                'border-amber-300 text-amber-600 hover:bg-amber-50' => $u->is_active,
                                'border-emerald-300 text-emerald-600 hover:bg-emerald-50' => ! $u->is_active,
                            ])>{{ $u->is_active ? 'إيقاف' : 'تفعيل' }}</button>
                        </form>
                    @endunless
                    <x-admin.edit-link :href="route('admin.users.index', ['edit' => $u->id])" />
                    @unless ($isMe)
                        <x-admin.delete-button :action="route('admin.users.destroy', $u)" confirm="سيُحذف هذا الحساب نهائيًا. متابعة؟" />
                    @endunless
                </div>
            </div>
        @empty
            <x-admin.empty icon="users" title="لا توجد حسابات مطابقة" desc="جرّب تعديل كلمة البحث أو أضف مستخدمًا جديدًا." />
        @endforelse
    </div>
</div>
@endsection
