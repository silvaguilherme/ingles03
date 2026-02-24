<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg sm:text-2xl text-gray-800 dark:text-gray-200 leading-tight">
            Importar Decks Anki
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto px-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8">
                <div class="mb-8">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">
                        🎴 Importar Decks das Pastas
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Este formulário irá varrer as pastas especificadas e importar automaticamente todos os arquivos APKG encontrados.
                    </p>
                </div>

                <form id="importForm" class="space-y-6">
                    @csrf

                    <div>
                        <label for="path" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Caminho Base para Importação
                        </label>
                        <input type="text" 
                               id="path" 
                               name="path" 
                               value="/var/www/ingles03/storage/app/public/videos"
                               placeholder="/caminho/para/videos"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                        >
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                            💡 O comando irá procurar por arquivos .apkg dentro de subpastas /anki/
                        </p>
                    </div>

                    <button type="submit" 
                            id="importBtn"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            data-loading-text="⏳ Importando...">
                        🚀 Iniciar Importação
                    </button>
                </form>

                <!-- Output -->
                <div id="outputContainer" class="mt-8 hidden">
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        Resultado da Importação
                    </h4>
                    <div id="consoleOutput" class="bg-gray-900 text-gray-100 p-4 rounded-lg font-mono text-sm overflow-y-auto max-h-96 whitespace-pre-wrap break-words"></div>
                    
                    <div class="mt-4 flex gap-2">
                        <button onclick="window.location.reload()" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                            ✓ Feito
                        </button>
                        <a href="{{ route('anki.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                            Ver Decks
                        </a>
                    </div>
                </div>

                <!-- Loading -->
                <div id="loadingContainer" class="mt-8 text-center hidden">
                    <div class="inline-block">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400 mt-4">
                        Importando decks... isso pode levar alguns minutos.
                    </p>
                </div>

                <!-- Info -->
                <div class="mt-8 bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg p-6">
                    <h4 class="font-semibold text-blue-900 dark:text-blue-100 mb-3">ℹ️ Informações</h4>
                    <ul class="text-sm text-blue-800 dark:text-blue-200 space-y-2">
                        <li>✓ O comando procura por arquivos <code class="bg-blue-100 dark:bg-blue-800 px-1 rounded">.apkg</code> em pastas <code class="bg-blue-100 dark:bg-blue-800 px-1 rounded">/anki/</code></li>
                        <li>✓ Associa automaticamente aos submodulos pelo número da pasta (ex: /01/, /03/)</li>
                        <li>✓ Importa todos os cards do arquivo APKG</li>
                        <li>✓ Extrai imagens e áudios automaticamente</li>
                        <li>⚠️ Se nenhum card aparecer, verifique se os APKGs existem no caminho especificado</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
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
                
                if (data.output) {
                    consoleOutput.textContent = data.output;
                } else {
                    consoleOutput.textContent = data.message || 'Simples terminado';
                }

                // Auto-scroll to bottom
                consoleOutput.scrollTop = consoleOutput.scrollHeight;
            } catch (error) {
                loadingContainer.classList.add('hidden');
                outputContainer.classList.remove('hidden');
                consoleOutput.textContent = `Erro: ${error.message}`;
            }
        });

        // Add loading states to buttons
        document.querySelectorAll('button[type="submit"]').forEach(button => {
            button.addEventListener('click', function(e) {
                if (this.hasAttribute('data-loading-text')) {
                    this.disabled = true;
                    const originalText = this.textContent;
                    this.setAttribute('data-original-text', originalText);
                    this.textContent = this.getAttribute('data-loading-text');
                }
            });
        });
    </script>
</x-app-layout>
