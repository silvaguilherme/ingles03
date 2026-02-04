@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-alura-accent text-start text-base font-medium text-alura-text bg-alura-card/50 focus:outline-none focus:text-alura-accent focus:bg-alura-card focus:border-alura-accent transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-alura-text-muted hover:text-alura-text hover:bg-alura-card/30 hover:border-alura-accent/30 focus:outline-none focus:text-alura-accent focus:bg-alura-card focus:border-alura-accent transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
