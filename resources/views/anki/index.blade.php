<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between py-2">
            <h2 class="font-semibold text-sm text-alura-text leading-tight">
                🎴 Dashboard Anki
            </h2>
            <div class="flex gap-3">
                <a href="{{ route('anki.management.decks') }}" class="text-xs text-alura-accent hover:text-alura-accent-hover underline">
                    ⚙️ Gerenciar
                </a>
                <a href="{{ route('anki.status') }}" class="text-xs text-alura-accent hover:text-alura-accent-hover underline">
                    🔧 Debug
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-2 bg-alura-dark">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Estatísticas Rápidas -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-1 mb-2">
                <div class="alura-card rounded shadow p-1 border-l-2 border-blue-500">
                    <div class="flex items-center justify-between gap-1">
                        <div class="min-w-0">
                            <p class="text-alura-text-muted text-xs font-medium">Cards</p>
                            <p class="text-xs font-bold text-alura-text mt-0">{{ $totalCards }}</p>
                        </div>
                        <svg class="w-4 h-4 text-blue-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>

                <div class="alura-card rounded shadow p-1 border-l-2 border-green-500">
                    <div class="flex items-center justify-between gap-1">
                        <div class="min-w-0">
                            <p class="text-alura-text-muted text-xs font-medium">Estudados</p>
                            <p class="text-xs font-bold text-alura-text mt-0">{{ $cardsStudied }}</p>
                        </div>
                        <svg class="w-4 h-4 text-green-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>

                <div class="alura-card rounded shadow p-1 border-l-2 border-orange-500">
                    <div class="flex items-center justify-between gap-1">
                        <div class="min-w-0">
                            <p class="text-alura-text-muted text-xs font-medium">Revisar</p>
                            <p class="text-xs font-bold text-alura-text mt-0">{{ $cardsDueReview }}</p>
                        </div>
                        <svg class="w-4 h-4 text-orange-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v2h8v-2zM2 15a4 4 0 018 0v2H2v-2z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>

                <div class="alura-card rounded shadow p-1 border-l-2 border-purple-500">
                    <div class="flex items-center justify-between gap-1">
                        <div class="min-w-0">
                            <p class="text-alura-text-muted text-xs font-medium">Decks</p>
                            <p class="text-xs font-bold text-alura-text mt-0">{{ count($decksWithProgress) }}</p>
                        </div>
                        <svg class="w-4 h-4 text-purple-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.669 0-3.218.51-4.5 1.385A7.968 7.968 0 009 4.804z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Decks -->
            @if($decksWithProgress->isNotEmpty())
                <div class="mb-2">
                    <h2 class="text-sm font-bold text-alura-text mb-2">Meus Decks</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($decksWithProgress as $item)
                            <div class="alura-card rounded shadow overflow-hidden hover:shadow-lg transition-shadow">
                                <div class="bg-gradient-to-r from-alura-accent to-blue-600 px-4 py-2">
                                    <h3 class="text-sm font-bold text-white">{{ $item['deck']->name }}</h3>
                                    <p class="text-blue-100 text-xs line-clamp-1">
                                        {{ $item['deck']->submodule->module->title }} > {{ $item['deck']->submodule->title }}
                                    </p>
                                </div>
                                
                                <div class="p-3">
                                    <!-- Progresso -->
                                    <div class="mb-3">
                                        <div class="flex justify-between items-center mb-1">
                                            <span class="text-xs font-medium text-alura-text-muted">Progresso</span>
                                            <span class="text-xs font-bold text-alura-accent">{{ $item['progress_percentage'] }}%</span>
                                        </div>
                                        <div class="w-full bg-alura-darker rounded-full h-1.5">
                                            <div class="bg-alura-accent h-1.5 rounded-full transition-all" style="width: {{ $item['progress_percentage'] }}%"></div>
                                        </div>
                                    </div>

                                    <!-- Estatísticas -->
                                    <div class="space-y-1 mb-3">
                                        <div class="flex justify-between text-xs">
                                            <span class="text-alura-text-muted">Total:</span>
                                            <span class="font-semibold text-alura-text">{{ $item['total_cards'] }}</span>
                                        </div>
                                        <div class="flex justify-between text-xs">
                                            <span class="text-alura-text-muted">Aprendidos:</span>
                                            <span class="font-semibold text-green-400">{{ $item['learned_cards'] }}</span>
                                        </div>
                                        <div class="flex justify-between text-xs">
                                            <span class="text-alura-text-muted">Revisar:</span>
                                            <span class="font-semibold text-orange-400">{{ $item['due_cards'] }}</span>
                                        </div>
                                    </div>

                                    <!-- Botão Estudar -->
                                    <a href="{{ route('anki.study', $item['deck']) }}" 
                                       class="block w-full bg-alura-accent hover:bg-alura-accent-hover text-white font-semibold py-1.5 px-3 rounded text-center transition-colors text-sm">
                                        Estudar
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="alura-card rounded shadow p-4 text-center">
                    <svg class="w-8 h-8 text-alura-text-muted mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="text-xs font-semibold text-alura-text mb-1">Nenhum Deck encontrado</h3>
                    <p class="text-alura-text-muted text-xs mb-2">Escolha uma opção abaixo:</p>
                    <div class="flex gap-2 justify-center flex-wrap">
                        <a href="{{ route('anki.import-page') }}" class="inline-block bg-alura-accent hover:bg-alura-accent-hover text-white font-semibold py-1 px-3 rounded text-xs transition-colors">
                            🎴 Importar
                        </a>
                        <a href="{{ route('courses.index') }}" class="inline-block bg-alura-card hover:bg-alura-darker text-alura-text font-semibold py-1 px-3 rounded text-xs transition-colors border border-alura-accent/30">
                            ← Voltar
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
