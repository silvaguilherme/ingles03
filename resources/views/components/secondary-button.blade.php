<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-alura-card border border-gray-600 rounded-md font-semibold text-xs text-alura-text uppercase tracking-widest shadow-sm hover:bg-gray-700 hover:border-alura-accent/30 focus:outline-none focus:ring-2 focus:ring-alura-accent focus:ring-offset-2 focus:ring-offset-alura-dark disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
