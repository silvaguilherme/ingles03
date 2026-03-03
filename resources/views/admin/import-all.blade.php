<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg sm:text-2xl text-gray-800 dark:text-gray-200 leading-tight">
            📥 Centro de Importação
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8">
                <div class="mb-8">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">
                        Importar Todos os Módulos
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Execute todos os imports de uma vez: vídeos, PDFs, Anki e áudios
                    </p>
                </div>

                <!-- Info Box -->
                <div class="bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg p-6 mb-8">
                    <h4 class="font-semibold text-blue-900 dark:text-blue-100 mb-3">ℹ️ O que será importado</h4>
                    <ul class="text-sm text-blue-800 dark:text-blue-200 space-y-2">
                        <li>✓ <strong>Vídeos/Aulas:</strong> php import_videos.php</li>
                        <li>📄 <strong>PDFs:</strong> php artisan import:pdfs</li>
                        <li>🎴 <strong>Anki Decks:</strong> php artisan anki:import</li>
                        <li>🎵 <strong>Áudios:</strong> php artisan import:audios</li>
                    </ul>
                </div>

                <!-- Import Button -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                    <button id="importAllBtn" 
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-4 px-6 rounded-lg transition-colors text-lg">
                        🚀 Importar TUDO
                    </button>
                    <button id="importAnkiBtn" 
                            class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-4 px-6 rounded-lg transition-colors text-lg">
                        🎴 Importar Apenas Anki
                    </button>
                </div>

                <!-- Loading -->
                <div id="loadingContainer" class="hidden text-center mb-8">
                    <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-indigo-600 mx-auto mb-4"></div>
                    <p class="text-gray-600 dark:text-gray-400 text-lg">Processando imports...</p>
                </div>

                <!-- Results -->
                <div id="resultsContainer" class="hidden space-y-4">
                    <!-- Vídeos -->
                    <div id="videosCard" class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 border-l-4 border-indigo-600">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-semibold text-gray-900 dark:text-gray-100 text-lg flex items-center gap-2">
                                🎥 Vídeos/Aulas
                            </h4>
                            <span id="videosStatus" class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-200 dark:bg-gray-600">Processando...</span>
                        </div>
                        <p id="videosMessage" class="text-sm text-gray-600 dark:text-gray-400 mb-2"></p>
                        <div id="videosOutput" class="bg-gray-900 text-gray-100 p-3 rounded text-xs font-mono overflow-y-auto max-h-40 hidden"></div>
                    </div>

                    <!-- PDFs -->
                    <div id="pdfsCard" class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 border-l-4 border-green-600">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-semibold text-gray-900 dark:text-gray-100 text-lg flex items-center gap-2">
                                📄 PDFs
                            </h4>
                            <span id="pdfsStatus" class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-200 dark:bg-gray-600">Processando...</span>
                        </div>
                        <p id="pdfsMessage" class="text-sm text-gray-600 dark:text-gray-400 mb-2"></p>
                        <div id="pdfsOutput" class="bg-gray-900 text-gray-100 p-3 rounded text-xs font-mono overflow-y-auto max-h-40 hidden"></div>
                    </div>

                    <!-- Anki -->
                    <div id="ankiCard" class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 border-l-4 border-purple-600">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-semibold text-gray-900 dark:text-gray-100 text-lg flex items-center gap-2">
                                🎴 Anki Decks
                            </h4>
                            <span id="ankiStatus" class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-200 dark:bg-gray-600">Processando...</span>
                        </div>
                        <p id="ankiMessage" class="text-sm text-gray-600 dark:text-gray-400 mb-2"></p>
                        <div id="ankiOutput" class="bg-gray-900 text-gray-100 p-3 rounded text-xs font-mono overflow-y-auto max-h-40 hidden"></div>
                    </div>

                    <!-- Áudios -->
                    <div id="audiosCard" class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 border-l-4 border-yellow-600">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-semibold text-gray-900 dark:text-gray-100 text-lg flex items-center gap-2">
                                🎵 Áudios
                            </h4>
                            <span id="audiosStatus" class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-200 dark:bg-gray-600">Processando...</span>
                        </div>
                        <p id="audiosMessage" class="text-sm text-gray-600 dark:text-gray-400 mb-2"></p>
                        <div id="audiosOutput" class="bg-gray-900 text-gray-100 p-3 rounded text-xs font-mono overflow-y-auto max-h-40 hidden"></div>
                    </div>

                    <!-- Summary -->
                    <div id="summaryContainer" class="hidden mt-8 bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 rounded-lg p-6">
                        <div class="text-center">
                            <div class="text-5xl mb-3">✅</div>
                            <h3 class="text-2xl font-bold text-green-900 dark:text-green-100 mb-2">Importação Concluída!</h3>
                            <p class="text-green-800 dark:text-green-200 mb-4" id="summaryText"></p>
                            <button onclick="location.reload()" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-semibold">
                                ↻ Começar Novamente
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const statusMap = {
            'success': { text: '✅ Sucesso', class: 'bg-green-200 dark:bg-green-600 text-green-900 dark:text-green-100' },
            'warning': { text: '⚠️ Aviso', class: 'bg-yellow-200 dark:bg-yellow-600 text-yellow-900 dark:text-yellow-100' },
            'error': { text: '❌ Erro', class: 'bg-red-200 dark:bg-red-600 text-red-900 dark:text-red-100' }
        };

        document.getElementById('importAllBtn').addEventListener('click', async () => {
            const btn = document.getElementById('importAllBtn');
            const loadingContainer = document.getElementById('loadingContainer');
            const resultsContainer = document.getElementById('resultsContainer');

            btn.disabled = true;
            btn.textContent = '⏳ Processando...';
            loadingContainer.classList.remove('hidden');
            resultsContainer.classList.remove('hidden');

            try {
                const response = await fetch('{{ route("import-all.execute") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': document.querySelector('input[name="_token"]').value,
                    },
                });

                const data = await response.json();
                loadingContainer.classList.add('hidden');

                // Atualizar resultados
                updateResult('videos', data.data.videos);
                updateResult('pdfs', data.data.pdfs);
                updateResult('anki', data.data.anki);
                updateResult('audios', data.data.audios);

                // Mostrar summary
                const allSuccess = Object.values(data.data).every(r => r.status === 'success');
                if (allSuccess || response.ok) {
                    document.getElementById('summaryContainer').classList.remove('hidden');
                    document.getElementById('summaryText').textContent = 'Todos os módulos foram importados com sucesso!';
                }
            } catch (error) {
                loadingContainer.classList.add('hidden');
                alert('Erro ao executar imports: ' + error.message);
                btn.disabled = false;
                btn.textContent = '🚀 Iniciar Importação Completa';
            }
        });

        function updateResult(type, result) {
            const statusEl = document.getElementById(type + 'Status');
            const messageEl = document.getElementById(type + 'Message');
            const outputEl = document.getElementById(type + 'Output');

            const statusInfo = statusMap[result.status];
            statusEl.textContent = statusInfo.text;
            statusEl.className = statusInfo.class + ' px-3 py-1 rounded-full text-xs font-semibold';

            messageEl.textContent = result.message;
            if (result.output && result.output.trim()) {
                outputEl.textContent = result.output;
                outputEl.classList.remove('hidden');
            }
        }

        // Importar apenas Anki
        document.getElementById('importAnkiBtn').addEventListener('click', async () => {
            const btn = document.getElementById('importAnkiBtn');
            const loadingContainer = document.getElementById('loadingContainer');
            const resultsContainer = document.getElementById('resultsContainer');

            btn.disabled = true;
            btn.textContent = '⏳ Processando...';
            loadingContainer.classList.remove('hidden');
            resultsContainer.classList.remove('hidden');

            try {
                const response = await fetch('{{ route("import-all.execute-anki") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': document.querySelector('input[name="_token"]').value,
                    },
                });

                const data = await response.json();
                loadingContainer.classList.add('hidden');

                // Limpar resultados anteriores
                document.getElementById('videosCard').classList.add('hidden');
                document.getElementById('pdfsCard').classList.add('hidden');
                document.getElementById('audiosCard').classList.add('hidden');

                // Atualizar apenas resultado de Anki
                updateResult('anki', data.data.anki);

                // Mostrar summary
                if (data.success) {
                    document.getElementById('summaryContainer').classList.remove('hidden');
                    document.getElementById('summaryText').textContent = 'Baralhos Anki importados com sucesso! (sem duplicatas)';
                }
            } catch (error) {
                loadingContainer.classList.add('hidden');
                alert('Erro ao importar Anki: ' + error.message);
                btn.disabled = false;
                btn.textContent = '🎴 Importar Apenas Anki';
            }
        });
    </script>
</x-app-layout>
