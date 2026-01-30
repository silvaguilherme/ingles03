<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <h2 class="font-semibold text-lg sm:text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Meus Cursos') }}
            </h2>
            <a href="{{ route('courses.create') }}" 
               class="w-full sm:w-auto px-4 py-3 bg-green-600 text-white rounded font-semibold text-center hover:bg-green-700 active:bg-green-800 min-h-10 flex items-center justify-center transition">
                ➕ Novo Curso
            </a>
        </div>
    </x-slot>

    <div class="py-4 sm:py-6">
        <div class="mx-auto px-3 sm:px-6 lg:px-8">
            @if($courses->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                    @foreach($courses as $course)
                        @php
                            $lessons   = $course->lessons;
                            $total     = max(1, $lessons->count());
                            $done      = 0;
                            $sumPct    = 0;
                            foreach ($lessons as $l) {
                                $p = $progressMap[$l->id] ?? null;
                                if ($p) {
                                    $sumPct += (int) $p->percentage;
                                    if ($p->completed) $done++;
                                }
                            }
                            $avg = $total ? (int) round($sumPct / $total) : 0;
                        @endphp

                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg p-4 sm:p-6 hover:shadow-lg transition-shadow flex flex-col h-full">
                            <div class="flex items-start justify-between mb-3 mb-auto">
                                <h3 class="text-base sm:text-lg font-bold flex-1 pr-2 line-clamp-2">{{ $course->title }}</h3>
                                <div class="flex gap-2 flex-shrink-0">
                                    <a href="{{ route('courses.edit', $course) }}" 
                                       class="p-2 text-blue-600 hover:text-blue-800 dark:hover:text-blue-400 text-xl rounded hover:bg-blue-50 dark:hover:bg-blue-900 transition" title="Editar">✏️</a>
                                    <form method="POST" action="{{ route('courses.destroy', $course) }}" 
                                          class="inline" onclick="return confirm('Tem certeza que deseja deletar este curso?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-2 text-red-600 hover:text-red-800 dark:hover:text-red-400 text-xl rounded hover:bg-red-50 dark:hover:bg-red-900 transition" title="Deletar">🗑️</button>
                                    </form>
                                </div>
                            </div>

                            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 mb-4 line-clamp-3">
                                {{ $course->description ?: 'Sem descrição' }}
                            </p>

                            <div class="mb-4">
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="text-gray-600 dark:text-gray-400">Progresso</span>
                                    <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $avg }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-600 overflow-hidden">
                                    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 h-2 rounded-full transition-all duration-300" style="width: {{ $avg }}%"></div>
                                </div>
                                <p class="text-xs text-gray-500 mt-2">
                                    {{ $done }}/{{ $total }} aulas concluídas
                                </p>
                            </div>

                            <div class="flex gap-2 pt-4 border-t border-gray-200 dark:border-gray-700 mt-auto">
                                <a href="{{ route('courses.show', $course) }}"
                                   class="flex-1 px-4 py-3 bg-indigo-600 text-white rounded hover:bg-indigo-700 active:bg-indigo-800 text-center text-xs sm:text-sm font-semibold min-h-10 flex items-center justify-center transition">
                                    Abrir Curso
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg p-6 sm:p-12 text-center">
                    <p class="text-gray-600 dark:text-gray-300 text-base sm:text-lg mb-6">
                        Nenhum curso cadastrado ainda.
                    </p>
                    <a href="{{ route('courses.create') }}" 
                       class="inline-flex px-4 sm:px-6 py-3 bg-green-600 text-white rounded hover:bg-green-700 active:bg-green-800 font-semibold text-center min-h-10 items-center justify-center transition">
                        ➕ Criar Seu Primeiro Curso
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
