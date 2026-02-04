@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-alura-accent text-sm font-medium leading-5 text-alura-text focus:outline-none focus:border-alura-accent-hover transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-alura-text-muted hover:text-alura-text hover:border-alura-accent/30 focus:outline-none focus:text-alura-text focus:border-alura-accent/50 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
