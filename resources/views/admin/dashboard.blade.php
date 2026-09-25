@extends('layouts.admin')
@section('page-title', 'نظرة عامة')

@php
    $me = auth()->user();
    $cats = config('kasp.news_categories');
    $stats = [
        ['icon' => 'newspaper', 'label' => 'أخبار منشورة', 'value' => $sectionCounts['news'], 'route' => 'admin.news.index'],
        ['icon' => 'book-open-text', 'label' => 'إجابات المعرفة', 'value' => $sectionCounts['kb'], 'route' => 'admin.kb.index'],
        ['icon' => 'inbox', 'label' => 'أسئلة بلا إجابة', 'value' => $sectionCounts['pending'], 'route' => 'admin.pending.index', 'warn' => $sectionCounts['pending'] > 0],
        ['icon' => 'globe', 'label' => 'جامعة معتمدة', 'value' => $sectionCounts['universities'], 'route' => 'admin.universities.index'],
        ['icon' => 'graduation-cap', 'label' => 'مسار دراسي', 'value' => $sectionCounts['tracks'], 'route' => 'admin.tracks.index'],
        ['icon' => 'route', 'label' => 'محطات الرحلة', 'value' => $sectionCounts['stations'], 'route' => 'admin.stations.index'],
        ['icon' => 'cloud-upload', 'label' => 'ملفات مرفوعة', 'value' => $sectionCounts['files'], 'route' => 'admin.files.index'],
    ];
    $maxCat = max(1, $categoryCounts->max('count'));
    $quick = [
        ['icon' => 'file-plus-2', 'label' => 'خبر جديد', 'route' => 'admin.news.index'],
        ['icon' => 'wand-sparkles', 'label' => 'إجابة معرفية', 'route' => 'admin.kb.index'],
        ['icon' => 'globe', 'label' => 'جامعة جديدة', 'route' => 'admin.universities.index'],
        ['icon' => 'cloud-upload', 'label' => 'رفع ملف', 'route' => 'admin.files.index'],
    ];
@endphp

