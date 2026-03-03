<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg sm:text-2xl text-gray-800 dark:text-gray-200 leading-tight">
            📚 Importar Decks Anki
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto px-4">
            <!-- Tabs -->
            <div class="flex gap-4 mb-8" role="tablist">
                <button role="tab" aria-selected="true" aria-controls="apkg-panel" 
                        class="tab-button px-6 py-3 rounded-lg font-semibold transition-colors bg-indigo-600 text-white"
                        onclick="switchTab(event, 'apkg')">
                    🎴 Importar APKG
                </button>
                <button role="tab" aria-selected="false" aria-controls="pdf-panel"
                        class="tab-button px-6 py-3 rounded-lg font-semibold transition-colors bg-gray-600 text-white hover:bg-gray-700"
                        onclick="switchTab(event, 'pdf')">
                    📄 Importar PDF
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
            <div id="pdf-panel" class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8 hidden">
                <div class="mb-8">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">
                        Importar Cards de PDF
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Selecione um arquivo PDF com cards no padrão Front:/Back: e crie um novo deck automaticamente.
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Formulário -->
                    <div class="space-y-6">
                        <form id="pdfImportForm" class="space-y-6">
                            @csrf

                            <div>
                                <label for="pdf_path" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    Caminho do PDF
                                </label>
                                <input type="text" 
                                       id="pdf_path" 
                                       name="path"
                                       placeholder="Ex: pdfs/course1/grammar.pdf"
                                       class="w-full px-4 py-3 bg-[#1a8eff] text-white placeholder-gray-200 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                >
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                    📁 Caminho relativo no storage (app/public ou app/)
                                </p>
                            </div>

                            <div>
                                <label for="audio_path" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    🎵 Áudio (opcional)
                                </label>
                                <input type="text" 
                                       id="audio_path" 
                                       name="audio_path"
                                       placeholder="Ex: audio/course1/grammar.mp3"
                                       class="w-full px-4 py-3 bg-[#1a8eff] text-white placeholder-gray-200 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                >
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                    🔊 Se deixar em branco, procurará automaticamente em /audio/ (nome do PDF)
                                </p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="submodule_id" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                        Submodulo
                                    </label>
                                    <select id="submodule_id" 
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
                                    <label for="deck_name" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                        Nome do Deck (opcional)
                                    </label>
                                    <input type="text" 
                                           id="deck_name"
                                           name="deck_name"
                                           placeholder="Ex: Grammar Lesson 01"
                                           class="w-full px-4 py-3 bg-[#1a8eff] text-white placeholder-gray-200 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                    >
                                </div>
                            </div>

                            <div class="flex gap-3">
                                <button type="button" 
                                        id="previewBtn"
                                        class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors">
                                    👁️ Preview
                                </button>
                                <button type="submit" 
                                        id="importPdfBtn"
                                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors disabled:opacity-50"
                                        disabled>
                                    📥 Importar
                                </button>
                            </div>
                        </form>

                        <!-- Loading -->
                        <div id="pdfLoadingContainer" class="hidden text-center">
                            <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600 mx-auto"></div>
                            <p class="text-gray-600 dark:text-gray-400 mt-2 text-sm">Processando...</p>
                        </div>
                    </div>

                    <!-- Preview -->
                    <div id="previewContainer" class="hidden bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-4">
                            📋 Preview (primeiros 3 cards)
                        </h4>
                        <div id="previewContent" class="space-y-3 max-h-96 overflow-y-auto"></div>
                    </div>

                    <!-- Success -->
                    <div id="successContainer" class="hidden bg-green-50 dark:bg-green-900 rounded-lg p-4">
                        <div class="text-center">
                            <div class="text-4xl mb-2">✅</div>
                            <h4 class="font-semibold text-green-900 dark:text-green-100 mb-2">Importação Concluída!</h4>
                            <div id="successMessage" class="text-sm text-green-800 dark:text-green-200 mb-4"></div>
                            <a href="{{ route('anki.index') }}" class="inline-block px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                Ver Decks
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Info -->
                <div class="mt-8 bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg p-6">
                    <h4 class="font-semibold text-blue-900 dark:text-blue-100 mb-3">ℹ️ Formato Esperado</h4>
                    <pre class="text-xs text-blue-800 dark:text-blue-200 overflow-x-auto"><code>Front:
[Grammar] You must read this book.
Back:
Você tem que ler esse livro.
-----
Front:
[Grammar] You must come visit us.
Back:
Você tem que vir nos visitar.</code></pre>
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

        // PDF Preview
        document.getElementById('previewBtn').addEventListener('click', async () => {
            const path = document.getElementById('pdf_path').value;
            if (!path) {
                alert('Digite o caminho do PDF');
                return;
            }

            const loadingContainer = document.getElementById('pdfLoadingContainer');
            const previewContainer = document.getElementById('previewContainer');
            const previewContent = document.getElementById('previewContent');

            loadingContainer.classList.remove('hidden');
            previewContainer.classList.add('hidden');

            try {
                const response = await fetch('{{ route("anki.preview-pdf") }}', {
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
                        <div class="text-gray-600 dark:text-gray-400">
                            📊 <strong>${data.data.estimated_cards}</strong> cards encontrados
                        </div>`;
                    html += '</div>';
                    
                    previewContent.innerHTML = html;
                    previewContainer.classList.remove('hidden');
                    document.getElementById('importPdfBtn').disabled = false;
                } else {
                    alert('Erro: ' + (data.message || 'Não foi possível processar o PDF'));
                }
            } catch (error) {
                loadingContainer.classList.add('hidden');
                alert('Erro: ' + error.message);
            }
        });

        // PDF Import
        document.getElementById('pdfImportForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const path = document.getElementById('pdf_path').value;
            const submoduleId = document.getElementById('submodule_id').value;
            const deckName = document.getElementById('deck_name').value;
            const audioPath = document.getElementById('audio_path').value;

            if (!path || !submoduleId) {
                alert('Preencha o caminho do PDF e selecione um submodulo');
                return;
            }

            const loadingContainer = document.getElementById('pdfLoadingContainer');
            const successContainer = document.getElementById('successContainer');
            const successMessage = document.getElementById('successMessage');
            const previewContainer = document.getElementById('previewContainer');

            document.getElementById('pdfImportForm').style.display = 'none';
            previewContainer.classList.add('hidden');
            loadingContainer.classList.remove('hidden');

            try {
                const response = await fetch('{{ route("anki.import-pdf") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': document.querySelector('input[name="_token"]').value,
                    },
                    body: JSON.stringify({ path, submodule_id: submoduleId, deck_name: deckName, audio_path: audioPath }),
                });

                const data = await response.json();
                loadingContainer.classList.add('hidden');

                if (data.success) {
                    successMessage.innerHTML = `
                        <div class="text-left space-y-2">
                            <p>✓ Deck: <strong>${data.data.deck_name}</strong></p>
                            <p>✓ Cards Criados: <strong>${data.data.cards_created}</strong></p>
                            ${data.data.audio_path ? `<p>🎵 Áudio: <strong>${data.data.audio_path}</strong></p>` : ''}
                        </div>
                    `;
                    successContainer.classList.remove('hidden');
                } else {
                    alert('Erro: ' + (data.message || 'Erro desconhecido'));
                    document.getElementById('pdfImportForm').style.display = 'block';
                }
            } catch (error) {
                loadingContainer.classList.add('hidden');
                alert('Erro: ' + error.message);
                document.getElementById('pdfImportForm').style.display = 'block';
            }
        });
    </script>
</x-app-layout>
