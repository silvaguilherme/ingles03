<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg sm:text-2xl text-gray-800 dark:text-gray-200 leading-tight">
            Teste de Diagnóstico Anki
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-8">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">
                    ✅ Página de Diagnóstico Carregada com Sucesso!
                </h3>

                <div class="space-y-4 mb-8">
                    <p class="text-gray-700 dark:text-gray-300">
                        ✓ Layout está funcionando
                    </p>
                    <p class="text-gray-700 dark:text-gray-300">
                        ✓ Blade templates estão funcionando
                    </p>
                    <p class="text-gray-700 dark:text-gray-300">
                        ✓ Router está funcionando
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <a href="{{ route('anki.status') }}" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg text-center">
                        Ir para Debug Status
                    </a>
                    <a href="{{ route('anki.index') }}" class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-bold rounded-lg text-center">
                        Voltar ao Dashboard Anki
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
