<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg sm:text-2xl text-gray-800 dark:text-gray-200 leading-tight">
            🔧 Diagnóstico Anki
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4">
            <!-- BOTÃO PRINCIPAL -->
            <div class="bg-blue-50 dark:bg-blue-900 border-2 border-blue-400 dark:border-blue-600 rounded-lg p-8 mb-8">
                <h2 class="text-3xl font-bold text-blue-900 dark:text-blue-100 mb-6">
                    🔄 Reasociar Decks Agora
                </h2>
                <p class="text-blue-800 dark:text-blue-200 mb-6">
                    Se os decks não aparecem nos submodulos, clique no botão abaixo para reasociar automaticamente:
                </p>
                <button onclick="reassociateDecks()" class="px-8 py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg transition-colors text-lg">
                    ✅ Reasociar Agora
                </button>
            </div>

            <!-- Decks -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-8">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-4">
                    📚 Decks Importados: {{ count($decks) }}
                </h2>
                
                @if(count($decks) === 0)
                    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                        <p>❌ Nenhum deck foi importado ainda</p>
                        <p class="text-sm mt-2">Acesse <a href="{{ route('anki.import-page') }}" class="text-indigo-600 underline">Importar de Pastas</a> para começar.</p>
                    </div>
                @else
                    <ul class="space-y-3">
                        @foreach($decks as $deck)
                            <li class="p-3 bg-gray-50 dark:bg-gray-700 rounded border-l-4 border-indigo-500">
                                <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $deck->name }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    🆔 ID: {{ $deck->id }}
                                    | 📊 Cards: {{ $deck->cards->count() }}
                                    | 🗂️ Submodulo: 
                                    @if($deck->submodule)
                                        <span class="text-green-600 font-semibold">{{ $deck->submodule->title }}</span>
                                    @else
                                        <span class="text-red-600 font-semibold">❌ Não associado</span>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <!-- Submodulos -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-4">
                    📖 Submodulos Disponíveis: {{ count($submodules) }}
                </h2>
                
                <div class="space-y-2">
                    @foreach($submodules as $sub)
                        <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded">
                            <div class="font-semibold text-gray-900 dark:text-gray-100">
                                [{{ $sub->id }}] {{ $sub->title }} 
                                <span class="text-xs text-gray-500">(ordem: {{ $sub->order }})</span>
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                🎴 Decks: {{ $sub->ankiDecks->count() }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <script>
        async function reassociateDecks() {
            if (!confirm('Reasociar todos os decks? Continuar?')) return;

            const button = event.target;
            button.disabled = true;
            button.innerHTML = '⏳ Processando...';

            try {
                const response = await fetch('{{ route("anki.reassociate") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });

                const data = await response.json();
                alert(`✅ Concluído!\n\nReasociados: ${data.reassociated}\nFalhados: ${data.failed}\n\n${data.messages.join('\n')}`);
                location.reload();
            } catch (error) {
                alert(`❌ Erro: ${error.message}`);
                button.disabled = false;
                button.innerHTML = '✅ Reasociar Agora';
            }
        }
    </script>
</x-app-layout>
