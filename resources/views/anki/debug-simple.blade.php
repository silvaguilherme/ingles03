<x-app-layout>
    <x-slot name="header">
        Diagnóstico Anki
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Botão Reasociar -->
            <div class="bg-blue-100 border-2 border-blue-500 rounded-lg p-6 mb-8">
                <h2 class="text-2xl font-bold text-blue-900 mb-4">🔄 Reasociar Decks</h2>
                <button id="reassociateBtn" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded">
                    Clique aqui para reasociar decks
                </button>
            </div>

            <!-- Decks -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <h3 class="text-xl font-bold mb-4">Decks: {{ count($decks) }}</h3>
                @if(count($decks) === 0)
                    <p>Nenhum deck</p>
                @else
                    <ul>
                        @foreach($decks as $deck)
                            <li class="py-2 border-b">
                                <strong>{{ $deck->name }}</strong> 
                                (ID: {{ $deck->id }}, Cards: {{ $deck->cards->count() }}, SubModule: {{ $deck->submodule_id ?? 'null' }})
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <!-- Submodulos -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-xl font-bold mb-4">Submodulos: {{ count($submodules) }}</h3>
                <ul>
                    @foreach($submodules as $sub)
                        <li class="py-2 border-b">
                            [{{ $sub->id }}] {{ $sub->title }} (order: {{ $sub->order }}, Decks: {{ $sub->ankiDecks->count() }})
                        </li>
                    @endforeach
                </ul>
            </div>

        </div>
    </div>

    <script>
        document.getElementById('reassociateBtn').addEventListener('click', async () => {
            if (!confirm('Reasociar todos os decks?')) return;

            const btn = document.getElementById('reassociateBtn');
            btn.disabled = true;
            btn.textContent = 'Processando...';

            try {
                const res = await fetch('{{ route("anki.reassociate") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                const data = await res.json();
                alert(`Reasociados: ${data.reassociated}\nFalhados: ${data.failed}`);
                location.reload();
            } catch(e) {
                alert('Erro: ' + e.message);
                btn.disabled = false;
                btn.textContent = 'Clique aqui para reasociar decks';
            }
        });
    </script>
</x-app-layout>
