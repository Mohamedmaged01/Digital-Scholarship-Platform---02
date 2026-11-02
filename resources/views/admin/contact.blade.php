@extends('layouts.admin')
@section('page-title', 'وسائل الاتصال')

@php
    $e = $editing;
    $icons = ['phone' => 'هاتف', 'mail' => 'بريد إلكتروني', 'chat' => 'محادثة / واتساب', 'map-pin' => 'عنوان', 'globe' => 'موقع إلكتروني'];
@endphp

@section('admin')
<div class="grid gap-8 lg:grid-cols-[380px_1fr]">
    <form method="POST" action="{{ $e ? route('admin.contact.update', $e) : route('admin.contact.store') }}"
          class="h-fit rounded-3xl border border-forest-800/12 bg-white p-5 shadow-sm lg:sticky lg:top-24" novalidate>
        @csrf
        @if ($e) @method('PUT') @endif
        <fieldset @disabled(auth()->user()->cannot('edit-content')) class="space-y-3.5">
            <h2 class="flex items-center gap-2 text-sm font-bold text-ink">
                @if ($e) <x-lucide-pencil class="size-4 text-gold-600" /> تعديل وسيلة الاتصال
                @else <x-lucide-plus class="size-4 text-gold-600" /> إضافة وسيلة اتصال @endif
            </h2>
            <div>
                <label for="label" class="dash-label">المسمى</label>
                <input id="label" name="label" value="{{ old('label', $e?->label) }}" class="dash-input" placeholder="الرقم الموحّد" required>
                <x-admin.error name="label" />
            </div>
            <div>
                <label for="value" class="dash-label">القيمة المعروضة</label>
                <input id="value" name="value" dir="ltr" value="{{ old('value', $e?->value) }}" class="dash-input text-left" placeholder="19996" required>
                <x-admin.error name="value" />
            </div>
            <div>
                <label for="href" class="dash-label">رابط الاتصال</label>
                <input id="href" name="href" dir="ltr" value="{{ old('href', $e?->href) }}" class="dash-input text-left" placeholder="tel:19996 أو mailto:… أو https://…">
                <p class="mt-1.5 text-[11px] leading-relaxed text-slate-400">اتركه فارغًا ليُنشأ تلقائيًا من القيمة: البريد الإلكتروني ← mailto، الهاتف ← tel، الموقع ← https.</p>
                <x-admin.error name="href" />
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="icon" class="dash-label">النوع والأيقونة</label>
                    <select id="icon" name="icon" class="dash-input appearance-none">
                        @foreach ($icons as $key => $label)
                            <option value="{{ $key }}" @selected(old('icon', $e?->icon ?? 'phone') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="sort" class="dash-label">الترتيب</label>
                    <input id="sort" name="sort" type="number" min="0" dir="ltr" value="{{ old('sort', $e?->sort ?? 0) }}" class="dash-input">
                </div>
            </div>
            <x-admin.form-actions :editing="(bool) $e" create-label="إضافة" :cancel="route('admin.contact.index')" />
        </fieldset>
    </form>

    <div class="space-y-3.5">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="flex items-center gap-2 text-sm font-bold text-forest-800"><x-lucide-phone class="size-4 text-gold-600" /> وسائل الاتصال ({{ $items->count() }})</h2>
            @can('delete-content')
                <form method="POST" action="{{ route('admin.contact.reset') }}" onsubmit="return confirm('ستُستبدل وسائل الاتصال الحالية بالافتراضية. متابعة؟')">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-full border border-forest-800/15 px-4 py-2.5 text-xs font-bold text-slate-500 transition-all hover:bg-sand">
                        <x-lucide-rotate-ccw class="size-3.5" /> استعادة الافتراضي
                    </button>
                </form>
            @endcan
        </div>
        <p class="text-[12px] text-slate-500">تظهر في تذييل المنصة وفي ردّ المساعد الذكي حين لا يجد إجابة.</p>
        @forelse ($items as $c)
            <div @class(['flex items-center gap-3 rounded-2xl border bg-white p-4 shadow-sm', 'border-gold-500 ring-4 ring-gold-500/20' => $e?->id === $c->id, 'border-forest-800/12' => $e?->id !== $c->id])>
                <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-forest-800 text-gold-300">@svg('lucide-'.$c->lucideIcon(), 'size-4.5')</span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-bold text-ink">{{ $c->label }}</p>
                    <p class="mt-0.5 text-right font-plex text-[12px] text-slate-400" dir="ltr">{{ $c->value }}</p>
                </div>
                <x-admin.edit-link :href="route('admin.contact.index', ['edit' => $c->id])" />
                <x-admin.delete-button :action="route('admin.contact.destroy', $c)" />
            </div>
        @empty
            <x-admin.empty icon="phone" title="لا توجد وسائل اتصال" />
        @endforelse
    </div>
</div>

{{-- ══════ مواقع التواصل الاجتماعي ══════ --}}
@php $se = $editingSocial; @endphp
<div id="social" class="mt-10 grid gap-8 border-t border-forest-800/10 pt-10 lg:grid-cols-[380px_1fr]">
    <form method="POST" action="{{ $se ? route('admin.social.update', $se) : route('admin.social.store') }}"
          class="h-fit rounded-3xl border border-forest-800/12 bg-white p-5 shadow-sm" novalidate>
        @csrf
        @if ($se) @method('PUT') @endif
        <fieldset @disabled(auth()->user()->cannot('edit-content')) class="space-y-3.5">
            <h2 class="flex items-center gap-2 text-sm font-bold text-ink">
                @if ($se) <x-lucide-pencil class="size-4 text-gold-600" /> تعديل حساب التواصل
                @else <x-lucide-plus class="size-4 text-gold-600" /> إضافة حساب تواصل اجتماعي @endif
            </h2>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="platform" class="dash-label">المنصة والأيقونة</label>
                    <select id="platform" name="platform" class="dash-input appearance-none">
                        @foreach (config('kasp.social_platforms') as $key => $label)
                            <option value="{{ $key }}" @selected(old('platform', $se?->platform ?? 'x') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-admin.error name="platform" />
                </div>
                <div>
                    <label for="social-name" class="dash-label">الاسم</label>
                    <input id="social-name" name="name" value="{{ old('name', $se?->name) }}" class="dash-input" placeholder="X" required>
                    <x-admin.error name="name" />
                </div>
            </div>
            <div>
                <label for="url" class="dash-label">رابط الحساب</label>
                <input id="url" name="url" dir="ltr" value="{{ old('url', $se?->url) }}" class="dash-input text-left" placeholder="https://x.com/…" required>
                <x-admin.error name="url" />
            </div>
            <div class="w-1/2 pe-1.5">
                <label for="social-sort" class="dash-label">الترتيب</label>
                <input id="social-sort" name="sort" type="number" min="0" dir="ltr" value="{{ old('sort', $se?->sort ?? ($socials->max('sort') + 1)) }}" class="dash-input">
            </div>
            <x-admin.form-actions :editing="(bool) $se" create-label="إضافة" :cancel="route('admin.contact.index').'#social'" />
        </fieldset>
    </form>

    <div class="space-y-3.5">
        <h2 class="flex items-center gap-2 text-sm font-bold text-forest-800"><x-lucide-share-2 class="size-4 text-gold-600" /> مواقع التواصل الاجتماعي ({{ $socials->count() }})</h2>
        <p class="text-[12px] text-slate-500">تظهر أيقونات أسفل وسائل الاتصال في تذييل المنصة، وتفتح في نافذة جديدة.</p>
        @forelse ($socials as $s)
            <div @class(['flex items-center gap-3 rounded-2xl border bg-white p-4 shadow-sm', 'border-gold-500 ring-4 ring-gold-500/20' => $se?->id === $s->id, 'border-forest-800/12' => $se?->id !== $s->id])>
                <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-forest-800 text-gold-300"><x-social-icon :platform="$s->platform" class="size-4.5" /></span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-bold text-ink">{{ $s->name }}</p>
                    <p class="mt-0.5 truncate text-right font-plex text-[12px] text-slate-400" dir="ltr">{{ $s->url }}</p>
                </div>
                <x-admin.edit-link :href="route('admin.contact.index', ['social' => $s->id]).'#social'" />
                <x-admin.delete-button :action="route('admin.social.destroy', $s)" />
            </div>
        @empty
            <x-admin.empty icon="share-2" title="لا توجد حسابات تواصل" />
        @endforelse
    </div>
</div>
@endsection
