@props(['name'])
@error($name)
    <p class="mt-1.5 text-[11px] font-bold text-red-500">{{ $message }}</p>
@enderror
