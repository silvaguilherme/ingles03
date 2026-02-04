@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-alura-text']) }}>
    {{ $value ?? $slot }}
</label>
