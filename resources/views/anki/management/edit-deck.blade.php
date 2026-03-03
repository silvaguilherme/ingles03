<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-lg sm:text-2xl text-gray-800 dark:text-gray-200 leading-tight">
                ✏️ Editar Baralho: {{ $deck->name }}
            </h2>
            <a href="{{ route('anki.management.decks') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                ← Voltar
            </a>
        </div>
    </x-slot>

    <input type="hidden" name="_token" value="{{ csrf_token() }}">

    <div class="py-12">
        <div class="max-w-6xl mx-auto px-4">
            <!-- Editar Nome do Deck -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-8">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">📋 Informações do Baralho</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Nome do Baralho
                        </label>
                        <input type="text" id="deckName" value="{{ $deck->name }}" 
                               class="w-full px-4 py-2 bg-[#1a8eff] text-white border border-gray-300 rounded-lg">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Caminho do Arquivo
                        </label>
                        <input type="text" value="{{ $deck->file_path }}" disabled 
                               class="w-full px-4 py-2 bg-gray-600 text-gray-300 border border-gray-500 rounded-lg">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            📚 Módulo
                        </label>
                        <input type="text" 
                               value="{{ $deck->subModule->module->course->title }} › {{ $deck->subModule->module->title }}" 
                               disabled 
                               class="w-full px-4 py-2 bg-gray-600 text-gray-300 border border-gray-500 rounded-lg">
                    </div>

                    <button onclick="updateDeck()" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg">
                        💾 Salvar Alterações
                    </button>

                    <button onclick="cleanErrorCards()" class="w-full px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-lg">
                        🧹 Limpar cards com erro do Anki
                    </button>
                </div>
            </div>

            <!-- Lista de Cards -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                        🎴 Cards ({{ $cards->total() }})
                    </h3>

                    <form method="GET" action="{{ route('anki.management.edit-deck', $deck) }}" class="inline-flex items-center gap-2">
                        <label for="without_audio" class="text-sm font-semibold text-gray-700 dark:text-gray-300">Somente sem áudio</label>
                        <input
                            id="without_audio"
                            type="checkbox"
                            name="without_audio"
                            value="1"
                            {{ ($withoutAudioOnly ?? false) ? 'checked' : '' }}
                            onchange="this.form.submit()"
                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                        >
                    </form>
                </div>

                <div class="space-y-4 max-h-96 overflow-y-auto">
                    @forelse($cards as $card)
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg border border-gray-200 dark:border-gray-600 hover:border-indigo-400 transition">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1">
                                    <div class="font-semibold text-gray-900 dark:text-gray-100 mb-2">
                                        Front: {{ substr($card->front, 0, 100) }}{{ strlen($card->front) > 100 ? '...' : '' }}
                                    </div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                        Back: {{ substr($card->back, 0, 100) }}{{ strlen($card->back) > 100 ? '...' : '' }}
                                    </div>
                                    @php
                                        $hasAudio = str_contains(strtolower((string) $card->front), '<audio')
                                            || str_contains(strtolower((string) $card->back), '<audio')
                                            || str_contains(strtolower((string) ($card->extra ?? '')), '<audio');
                                    @endphp
                                    @if($hasAudio)
                                        <div class="text-xs text-green-600 dark:text-green-400">
                                            🎵 Com áudio
                                        </div>
                                    @else
                                        <div class="text-xs text-gray-500">🔇 Sem áudio</div>
                                    @endif
                                </div>
                                <div class="flex gap-2 ml-4">
                                    <a href="{{ route('anki.management.edit-card', $card) }}" 
                                       class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded transition">
                                        ✏️
                                    </a>
                                    <button onclick="deleteCard({{ $card->id }})" 
                                            class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded transition">
                                        🗑️
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-600 dark:text-gray-400 text-center py-6">Nenhum card neste baralho</p>
                    @endforelse
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $cards->links() }}
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateDeck() {
            const name = document.getElementById('deckName').value;
            
            if (!name.trim()) {
                alert('Digite um nome para o baralho');
                return;
            }

            fetch(`/anki/management/decks/{{ $deck->id }}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('input[name="_token"]').value,
                },
                body: JSON.stringify({ name })
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
            .catch(err => alert('Erro: ' + err));
        }

        function deleteCard(cardId) {
            if (confirm('Deseja deletar este card?')) {
                fetch(`/anki/management/cards/${cardId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-Token': document.querySelector('input[name="_token"]').value,
                    },
                })
                .then(res => res.json())
                .then(data => {
                    alert(data.message);
                    location.reload();
                })
                .catch(err => alert('Erro: ' + err));
            }
        }

        function cleanErrorCards() {
            if (!confirm('Deseja remover cards com mensagens de erro do Anki deste baralho?')) {
                return;
            }

            fetch(`/anki/management/decks/{{ $deck->id }}/clean-errors`, {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': document.querySelector('input[name="_token"]').value,
                },
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message || 'Limpeza concluída');
                location.reload();
            })
            .catch(err => alert('Erro: ' + err));
        }
    </script>
</x-app-layout>
