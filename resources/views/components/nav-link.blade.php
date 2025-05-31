@props(['active'])

@php
$classes = ($active ?? false)
    ? 'flex items-center w-full px-6 py-3 transition-all duration-200 text-gray-300 hover:bg-gray-800/50 hover:text-white bg-gray-800/50 text-white'
    : 'flex items-center w-full px-6 py-3 transition-all duration-200 text-gray-300 hover:bg-gray-800/50 hover:text-white';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
