<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg sm:text-xl text-gray-800 dark:text-gray-200 leading-tight line-clamp-2">
            Editar Sub-módulo: {{ $module->title }}
        </h2>
    </x-slot>

    <div class="py-4 sm:py-12">
        <div class="mx-auto px-3 sm:px-6 lg:px-8 max-w-2xl">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-4 sm:p-6 text-gray-900 dark:text-gray-100">
                    
                    <form method="POST" action="{{ route('submodules.update', $subModule) }}">
                        @csrf
                        @method('PATCH')

                        <div class="mb-6">
                            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Título do Sub-módulo
                            </label>
                            <input type="text" name="title" id="title" 
                                   class="w-full rounded-md border border-gray-300 shadow-sm px-4 py-3 text-black placeholder-gray-400 focus:border-blue-500 focus:ring-blue-500 min-h-10 @error('title') border-red-500 @enderror"
                                   value="{{ $subModule->title }}" required>
                            @error('title')
                                <span class="text-red-600 text-xs sm:text-sm mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Descrição (opcional)
                            </label>
                            <textarea name="description" id="description" rows="4"
                                      class="w-full rounded-md border border-gray-300 shadow-sm px-4 py-3 text-black placeholder-gray-400 focus:border-blue-500 focus:ring-blue-500">{{ $subModule->description }}</textarea>
                        </div>

                        <div class="mb-6">
                            <label for="order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Ordem
                            </label>
                            <input type="number" name="order" id="order" min="1"
                                   class="w-full rounded-md border border-gray-300 shadow-sm px-4 py-3 text-black placeholder-gray-400 focus:border-blue-500 focus:ring-blue-500 min-h-10 @error('order') border-red-500 @enderror"
                                   value="{{ $subModule->order }}" required>
                            @error('order')
                                <span class="text-red-600 text-xs sm:text-sm mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="flex flex-col sm:flex-row gap-3">
                            <button type="submit" class="w-full sm:w-auto px-4 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 active:bg-blue-800 font-medium min-h-10 transition">
                                Atualizar Sub-módulo
                            </button>
                            <a href="{{ route('courses.show', $course) }}" class="w-full sm:w-auto px-4 py-3 bg-gray-400 text-white rounded-md hover:bg-gray-500 active:bg-gray-600 font-medium text-center min-h-10 flex items-center justify-center transition">
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
