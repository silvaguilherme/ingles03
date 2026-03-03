<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg sm:text-2xl text-gray-800 dark:text-gray-200 leading-tight">
            📚 Importar Decks Anki
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto px-4">
            <!-- Tabs -->
            <div class="flex gap-4 mb-8 flex-wrap" role="tablist">
                <button role="tab" aria-selected="true" aria-controls="apkg-panel" 
                        class="tab-button px-6 py-3 rounded-lg font-semibold transition-colors bg-indigo-600 text-white"
                        onclick="switchTab(event, 'apkg')">
                    🎴 Importar APKG
                </button>
                <button role="tab" aria-selected="false" aria-controls="csv-panel"
                        class="tab-button px-6 py-3 rounded-lg font-semibold transition-colors bg-gray-600 text-white hover:bg-gray-700"
                        onclick="switchTab(event, 'csv')">
                    📊 Importar CSV
                </button>
            </div>

            <!-- APKG Tab -->
            <div id="apkg-panel" class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8 mb-8">
                <div class="mb-8">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">
                        Importar Arquivos APKG
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Varreia as pastas especificadas e importa automaticamente todos os arquivos APKG encontrados.
                    </p>
                </div>

                <form id="importForm" class="space-y-6">
                    @csrf
                    <div>
                        <label for="path" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Caminho Base
                        </label>
                        <input type="text" 
                               id="path" 
                               name="path" 
                               value="/var/www/ingles03/storage/app/public/videos"
                               placeholder="/caminho/para/videos"
                               class="w-full px-4 py-3 bg-[#1a8eff] text-white placeholder-gray-200 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                        >
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                            💡 Procurará por .apkg em subpastas /anki/
                        </p>
                    </div>

                    <button type="submit" 
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-lg transition-colors disabled:opacity-50"
                            data-loading-text="⏳ Importando...">
                        🚀 Iniciar Importação
                    </button>
                </form>

                <div id="outputContainer" class="mt-8 hidden">
                    <div id="consoleOutput" class="bg-gray-900 text-gray-100 p-4 rounded-lg font-mono text-sm h-64 overflow-y-auto whitespace-pre-wrap"></div>
                    <div class="mt-4 flex gap-2">
                        <button onclick="window.location.reload()" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                            ✓ Feito
                        </button>
                        <a href="{{ route('anki.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                            Ver Decks
                        </a>
                    </div>
                </div>

                <div id="loadingContainer" class="mt-8 text-center hidden">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mx-auto"></div>
                    <p class="text-gray-600 dark:text-gray-400 mt-4">Importando...</p>
                </div>
            </div>

            <!-- PDF Tab -->
            <div id="csv-panel" class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8 hidden">
                <div class="mb-8">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">
                        Importar Cards de CSV
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Importe um arquivo CSV com colunas: Front, Back, Audio
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Formulário -->
                    <div class="space-y-6">
                        <form id="csvImportForm" class="space-y-6">
                            @csrf

                            <div>
                                <label for="csv_path" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    📁 Caminho do CSV
                                </label>
                                <input type="text" 
                                       id="csv_path" 
                                       name="path"
                                       placeholder="Ex: cards/lesson1.csv"
                                       class="w-full px-4 py-3 bg-[#1a8eff] text-white placeholder-gray-200 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                >
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                    📁 Caminho relativo no storage
                                </p>
                            </div>

                            <div>
                                <label for="csv_submodule" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    📚 Submodulo
                                </label>
                                <select id="csv_submodule" 
                                        name="submodule_id"
                                        class="w-full px-4 py-3 bg-[#1a8eff] text-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                        required>
                                    <option value="">Selecione...</option>
                                    @forelse(\App\Models\SubModule::with('module.course')->get() as $submodule)
                                        <option value="{{ $submodule->id }}">
                                            {{ $submodule->module->course->title }} › {{ $submodule->module->title }} › {{ $submodule->title }}
                                        </option>
                                    @empty
                                        <option value="" disabled>Nenhum submodulo encontrado</option>
                                    @endforelse
                                </select>
                            </div>

                            <div>
                                <label for="csv_deck_name" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    🎴 Nome do Deck (opcional)
                                </label>
                                <input type="text" 
                                       id="csv_deck_name"
                                       name="deck_name"
                                       placeholder="Ex: Grammar Lesson 01"
                                       class="w-full px-4 py-3 bg-[#1a8eff] text-white placeholder-gray-200 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                >
                            </div>

                            <div class="flex gap-3">
                                <button type="button" 
                                        id="csvPreviewBtn"
                                        class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors">
                                    👁️ Preview
                                </button>
                                <button type="submit" 
                                        id="csvImportBtn"
                                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors disabled:opacity-50"
                                        disabled>
                                    📥 Importar
                                </button>
                            </div>
                        </form>

                        <!-- Loading -->
                        <div id="csvLoadingContainer" class="hidden text-center">
                            <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600 mx-auto"></div>
                            <p class="text-gray-600 dark:text-gray-400 mt-2 text-sm">Processando...</p>
                        </div>
                    </div>

                    <!-- Preview -->
                    <div id="csvPreviewContainer" class="hidden bg-gray-50 dark:bg-gray-700 rounded-lg p-4 order-last lg:order-none">
                        <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-4">
                            📋 Preview (primeiros 3 cards)
                        </h4>
                        <div id="csvPreviewContent" class="space-y-4 max-h-96 overflow-y-auto">
                            <!-- Cards serão inseridos aqui -->
                        </div>
                    </div>

                    <!-- Success -->
                    <div id="csvSuccessContainer" class="hidden bg-green-50 dark:bg-green-900 rounded-lg p-4 lg:col-span-2">
                        <div class="text-center">
                            <div class="text-4xl mb-2">✅</div>
                            <h4 class="font-semibold text-green-900 dark:text-green-100 mb-2">Importação Concluída!</h4>
                            <div id="csvSuccessMessage" class="text-sm text-green-800 dark:text-green-200 mb-4"></div>
                            <a href="{{ route('anki.index') }}" class="inline-block px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                Ver Decks
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Info -->
                <div class="mt-8 bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg p-6">
                    <h4 class="font-semibold text-blue-900 dark:text-blue-100 mb-3">ℹ️ Formato Esperado</h4>
                    <pre class="text-xs text-blue-800 dark:text-blue-200 overflow-x-auto bg-white dark:bg-gray-800 p-3 rounded border border-blue-300 dark:border-blue-600"><code>Front,Back,Audio
