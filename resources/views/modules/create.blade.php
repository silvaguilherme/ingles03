<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Novo Módulo: {{ $course->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    <form method="POST" action="{{ route('modules.store', $course) }}">
                        @csrf

                        <div class="mb-4">
                            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Título do Módulo
                            </label>
                            <input type="text" name="title" id="title" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm @error('title') is-invalid @enderror"
                                   value="{{ old('title') }}" required>
                            @error('title')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="order" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Ordem
                            </label>
                            <input type="number" name="order" id="order" min="1"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm @error('order') is-invalid @enderror"
                                   value="{{ old('order', $course->modules()->count() + 1) }}" required>
                            @error('order')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="flex gap-4">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                Criar Módulo
                            </button>
                            <a href="{{ route('courses.show', $course) }}" class="px-4 py-2 bg-gray-400 text-white rounded-md hover:bg-gray-500">
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
