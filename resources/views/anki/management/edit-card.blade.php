<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-lg sm:text-2xl text-gray-800 dark:text-gray-200 leading-tight">
                ✏️ Editar Card
            </h2>
            <a href="{{ route('anki.management.edit-deck', $card->deck) }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                ← Voltar
            </a>
        </div>
    </x-slot>

    <input type="hidden" name="_token" value="{{ csrf_token() }}">

    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8">
                <form id="editCardForm" class="space-y-6">
                    @csrf

                    <!-- Info do Deck -->
                    <div class="bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
                        <p class="text-sm text-blue-900 dark:text-blue-100">
                            <strong>📚 Baralho:</strong> {{ $card->deck->name }}
                        </p>
                    </div>

                    <!-- Field Front -->
                    <div>
                        <label for="front" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Front (Pergunta)
                        </label>
                        <textarea id="front" name="front" rows="4" 
                                  class="w-full px-4 py-3 bg-[#1a8eff] text-white placeholder-gray-200 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                  required>{{ $card->front }}</textarea>
                    </div>

                    <!-- Field Back -->
                    <div>
                        <label for="back" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Back (Resposta)
                        </label>
                        <textarea id="back" name="back" rows="4" 
                                  class="w-full px-4 py-3 bg-[#1a8eff] text-white placeholder-gray-200 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                  required>{{ $card->back }}</textarea>
                    </div>

                    <!-- Field Audio -->
                    <div>
                        <label for="audio_path" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            🎵 Áudio (Opcional)
                        </label>
                        <input type="text" id="audio_path" name="audio_path" 
                               placeholder="Ex: audio/lesson1/card1.mp3"
                               value="{{ $card->audio_path ?? '' }}"
                               class="w-full px-4 py-3 bg-[#1a8eff] text-white placeholder-gray-200 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                            Deixe em branco para remover o áudio
                        </p>
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-3 pt-4">
                        <button type="submit" class="flex-1 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition">
                            💾 Salvar Card
                        </button>
                        <button type="button" onclick="deleteCard()" class="flex-1 px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition">
                            🗑️ Deletar Card
                        </button>
                    </div>
                </form>

                <!-- Preview -->
                <div class="mt-8 border-t border-gray-300 dark:border-gray-600 pt-8">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">👁️ Preview</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-indigo-50 dark:bg-indigo-900/30 border-2 border-indigo-600 rounded-lg p-4">
                            <p class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold mb-2">FRONT</p>
                            <p class="text-gray-900 dark:text-gray-100 whitespace-pre-wrap" id="previewFront">
                                {{ $card->front }}
                            </p>
                        </div>
                        <div class="bg-green-50 dark:bg-green-900/30 border-2 border-green-600 rounded-lg p-4">
                            <p class="text-xs text-green-600 dark:text-green-400 font-semibold mb-2">BACK</p>
                            <p class="text-gray-900 dark:text-gray-100 whitespace-pre-wrap" id="previewBack">
                                {{ $card->back }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Atualizar preview em tempo real
        document.getElementById('front').addEventListener('input', (e) => {
            document.getElementById('previewFront').textContent = e.target.value;
        });

        document.getElementById('back').addEventListener('input', (e) => {
            document.getElementById('previewBack').textContent = e.target.value;
        });

        // Salvar card
        document.getElementById('editCardForm').addEventListener('submit', (e) => {
            e.preventDefault();

            const form = new FormData();
            form.append('front', document.getElementById('front').value);
            form.append('back', document.getElementById('back').value);
            form.append('audio_path', document.getElementById('audio_path').value);

            fetch(`/anki/management/cards/{{ $card->id }}`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-Token': document.querySelector('input[name="_token"]').value,
                },
                body: form
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    history.back();
                } else {
                    alert('Erro: ' + data.message);
                }
            })
            .catch(err => alert('Erro: ' + err));
        });

        // Deletar card
        function deleteCard() {
            if (confirm('Deseja deletar este card permanentemente?')) {
                fetch(`/anki/management/cards/{{ $card->id }}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-Token': document.querySelector('input[name="_token"]').value,
                    },
                })
                .then(res => res.json())
                .then(data => {
                    alert(data.message);
                    history.back();
                })
                .catch(err => alert('Erro: ' + err));
            }
        }
    </script>
</x-app-layout>
