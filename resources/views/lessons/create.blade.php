<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg sm:text-xl text-gray-800 dark:text-gray-200 leading-tight line-clamp-2">
            {{ isset($lesson) ? 'Editar' : 'Nova' }} Lição: {{ $subModule->title }}
        </h2>
    </x-slot>

    <div class="py-4 sm:py-12">
        <div class="mx-auto px-3 sm:px-6 lg:px-8 max-w-3xl">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-4 sm:p-6 text-gray-900 dark:text-gray-100">
                    
                    <form method="POST" action="{{ isset($lesson) ? route('lessons.update', $lesson) : route('lessons.store', $subModule) }}">
                        @csrf
                        @if(isset($lesson))
                            @method('PATCH')
                        @endif

                        <!-- Single column on mobile, 2 columns on tablets and up -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Título
                                </label>
                                <input type="text" name="title" id="title" 
                                       class="w-full rounded-md border border-gray-300 shadow-sm px-4 py-3 text-black placeholder-gray-400 focus:border-blue-500 focus:ring-blue-500 min-h-10 @error('title') border-red-500 @enderror"
                                       value="{{ isset($lesson) ? $lesson->title : old('title') }}" required placeholder="Ex: Introdução ao Inglês">
                                @error('title')
                                    <span class="text-red-600 text-xs sm:text-sm mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="sub_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Sub-título (opcional)
                                </label>
                                <input type="text" name="sub_title" id="sub_title" 
                                       class="w-full rounded-md border border-gray-300 shadow-sm px-4 py-3 text-black placeholder-gray-400 focus:border-blue-500 focus:ring-blue-500 min-h-10"
                                       value="{{ isset($lesson) ? $lesson->sub_title : old('sub_title') }}" placeholder="Ex: Pronúncia básica">
                            </div>

                            <div>
                                <label for="content_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Tipo de Conteúdo
                                </label>
                                <select name="content_type" id="content_type" 
                                        class="w-full rounded-md border border-gray-300 shadow-sm px-4 py-3 text-black focus:border-blue-500 focus:ring-blue-500 min-h-10 @error('content_type') border-red-500 @enderror"
                                        required>
                                    <option value="">-- Selecione --</option>
                                    <option value="video" {{ (isset($lesson) ? $lesson->content_type : old('content_type')) === 'video' ? 'selected' : '' }}>🎥 Vídeo</option>
                                    <option value="pdf" {{ (isset($lesson) ? $lesson->content_type : old('content_type')) === 'pdf' ? 'selected' : '' }}>📄 PDF</option>
                                    <option value="quiz" {{ (isset($lesson) ? $lesson->content_type : old('content_type')) === 'quiz' ? 'selected' : '' }}>🧪 Quiz</option>
                                    <option value="text" {{ (isset($lesson) ? $lesson->content_type : old('content_type')) === 'text' ? 'selected' : '' }}>📝 Texto</option>
                                </select>
                                @error('content_type')
                                    <span class="text-red-600 text-xs sm:text-sm mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Ordem
                                </label>
                                <input type="number" name="order" id="order" min="1"
                                       class="w-full rounded-md border border-gray-300 shadow-sm px-4 py-3 text-black placeholder-gray-400 focus:border-blue-500 focus:ring-blue-500 min-h-10 @error('order') border-red-500 @enderror"
                                       value="{{ isset($lesson) ? $lesson->order : old('order', $module->lessons()->count() + 1) }}" required>
                                @error('order')
                                    <span class="text-red-600 text-xs sm:text-sm mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="video_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Chave do Vídeo
                                </label>
                                <input type="text" name="video_key" id="video_key" 
                                       class="w-full rounded-md border border-gray-300 shadow-sm px-4 py-3 text-black placeholder-gray-400 focus:border-blue-500 focus:ring-blue-500 min-h-10"
                                       value="{{ isset($lesson) ? $lesson->video_key : old('video_key') }}" placeholder="Ex: videos/mod1/aula1.mp4">
                            </div>

                            <div>
                                <label for="pdf_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Chave do PDF
                                </label>
                                <input type="text" name="pdf_key" id="pdf_key" 
                                       class="w-full rounded-md border border-gray-300 shadow-sm px-4 py-3 text-black placeholder-gray-400 focus:border-blue-500 focus:ring-blue-500 min-h-10"
                                       value="{{ isset($lesson) ? $lesson->pdf_key : old('pdf_key') }}" placeholder="Ex: pdfs/mod1/aula1.pdf">
                            </div>

                            <div>
                                <label for="duration_seconds" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Duração (segundos)
                                </label>
                                <input type="number" name="duration_seconds" id="duration_seconds" min="0"
                                       class="w-full rounded-md border border-gray-300 shadow-sm px-4 py-3 text-black placeholder-gray-400 focus:border-blue-500 focus:ring-blue-500 min-h-10"
                                       value="{{ isset($lesson) ? $lesson->duration_seconds : old('duration_seconds', 0) }}" placeholder="Ex: 300">
                            </div>
                        </div>

                        <div class="mt-6 mb-6">
                            <label for="quiz_data" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Dados do Quiz (JSON, opcional)
                            </label>
                            <textarea name="quiz_data" id="quiz_data" rows="6"
                                      class="w-full rounded-md border border-gray-300 shadow-sm px-4 py-3 text-black placeholder-gray-400 focus:border-blue-500 focus:ring-blue-500"
                                      placeholder='{"questions":[{"question":"What is...?","options":["a","b","c"],"correct":0}]}'>{{ isset($lesson) ? $lesson->quiz_data : old('quiz_data') }}</textarea>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-3">
                            <button type="submit" class="w-full sm:w-auto px-4 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 active:bg-blue-800 font-medium min-h-10 transition">
                                {{ isset($lesson) ? 'Atualizar' : 'Criar' }} Lição
                            </button>
                            <a href="{{ route('courses.show', $module->course) }}" class="w-full sm:w-auto px-4 py-3 bg-gray-400 text-white rounded-md hover:bg-gray-500 active:bg-gray-600 font-medium text-center min-h-10 flex items-center justify-center transition">
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
