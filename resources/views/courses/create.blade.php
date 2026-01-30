<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg sm:text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ isset($course) ? 'Editar Curso' : 'Novo Curso' }}
        </h2>
    </x-slot>

    <div class="py-4 sm:py-12">
        <div class="mx-auto px-3 sm:px-6 lg:px-8 max-w-2xl">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-4 sm:p-6 text-gray-900 dark:text-gray-100">
                    
                    <form method="POST" action="{{ isset($course) ? route('courses.update', $course) : route('courses.store') }}">
                        @csrf
                        @if(isset($course))
                            @method('PATCH')
                        @endif

                        <div class="mb-6">
                            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Título do Curso
                            </label>
                            <input type="text" name="title" id="title" 
                                   class="w-full rounded-md border border-gray-300 shadow-sm px-4 py-3 text-black placeholder-gray-400 focus:border-blue-500 focus:ring-blue-500 min-h-10 @error('title') border-red-500 @enderror"
                                   value="{{ $course->title ?? old('title') }}" required placeholder="Ex: Inglês Intermediário">
                            @error('title')
                                <span class="text-red-600 text-xs sm:text-sm mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Descrição
                            </label>
                            <textarea name="description" id="description" rows="5"
                                      class="w-full rounded-md border border-gray-300 shadow-sm px-4 py-3 text-black placeholder-gray-400 focus:border-blue-500 focus:ring-blue-500"
                                      placeholder="Descreva o conteúdo do curso...">{{ $course->description ?? old('description') }}</textarea>
                            @error('description')
                                <span class="text-red-600 text-xs sm:text-sm mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="flex flex-col sm:flex-row gap-3">
                            <button type="submit" class="w-full sm:w-auto px-4 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 active:bg-blue-800 font-medium min-h-10 transition">
                                {{ isset($course) ? 'Atualizar' : 'Criar Curso' }}
                            </button>
                            <a href="{{ route('courses.index') }}" class="w-full sm:w-auto px-4 py-3 bg-gray-400 text-white rounded-md hover:bg-gray-500 active:bg-gray-600 font-medium text-center min-h-10 flex items-center justify-center transition">
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
