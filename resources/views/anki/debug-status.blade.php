<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg sm:text-2xl text-gray-800 dark:text-gray-200 leading-tight">
            Status dos Decks Anki
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto px-4">
            <!-- Decks Importados -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden mb-8">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        📚 Decks Importados ({{ count($decks) }})
                    </h3>
                </div>
                
                @if($decks->isEmpty())
                    <div class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                        <p>Nenhum deck importado ainda</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Nome do Deck</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Submodulo ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Submodulo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Cards</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($decks as $deck)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $deck->id }}</td>
                                        <td class="px-6 py-3 text-sm text-gray-900 dark:text-gray-100 font-medium">{{ $deck->name }}</td>
                                        <td class="px-6 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $deck->submodule_id ?? '—' }}</td>
                                        <td class="px-6 py-3 text-sm text-gray-900 dark:text-gray-100">
                                            @if($deck->submodule)
                                                <span class="bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 px-2 py-1 rounded text-xs">
                                                    {{ $deck->submodule->title }}
                                                </span>
                                            @else
                                                <span class="bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 px-2 py-1 rounded text-xs">
                                                    ❌ Não associado
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $deck->cards->count() }}</td>
                                        <td class="px-6 py-3 text-sm">
                                            @if($deck->submodule_id && $deck->cards->count() > 0)
                                                <span class="bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 px-2 py-1 rounded text-xs">
                                                    ✅ OK
                                                </span>
                                            @elseif(!$deck->submodule_id)
                                                <span class="bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 px-2 py-1 rounded text-xs">
                                                    ⚠️ Sem submodulo
                                                </span>
                                            @else
                                                <span class="bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 px-2 py-1 rounded text-xs">
                                                    ⚠️ Sem cards
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- Submodulos -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden mb-8">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        📖 Submodulos Disponíveis ({{ count($submodules) }})
                    </h3>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Titulo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Order</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Decks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($submodules as $sub)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-3 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $sub->id }}</td>
                                    <td class="px-6 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $sub->title }}</td>
                                    <td class="px-6 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $sub->order }}</td>
                                    <td class="px-6 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $sub->ankiDecks->count() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Ações -->
            <div class="bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-4">🔧 Ações</h3>
                
                <button onclick="reassociateDecks()" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors">
                    🔄 Reasociar Decks aos Submodulos
                </button>
                
                <p class="text-sm text-blue-800 dark:text-blue-200 mt-3">
                    Se os decks não aparecem nos submodulos, clique neste botão para tentar reasociar baseado no nome/ID do deck.
                </p>
            </div>
        </div>
    </div>

    <script>
        async function reassociateDecks() {
            if (!confirm('Isso vai tentar reasociar todos os decks aos submodulos. Continuar?')) {
                return;
            }

            try {
                const response = await fetch('{{ route("anki.reassociate") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });

                const data = await response.json();

                let output = `✅ Sucesso!\n`;
                output += `Reasociados: ${data.reassociated}\n`;
                output += `Falhados: ${data.failed}\n\n`;
                output += data.messages.join('\n');

                alert(output);
                location.reload();
            } catch (error) {
                alert(`Erro: ${error.message}`);
            }
        }
    </script>
</x-app-layout>
