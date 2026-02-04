@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-green-400 bg-green-500/10 border border-green-500/30 rounded-lg p-3']) }}>
        {{ $status }}
    </div>
@endif