@section('admin')
<div class="space-y-8">
    {{-- بطاقة الترحيب والصلاحية --}}
    <div class="flex flex-wrap items-center justify-between gap-4 rounded-3xl border border-forest-800/12 bg-white p-5 shadow-sm">
        <div class="flex items-center gap-4">
            <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-forest-800 font-plex text-lg font-bold text-gold-300">{{ $me->initial() }}</span>
            <div class="leading-tight">
                <p class="text-sm font-bold text-ink">أهلًا بك، {{ $me->name }}</p>
                <p dir="ltr" class="mt-1 text-right font-plex text-[11px] text-slate-400">{{ $me->email }}</p>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <span class="inline-flex items-center gap-1.5 rounded-full border border-gold-500/50 bg-gold-500/10 px-4 py-2 text-xs font-bold text-gold-700">
                <x-lucide-database class="size-3.5" />
                صلاحيتك: {{ $me->roleLabel() }}
            </span>
            @can('manage-users')
                <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 rounded-full bg-forest-800 px-5 py-2.5 text-xs font-bold text-gold-300 transition-all hover:bg-forest-700">
                    إدارة المستخدمين ({{ $sectionCounts['users'] }})
                </a>
            @endcan
        </div>
    </div>

    {{-- الإحصاءات --}}
    <div class="grid grid-cols-2 gap-3.5 md:grid-cols-4 xl:grid-cols-7">
        @foreach ($stats as $s)
            <a href="{{ route($s['route']) }}" @class([
                'group rounded-2xl border bg-white p-4 text-start shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md',
                'border-amber-400/60' => $s['warn'] ?? false,
                'border-forest-800/12 hover:border-gold-500/50' => ! ($s['warn'] ?? false),
            ])>
                @svg('lucide-'.$s['icon'], 'size-5 '.(($s['warn'] ?? false) ? 'text-amber-500' : 'text-gold-600'))
                <div class="mt-2.5 font-plex text-2xl font-bold text-ink" dir="ltr">{{ $s['value'] }}</div>
                <div class="mt-0.5 text-[11px] font-bold text-slate-500 group-hover:text-forest-700">{{ $s['label'] }}</div>
            </a>
        @endforeach
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- توزيع الأخبار --}}
        <div class="rounded-3xl border border-forest-800/12 bg-white p-6 shadow-sm">
            <h2 class="text-sm font-bold text-ink">توزيع الأخبار بحسب التصنيف</h2>
            <div class="mt-5 space-y-4">
                @foreach ($categoryCounts as $c)
                    <div>
                        <div class="flex items-center justify-between text-xs font-bold">
                            <span class="text-slate-600">{{ $c['label'] }}</span>
                            <span class="font-plex text-forest-700" dir="ltr">{{ $c['count'] }}</span>
                        </div>
                        <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-sand">
                            <div class="h-full rounded-full bg-gradient-to-l from-forest-700 to-gold-500" style="width: {{ ($c['count'] / $maxCat) * 100 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6 flex items-center gap-2.5 rounded-2xl border border-emerald-300 bg-emerald-50 p-3.5">
                <x-lucide-database class="size-5 text-emerald-600" />
                <div class="text-xs leading-relaxed">
                    <div class="font-bold text-slate-700">قاعدة البيانات متصلة <span dir="ltr" class="font-plex">({{ $dbDriver }})</span></div>
                    <div class="text-slate-500">كل تعديل يُحفظ مباشرة ويظهر فورًا في الواجهة العامة.</div>
                </div>
            </div>
        </div>

        {{-- أحدث الأخبار --}}
        <div class="rounded-3xl border border-forest-800/12 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-bold text-ink">أحدث الأخبار</h2>
                <a href="{{ route('admin.news.index') }}" class="text-xs font-bold text-gold-600 hover:text-gold-700">عرض الكل</a>
            </div>
            <div class="mt-4 space-y-3">
                @forelse ($latestNews as $n)
                    <a href="{{ route('admin.news.index', ['edit' => $n->id]) }}" class="block w-full rounded-2xl border border-forest-800/10 p-3.5 text-start transition-all hover:border-gold-500/40 hover:bg-forest-50/50">
                        <div class="flex items-center gap-2">
                            <span class="size-2 rounded-full {{ $cats[$n->category]['dotClass'] }}"></span>
                            <span class="font-plex text-[10px] font-bold text-slate-400" dir="ltr">{{ $n->published_on->format('Y-m-d') }}</span>
                        </div>
                        <p class="mt-1.5 line-clamp-2 text-[13px] font-bold leading-relaxed text-ink">{{ $n->title }}</p>
                    </a>
                @empty
                    <p class="rounded-xl bg-sand/70 p-4 text-center text-xs font-bold text-slate-500">لا توجد أخبار منشورة</p>
                @endforelse
            </div>
        </div>

        <div class="space-y-6">
            {{-- الأسئلة المعلقة --}}
            <div class="rounded-3xl border border-forest-800/12 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold text-ink">أسئلة بلا إجابة</h2>
                    <a href="{{ route('admin.pending.index') }}" class="text-xs font-bold text-gold-600 hover:text-gold-700">إدارة</a>
                </div>
                <div class="mt-4 space-y-2.5">
                    @forelse ($pending as $u)
                        <div class="flex items-start gap-2.5 rounded-xl bg-sand/70 p-3">
                            <x-lucide-inbox class="mt-0.5 size-4 shrink-0 text-gold-600" />
                            <p class="line-clamp-2 text-[13px] font-semibold leading-relaxed text-slate-700">{{ $u->question }}</p>
                        </div>
                    @empty
                        <p class="rounded-xl bg-sand/70 p-4 text-center text-xs font-bold text-slate-500">صندوق نظيف — لا توجد أسئلة معلّقة</p>
                    @endforelse
                </div>
            </div>

            {{-- إجراءات سريعة --}}
            @can('edit-content')
                <div class="rounded-3xl border border-forest-800/12 bg-white p-6 shadow-sm">
                    <h2 class="text-sm font-bold text-ink">إجراءات سريعة</h2>
                    <div class="mt-4 grid grid-cols-2 gap-2.5">
                        @foreach ($quick as $a)
                            <a href="{{ route($a['route']) }}"
                               class="flex items-center gap-2.5 rounded-2xl border border-forest-800/12 bg-sand/60 px-4 py-3 text-xs font-bold text-ink transition-all hover:-translate-y-0.5 hover:border-gold-500/60 hover:bg-white hover:text-gold-600 hover:shadow-md">
                                @svg('lucide-'.$a['icon'], 'size-4 shrink-0 text-gold-600')
                                {{ $a['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endcan
        </div>
    </div>
</div>
@endsection
