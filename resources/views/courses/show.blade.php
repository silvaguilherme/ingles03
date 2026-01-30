<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ $course->title }}
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    {{ $course->description }}
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('courses.edit', $course) }}" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Editar
                </a>
                <a href="{{ route('courses.index') }}" class="px-3 py-1 bg-gray-600 text-white rounded hover:bg-gray-700">
                    Voltar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid md:grid-cols-3 gap-6">
            <!-- Sidebar -->
            <aside class="md:col-span-1 bg-white dark:bg-gray-800 rounded-lg shadow p-4 h-fit">
                <div class="mb-4">
                    <a href="{{ route('modules.create', $course) }}" 
                       class="w-full block px-3 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-center text-sm">
                        + Novo Módulo
                    </a>
                </div>

                @forelse($course->modules as $module)
                    <details class="mb-4 border rounded-lg overflow-hidden" open>
                        <summary class="cursor-pointer font-semibold text-gray-800 dark:text-gray-200 p-3 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 flex items-center justify-between">
                            <span>{{ $module->title }}</span>
                            <div class="flex gap-1">
                                <a href="{{ route('modules.edit', $module) }}" class="text-xs bg-blue-500 text-white px-2 py-1 rounded" onclick="event.stopPropagation()">
                                    ✏️
                                </a>
                                <form method="POST" action="{{ route('modules.destroy', $module) }}" class="inline" onclick="return confirm('Tem certeza?')" onsubmit="event.stopPropagation()">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs bg-red-500 text-white px-2 py-1 rounded">🗑️</button>
                                </form>
                            </div>
                        </summary>

                        <!-- Progresso do Módulo -->
                        @php
                            $totalLessons = $module->lessons->count();
                            $completedLessons = $module->lessons->filter(fn($l) => $progressMap[$l->id]?->completed)->count();
                            $moduleProgress = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;
                        @endphp
                        <div class="p-3 border-t">
                            <div class="flex justify-between text-xs mb-1">
                                <span>Progresso:</span>
                                <span>{{ $moduleProgress }}%</span>
                            </div>
                            <div class="w-full bg-gray-300 rounded-full h-2 dark:bg-gray-600">
                                <div class="bg-green-600 h-2 rounded-full" style="width: {{ $moduleProgress }}%"></div>
                            </div>
                        </div>

                        <!-- Lições -->
                        <ul class="space-y-1 p-3 border-t">
                            @forelse($module->lessons as $lesson)
                                @php $p = $progressMap[$lesson->id] ?? null; @endphp
                                <li class="flex items-center justify-between text-sm">
                                    <a href="{{ route('lessons.show', $lesson) }}"
                                       class="text-indigo-600 hover:underline flex-1">
                                        {{ $lesson->title }}
                                    </a>
                                    <span class="text-xs {{ ($p && $p->completed) ? 'text-green-600' : 'text-gray-500' }} ml-2">
                                        {{ $p->percentage ?? 0 }}% {{ ($p && $p->completed) ? '✓' : '' }}
                                    </span>
                                    <a href="{{ route('lessons.edit', $lesson) }}" class="text-xs text-blue-600 ml-1" onclick="event.stopPropagation()">✏️</a>
                                </li>
                            @empty
                                <li class="text-xs text-gray-500 italic">Nenhuma lição</li>
                            @endforelse

                            <li class="mt-2">
                                <a href="{{ route('lessons.create', $module) }}" 
                                   class="w-full block px-2 py-1 bg-green-500 text-white rounded hover:bg-green-600 text-center text-xs">
                                    + Lição
                                </a>
                            </li>
                        </ul>
                    </details>
                @empty
                    <p class="text-gray-500 italic text-sm">Nenhum módulo ainda</p>
                @endforelse
            </aside>

            <!-- Main Content -->
            <main class="md:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="text-gray-700 dark:text-gray-300 text-center">
                    <p class="mb-4">Selecione uma aula ao lado para começar.</p>
                    
                    <!-- Progresso Geral do Curso -->
                    @php
                        $allLessons = $course->lessons;
                        $completedCount = $allLessons->filter(fn($l) => $progressMap[$l->id]?->completed)->count();
                        $totalCount = $allLessons->count();
                        $courseProgress = $totalCount > 0 ? round(($completedCount / $totalCount) * 100) : 0;
                    @endphp
                    <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <h3 class="font-semibold mb-2">Progresso Geral: {{ $courseProgress }}%</h3>
                        <div class="w-full bg-gray-300 rounded-full h-4 dark:bg-gray-600">
                            <div class="bg-blue-600 h-4 rounded-full" style="width: {{ $courseProgress }}%"></div>
                        </div>
                        <p class="text-xs mt-2">{{ $completedCount }}/{{ $totalCount }} aulas concluídas</p>
                    </div>
                </div>
            </main>
        </div>
    </div>
</x-app-layout>
