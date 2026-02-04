<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-alura-accent border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-alura-accent-hover focus:bg-alura-accent-hover active:bg-alura-accent focus:outline-none focus:ring-2 focus:ring-alura-accent focus:ring-offset-2 focus:ring-offset-alura-dark transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
