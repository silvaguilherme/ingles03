<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Editar Lição: {{ $lesson->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    <form method="POST" action="{{ route('lessons.update', $lesson) }}">
                        @csrf
                        @method('PATCH')

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Título
                                </label>
                                <input type="text" name="title" id="title" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm @error('title') is-invalid @enderror"
                                       value="{{ $lesson->title }}" required>
                                @error('title')
                                    <span class="text-red-600 text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="sub_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Sub-título (opcional)
                                </label>
                                <input type="text" name="sub_title" id="sub_title" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                       value="{{ $lesson->sub_title }}">
                            </div>

                            <div>
                                <label for="content_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Tipo de Conteúdo
                                </label>
                                <select name="content_type" id="content_type" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm @error('content_type') is-invalid @enderror"
                                        required>
                                    <option value="">-- Selecione --</option>
                                    <option value="video" {{ $lesson->content_type === 'video' ? 'selected' : '' }}>Vídeo</option>
                                    <option value="pdf" {{ $lesson->content_type === 'pdf' ? 'selected' : '' }}>PDF</option>
                                    <option value="audio" {{ $lesson->content_type === 'audio' ? 'selected' : '' }}>Áudio</option>
                                    <option value="quiz" {{ $lesson->content_type === 'quiz' ? 'selected' : '' }}>Quiz</option>
                                    <option value="text" {{ $lesson->content_type === 'text' ? 'selected' : '' }}>Texto</option>
                                </select>
                                @error('content_type')
                                    <span class="text-red-600 text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="order" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Ordem
                                </label>
                                <input type="number" name="order" id="order" min="1"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm @error('order') is-invalid @enderror"
                                       value="{{ $lesson->order }}" required>
                                @error('order')
                                    <span class="text-red-600 text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="video_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Chave do Vídeo (ex: videos/mod1/aula1.mp4)
                                </label>
                                <input type="text" name="video_key" id="video_key" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                       value="{{ $lesson->video_key }}">
                            </div>

                            <div>
                                <label for="pdf_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Chave do PDF (ex: pdfs/mod1/aula1.pdf)
                                </label>
                                <input type="text" name="pdf_key" id="pdf_key" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                       value="{{ $lesson->pdf_key }}">
                            </div>

                            <div>
                                <label for="audio_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Chave do Áudio (ex: audios/mod1/aula1.mp3)
                                </label>
                                <input type="text" name="audio_key" id="audio_key" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                       value="{{ $lesson->audio_key }}">
                            </div>

                            <div>
                                <label for="duration_seconds" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Duração (segundos)
                                </label>
                                <input type="number" name="duration_seconds" id="duration_seconds" min="0"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                       value="{{ $lesson->duration_seconds }}">
                            </div>
                        </div>

                        <div class="mt-4 mb-4">
                            <label for="quiz_data" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Dados do Quiz (JSON, opcional)
                            </label>
                            <textarea name="quiz_data" id="quiz_data" rows="6"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                      placeholder='{"questions":[{"question":"...","options":["a","b"],"correct":0}]}'>{{ json_encode($lesson->quiz_data) }}</textarea>
                        </div>

                        <div class="flex gap-4">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                Atualizar
                            </button>
                            <a href="{{ route('courses.show', $lesson->subModule->module->course) }}" class="px-4 py-2 bg-gray-400 text-white rounded-md hover:bg-gray-500">
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
