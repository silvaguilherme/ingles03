<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-lg sm:text-2xl text-gray-800 dark:text-gray-200 leading-tight">
                Meu Dashboard Anki
            </h2>
            <a href="{{ route('anki.status') }}" class="text-xs text-indigo-600 hover:text-indigo-500 underline">
                🔧 Debug
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4">
            <!-- Estatísticas Rápidas -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-12">
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm font-medium">Total de Cards</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalCards }}</p>
                        </div>
                        <svg class="w-12 h-12 text-blue-200" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm font-medium">Cards Estudados</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $cardsStudied }}</p>
                        </div>
                        <svg class="w-12 h-12 text-green-200" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-orange-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm font-medium">Prontos para Revisar</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $cardsDueReview }}</p>
                        </div>
                        <svg class="w-12 h-12 text-orange-200" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v2h8v-2zM2 15a4 4 0 018 0v2H2v-2z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm font-medium">Decks Ativos</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">{{ count($decksWithProgress) }}</p>
                        </div>
                        <svg class="w-12 h-12 text-purple-200" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.669 0-3.218.51-4.5 1.385A7.968 7.968 0 009 4.804z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Decks -->
        @if($decksWithProgress->isNotEmpty())
            <div class="mb-12">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Meus Decks</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($decksWithProgress as $item)
                        <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow">
                            <div class="bg-gradient-to-r from-indigo-500 to-blue-600 px-6 py-4">
                                <h3 class="text-xl font-bold text-white">{{ $item['deck']->name }}</h3>
                                <p class="text-indigo-100 text-sm">
                                    {{ $item['deck']->submodule->module->title }} > {{ $item['deck']->submodule->title }}
                                </p>
                            </div>
                            
                            <div class="p-6">
                                <!-- Progresso -->
                                <div class="mb-6">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-sm font-medium text-gray-600">Progresso</span>
                                        <span class="text-sm font-bold text-indigo-600">{{ $item['progress_percentage'] }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-indigo-600 h-2 rounded-full transition-all" style="width: {{ $item['progress_percentage'] }}%"></div>
                                    </div>
                                </div>

                                <!-- Estatísticas -->
                                <div class="space-y-2 mb-6">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Total de Cards:</span>
                                        <span class="font-semibold text-gray-900">{{ $item['total_cards'] }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Aprendidos:</span>
                                        <span class="font-semibold text-green-600">{{ $item['learned_cards'] }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Prontos para Revisar:</span>
                                        <span class="font-semibold text-orange-600">{{ $item['due_cards'] }}</span>
                                    </div>
                                </div>

                                <!-- Botão Estudar -->
                                <a href="{{ route('anki.study', $item['deck']) }}" 
                                   class="block w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg text-center transition-colors">
                                    Estudar Deck
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Nenhum Deck encontrado</h3>
                <p class="text-gray-600 mb-6">Você ainda não tem decks de Anki. Escolha uma opção abaixo:</p>
                <div class="flex gap-4 justify-center flex-wrap">
                    <a href="{{ route('anki.import-page') }}" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-6 rounded-lg transition-colors">
                        🎴 Importar de Pastas
                    </a>
                    <a href="{{ route('courses.index') }}" class="inline-block bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-6 rounded-lg transition-colors">
                        ← Voltar aos Cursos
                    </a>
                </div>
            </div>
        @endif
        </div>
    </div>
</x-app-layout>