"[Grammar] You must read this book.","Você tem que ler esse livro.","audio/lesson1/card1.mp3"
"[Grammar] You must come visit us.","Você tem que vir nos visitar.","audio/lesson1/card2.mp3"
"[Vocabulary] What is your name?","Qual é o seu nome?","audio/lesson1/card3.mp3"</code></pre>
                    <div class="mt-4 text-xs text-blue-800 dark:text-blue-200 space-y-2">
                        <p><strong>📌 Notas:</strong></p>
                        <ul class="list-disc list-inside space-y-1">
                            <li>Primeira linha deve ter os headers: Front, Back, Audio</li>
                            <li>Use aspas duplas para envolver textos com vírgulas</li>
                            <li>Coluna Audio: caminho relativo no storage ou deixar vazio</li>
                            <li>Encoding: UTF-8</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Tab switching
        function switchTab(e, tab) {
            e.preventDefault();
            const panels = document.querySelectorAll('[id$="-panel"]');
            const buttons = document.querySelectorAll('.tab-button');
            
            // Hide all panels
            panels.forEach(p => p.classList.add('hidden'));
            buttons.forEach(b => {
                b.classList.remove('bg-indigo-600');
                b.classList.add('bg-gray-600');
                b.setAttribute('aria-selected', 'false');
            });
            
            // Show selected panel
            document.getElementById(tab + '-panel').classList.remove('hidden');
            e.target.classList.remove('bg-gray-600');
            e.target.classList.add('bg-indigo-600');
            e.target.setAttribute('aria-selected', 'true');
        }

        // APKG Import
        document.getElementById('importForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const path = document.getElementById('path').value;
            const form = document.getElementById('importForm');
            const loadingContainer = document.getElementById('loadingContainer');
            const outputContainer = document.getElementById('outputContainer');
            const consoleOutput = document.getElementById('consoleOutput');

            form.style.display = 'none';
            loadingContainer.classList.remove('hidden');
            consoleOutput.textContent = '';

            try {
                const response = await fetch('{{ route("anki.import") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': document.querySelector('input[name="_token"]').value,
                    },
                    body: JSON.stringify({ path }),
                });

                const data = await response.json();
                loadingContainer.classList.add('hidden');
                outputContainer.classList.remove('hidden');
                consoleOutput.textContent = data.output || data.message || 'Simples concluído';
                consoleOutput.scrollTop = consoleOutput.scrollHeight;
            } catch (error) {
                loadingContainer.classList.add('hidden');
                outputContainer.classList.remove('hidden');
                consoleOutput.textContent = `Erro: ${error.message}`;
            }
        });

        // CSV Preview
        document.getElementById('csvPreviewBtn').addEventListener('click', async () => {
            const path = document.getElementById('csv_path').value;
            if (!path) {
                alert('Digite o caminho do CSV');
                return;
            }

            const loadingContainer = document.getElementById('csvLoadingContainer');
            const previewContainer = document.getElementById('csvPreviewContainer');
            const previewContent = document.getElementById('csvPreviewContent');

            loadingContainer.classList.remove('hidden');
            previewContainer.classList.add('hidden');

            try {
                const response = await fetch('{{ route("anki.preview-csv") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': document.querySelector('input[name="_token"]').value,
                    },
                    body: JSON.stringify({ path }),
                });

                const data = await response.json();
                loadingContainer.classList.add('hidden');

                if (data.success) {
                    let html = `<div class="text-sm space-y-3">
                        <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                            <span>📊</span>
                            <strong>${data.data.total_cards}</strong>
                            <span>cards encontrados</span>
                        </div>
                    </div>`;

                    if (data.data.preview && data.data.preview.length > 0) {
                        html += '<div class="border-t border-gray-300 dark:border-gray-600 pt-3">';
                        data.data.preview.forEach((card, idx) => {
                            html += `<div class="bg-white dark:bg-gray-800 p-3 rounded mb-2 border border-gray-200 dark:border-gray-600">
                                <div class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Card ${idx + 1}</div>
                                <div class="text-xs mb-2">
                                    <strong>Front:</strong> ${escapeHtml(card.front.substring(0, 50))}${card.front.length > 50 ? '...' : ''}
                                </div>
                                <div class="text-xs mb-2">
                                    <strong>Back:</strong> ${escapeHtml(card.back.substring(0, 50))}${card.back.length > 50 ? '...' : ''}
                                </div>
                                ${card.audio ? `<div class="text-xs text-green-700 dark:text-green-400">🎵 ${escapeHtml(card.audio)}</div>` : `<div class="text-xs text-gray-500">🔇 Sem áudio</div>`}
                            </div>`;
                        });
                        html += '</div>';
                    }

                    previewContent.innerHTML = html;
                    previewContainer.classList.remove('hidden');
                    document.getElementById('csvImportBtn').disabled = false;
                } else {
                    alert('Erro: ' + (data.message || 'Não foi possível processar o CSV'));
                }
            } catch (error) {
                loadingContainer.classList.add('hidden');
                alert('Erro: ' + error.message);
            }
        });

        // CSV Import
        document.getElementById('csvImportForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const path = document.getElementById('csv_path').value;
            const submoduleId = document.getElementById('csv_submodule').value;
            const deckName = document.getElementById('csv_deck_name').value;

            if (!path || !submoduleId) {
                alert('Preencha o caminho do CSV e selecione um submodulo');
                return;
            }

            const loadingContainer = document.getElementById('csvLoadingContainer');
            const successContainer = document.getElementById('csvSuccessContainer');
            const successMessage = document.getElementById('csvSuccessMessage');
            const previewContainer = document.getElementById('csvPreviewContainer');

            document.getElementById('csvImportForm').style.display = 'none';
            previewContainer.classList.add('hidden');
            loadingContainer.classList.remove('hidden');

            try {
                const response = await fetch('{{ route("anki.import-csv") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': document.querySelector('input[name="_token"]').value,
                    },
                    body: JSON.stringify({ path, submodule_id: submoduleId, deck_name: deckName }),
                });

                const data = await response.json();
                loadingContainer.classList.add('hidden');

                if (data.success) {
                    successMessage.innerHTML = `
                        <div class="text-left space-y-2">
                            <p>✓ Deck: <strong>${data.data.deck_name}</strong></p>
                            <p>✓ Cards Criados: <strong>${data.data.cards_created}</strong></p>
                            <p>📊 Total no CSV: <strong>${data.data.total_cards}</strong></p>
                        </div>
                    `;
                    successContainer.classList.remove('hidden');
                } else {
                    document.getElementById('csvImportForm').style.display = 'block';
                    loadingContainer.classList.add('hidden');
                    previewContainer.classList.remove('hidden');
                    alert('Erro: ' + (data.message || 'Erro ao importar CSV'));
                }
            } catch (error) {
                document.getElementById('csvImportForm').style.display = 'block';
                loadingContainer.classList.add('hidden');
                previewContainer.classList.remove('hidden');
                alert('Erro: ' + error.message);
            }
        });

        // Helper function to escape HTML
        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }
    </script>
</x-app-layout>
