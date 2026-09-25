@props(['href'])
@can('edit-content')
    <a href="{{ $href }}" aria-label="تعديل" title="تعديل"
       class="grid size-9 place-items-center rounded-full border border-forest-800/15 text-forest-700 transition-all hover:bg-forest-50">
        <x-lucide-pencil class="size-4" />
    </a>
@endcan
