<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-lg sm:text-2xl text-gray-800 dark:text-gray-200 leading-tight">
                🎴 Gerenciar Baralhos Anki
            </h2>
            <button onclick="deduplicateDecks()" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 font-semibold text-sm">
                🔄 Remover Duplicatas
            </button>
        </div>
    </x-slot>

    <input type="hidden" name="_token" value="{{ csrf_token() }}">

    <div class="py-12">
        <div class="max-w-6xl mx-auto px-4">
            <!-- Search -->
            <div class="mb-6">
                <input type="text" id="searchInput" placeholder="🔍 Buscar cards..." 
                       class="w-full px-4 py-2 bg-[#1a8eff] text-white placeholder-gray-200 border border-gray-300 rounded-lg"
                       onkeyup="searchCards(this.value)">
                <div id="searchResults" class="hidden mt-2 bg-gray-700 border border-gray-600 rounded-lg max-h-64 overflow-y-auto"></div>
            </div>

            <!-- Cards Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @forelse($decks as $deck)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 border-l-4 border-indigo-600">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $deck->name }}</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    📚 {{ $deck->subModule->module->course->title }} 
                                    › {{ $deck->subModule->module->title }}
                                </p>
                            </div>
                            <span class="px-3 py-1 bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200 rounded-full text-xs font-semibold">
                                {{ $deck->cards_count }} cards
                            </span>
                        </div>

                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-4 space-y-1">
                            <p>📁 {{ basename($deck->file_path) }}</p>
                            @if($deck->audio_path)
                                <p>🎵 {{ basename($deck->audio_path) }}</p>
                            @endif
                        </div>

                        <div class="flex gap-2">
                            <a href="{{ route('anki.management.edit-deck', $deck) }}" 
                               class="flex-1 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded text-sm font-semibold text-center transition-colors">
                                ✏️ Editar
                            </a>
                            <button onclick="deleteDeck({{ $deck->id }})" 
                                    class="flex-1 px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded text-sm font-semibold transition-colors">
                                🗑️ Deletar
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-12">
                        <p class="text-gray-600 dark:text-gray-400 text-lg">Nenhum baralho encontrado</p>
                        <a href="{{ route('anki.index') }}" class="mt-4 inline-block px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                            ← Voltar ao Dashboard
                        </a>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $decks->links() }}
            </div>
        </div>
    </div>

    <script>
        // Deletar deck
        function deleteDeck(deckId) {
            if (confirm('Tem certeza que deseja deletar este baralho? Todos os cards serão removidos!')) {
                fetch(`/anki/management/decks/${deckId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-Token': document.querySelector('input[name="_token"]').value,
                    },
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Erro: ' + data.message);
                    }
                })
                .catch(err => alert('Erro ao deletar: ' + err));
            }
        }

        // Remover duplicatas
        function deduplicateDecks() {
            if (confirm('Isso vai remover todos os baralhos duplicados! Continuar?')) {
                fetch('{{ route("anki.management.deduplicate") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-Token': document.querySelector('input[name="_token"]').value,
                    },
                })
                .then(res => res.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) location.reload();
                })
                .catch(err => alert('Erro: ' + err));
            }
        }

        // Buscar cards
        function searchCards(query) {
            const resultsDiv = document.getElementById('searchResults');
            
            if (query.length < 2) {
                resultsDiv.classList.add('hidden');
                return;
            }

            fetch(`/anki/management/search?q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.results.length === 0) {
                        resultsDiv.innerHTML = '<p class="p-3 text-gray-400">Nenhum card encontrado</p>';
                    } else {
                        resultsDiv.innerHTML = data.results.map(card => `
                            <div class="p-3 border-b border-gray-600 hover:bg-gray-650 cursor-pointer" 
                                 onclick="location.href='/anki/management/cards/${card.id}/edit'">
                                <div class="text-sm font-semibold text-white">${card.front}</div>
                                <div class="text-xs text-gray-400 mt-1">${card.back}</div>
                                <div class="text-xs text-indigo-400 mt-1">📚 ${card.deck}</div>
                            </div>
                        `).join('');
                    }
                    resultsDiv.classList.remove('hidden');
                });
        }
    </script>
</x-app-layout>
