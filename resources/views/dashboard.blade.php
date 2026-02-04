<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-alura-text leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="alura-card p-8">
                <h3 class="text-xl font-semibold text-alura-text mb-4">Bem-vindo ao seu Curso!</h3>
                <p class="text-alura-text-muted">
                    {{ __("Comece a explorar os cursos disponíveis e continue seu aprendizado.") }}
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
