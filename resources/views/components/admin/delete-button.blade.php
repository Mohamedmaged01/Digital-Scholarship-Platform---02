{{-- زر حذف يظهر للمدير فقط، مع تأكيد قبل التنفيذ --}}
@props(['action', 'confirm' => 'هل أنت متأكد من الحذف؟ لا يمكن التراجع عن هذا الإجراء.', 'size' => 'size-9'])
@can('delete-content')
    <form method="POST" action="{{ $action }}" onsubmit="return confirm(@js($confirm))">
        @csrf
        @method('DELETE')
        <button type="submit" aria-label="حذف" title="حذف"
                class="grid {{ $size }} place-items-center rounded-full border border-red-200 text-red-500 transition-all hover:bg-red-50">
            <x-lucide-trash-2 class="size-4" />
        </button>
    </form>
@endcan
