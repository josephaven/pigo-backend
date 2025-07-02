@props(['active'])

@php
    $classes = ($active ?? false)
                ? 'block px-4 py-2 bg-[#143E47] rounded hover:bg-[#102f35] font-semibold'
                : 'block px-4 py-2 hover:bg-[#143E47] rounded';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
