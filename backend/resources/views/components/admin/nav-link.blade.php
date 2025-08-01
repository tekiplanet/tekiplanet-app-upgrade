@props(['active' => false, 'icon' => null])

@php
$classes = $active
    ? 'bg-gray-800 text-white group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold'
    : 'text-gray-400 hover:text-white hover:bg-gray-800 group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold';
@endphp

<li>
    <a {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            @svg('heroicon-o-'.$icon, 'h-6 w-6 shrink-0')
        @endif
        {{ $slot }}
    </a>
</li> 