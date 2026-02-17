@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-12 px-4">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Upload de Deck Anki</h1>
            <p class="text-gray-600">Faça upload de um arquivo APKG para criar um novo deck neste submodulo</p>
        </div>

        <!-- Formulário -->
        <div class="bg-white rounded-lg shadow-lg p-8">
            <form action="{{ route('anki-decks.store', $subModule) }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-6">
                    <label for="deck_name" class="block text-sm font-semibold text-gray-700 mb-2">
                        Nome do Deck
                    </label>
                    <input type="text" 
                           id="deck_name" 
                           name="deck_name" 
                           placeholder="Ex: Vocabulário Inglês"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('deck_name') border-red-500 @enderror"
                    >
                    @error('deck_name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-8">
                    <label for="file" class="block text-sm font-semibold text-gray-700 mb-4">
                        Arquivo APKG
                    </label>
                    
                    <div class="border-2 border-dashed border-indigo-300 rounded-lg p-8 text-center cursor-pointer hover:border-indigo-500 hover:bg-indigo-50 transition-colors"
                         onclick="document.getElementById('file').click()">
                        <svg class="w-12 h-12 text-indigo-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3v-6" />
                        </svg>
                        
                        <p class="text-gray-700 font-semibold mb-1">Clique ou arraste um arquivo APKG</p>
                        <p class="text-gray-500 text-sm">ou CSV (formato: pergunta|resposta)</p>
                        
                        <input type="file" 
                               id="file" 
                               name="file" 
                               accept=".apkg,.zip,.csv" 
                               class="hidden"
                               onchange="updateFileName(this)"
                               required
                        >
                    </div>
                    
                    <div id="file-name" class="mt-3 text-sm text-gray-600"></div>
                    
                    @error('file')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Informações sobre formatos -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
                    <h3 class="font-semibold text-blue-900 mb-3">Formatos Suportados</h3>
                    <ul class="text-sm text-blue-800 space-y-2">
                        <li><strong>APKG:</strong> Arquivos nativos do Anki (.apkg)</li>
                        <li><strong>CSV:</strong> Arquivo de texto com formato: pergunta|resposta|tags (um por linha)</li>
                    </ul>
                </div>

                <!-- Botões -->
                <div class="flex gap-4">
                    <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors">
                        Fazer Upload
                    </button>
                    <a href="{{ route('submodules.show', $subModule) }}" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-3 px-4 rounded-lg transition-colors text-center">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>

        <!-- Dicas -->
        <div class="mt-8 bg-yellow-50 border border-yellow-200 rounded-lg p-6">
            <h3 class="font-semibold text-yellow-900 mb-2">💡 Dica</h3>
            <p class="text-yellow-800 text-sm">
                Você pode exportar decks do Anki como arquivos APKG através do menu de exportação no aplicativo Anki.
                Se tiver um CSV, use o formato: uma linha por card com a pergunta, resposta e tags separados por |.
            </p>
        </div>
    </div>
</div>

<script>
function updateFileName(input) {
    const fileName = document.getElementById('file-name');
    if (input.files && input.files[0]) {
        fileName.textContent = '✓ ' + input.files[0].name + ' (' + formatFileSize(input.files[0].size) + ')';
        fileName.className = 'mt-3 text-sm text-green-600 font-semibold';
    }
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

// Drag and drop
const dropZone = document.querySelector('[onclick="document.getElementById(\'file\').click()"]');

dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('border-indigo-500', 'bg-indigo-50');
});

dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('border-indigo-500', 'bg-indigo-50');
});

dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('border-indigo-500', 'bg-indigo-50');
    
    const files = e.dataTransfer.files;
    if (files.length) {
        document.getElementById('file').files = files;
        updateFileName(document.getElementById('file'));
    }
});
</script>
@endsection
