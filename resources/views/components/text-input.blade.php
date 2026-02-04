@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-700 bg-alura-card text-alura-text placeholder-alura-text-muted focus:border-alura-accent focus:ring-alura-accent rounded-md shadow-sm']) }}>
