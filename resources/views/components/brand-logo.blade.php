{{-- شعار برنامج خادم الحرمين الشريفين للابتعاث — light للخلفيات الداكنة --}}
@props(['light' => false, 'emblem' => false])
@php
    $file = ($emblem ? 'kasp-emblem' : 'kasp-logo').($light ? '-light' : '').'.png';
@endphp
<img src="{{ asset('images/brand/'.$file) }}"
     alt="{{ $emblem ? '' : 'شعار برنامج خادم الحرمين الشريفين للابتعاث' }}"
     @if ($emblem) aria-hidden="true" @endif
     draggable="false" decoding="async"
     {{ $attributes->class('w-auto select-none') }}>
