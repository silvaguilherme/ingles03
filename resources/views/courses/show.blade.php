<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div class="flex-1 min-w-0">
                <h2 class="font-semibold text-lg sm:text-2xl text-gray-800 dark:text-gray-200 leading-tight truncate">
                    {{ $course->title }}
                </h2>
                <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1 line-clamp-2">
                    {{ $course->description }}
                </p>
            </div>
            <div class="flex gap-2 w-full sm:w-auto">
                <a href="{{ route('courses.edit', $course) }}" class="flex-1 sm:flex-none px-3 py-2 bg-blue-600 text-white rounded text-center text-xs sm:text-sm font-medium min-h-10 flex items-center justify-center">
                    ✏️
                </a>
                <a href="{{ route('courses.index') }}" class="flex-1 sm:flex-none px-3 py-2 bg-gray-600 text-white rounded text-center text-xs sm:text-sm font-medium min-h-10 flex items-center justify-center">
                    ← Voltar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-3 sm:py-6">
        <div class="mx-auto px-3 sm:px-6 lg:px-8 max-w-full lg:max-w-7xl">
            <!-- Mobile Stacked Layout / Desktop Grid -->
            <div class="block lg:grid lg:grid-cols-3 lg:gap-6">
                <!-- Sidebar - Full width on mobile, collapse on tablet -->
                <aside class="lg:col-span-1 bg-white dark:bg-gray-800 rounded-lg shadow p-3 sm:p-4 mb-4 lg:mb-0 lg:sticky lg:top-4">
                    <div class="mb-3">
                        <a href="{{ route('modules.create', $course) }}" 
                           class="w-full block px-3 py-3 bg-green-600 text-white rounded font-medium text-sm min-h-11 flex items-center justify-center hover:bg-green-700 active:bg-green-800">
                            ➕ Novo Módulo
                        </a>
                    </div>

                    <div class="max-h-96 lg:max-h-none overflow-y-auto lg:overflow-visible">
                        @forelse($course->modules as $module)
                            <details class="mb-3 border rounded-lg overflow-hidden" open>
                                <summary class="cursor-pointer font-semibold text-sm text-gray-800 dark:text-gray-200 p-3 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 flex items-center justify-between transition">
                                    <span class="truncate flex-1 text-xs sm:text-sm">📚 {{ $module->title }}</span>
                                    <div class="flex gap-1 ml-2 flex-shrink-0">
                                        <a href="{{ route('modules.edit', $module) }}" class="text-xs bg-blue-500 text-white px-2 py-1 rounded min-h-8 flex items-center" onclick="event.stopPropagation()">
                                            ✏️
                                        </a>
                                        <form method="POST" action="{{ route('modules.destroy', $module) }}" class="inline" onclick="return confirm('Tem certeza?')" onsubmit="event.stopPropagation()">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-xs bg-red-500 text-white px-2 py-1 rounded min-h-8 flex items-center">🗑️</button>
                                        </form>
                                    </div>
                                </summary>

                                <!-- Sub-modules dentro do Module -->
                                <div class="p-3 border-t bg-gray-50 dark:bg-gray-750">
                                    <div class="mb-3">
                                        <a href="{{ route('submodules.create', $module) }}" 
                                           class="w-full block px-2 py-2 bg-green-500 text-white rounded font-medium text-xs min-h-9 flex items-center justify-center hover:bg-green-600 active:bg-green-700 transition">
                                            ➕ Sub-módulo
                                        </a>
                                    </div>

                                    @forelse($module->subModules as $subModule)
                                        <details class="mb-2 border border-gray-300 rounded overflow-hidden">
                                            <summary class="cursor-pointer font-semibold text-xs text-gray-700 dark:text-gray-300 p-2 bg-gray-100 dark:bg-gray-600 hover:bg-gray-150 dark:hover:bg-gray-500 flex items-center justify-between transition">
                                                <span class="truncate flex-1">📖 {{ $subModule->title }}</span>
                                                <div class="flex gap-1 ml-2 flex-shrink-0">
                                                    <a href="{{ route('submodules.edit', $subModule) }}" class="text-xs bg-blue-400 text-white px-1 py-0.5 rounded" onclick="event.stopPropagation()">✏️</a>
                                                    <form method="POST" action="{{ route('submodules.destroy', $subModule) }}" class="inline" onclick="return confirm('Tem certeza?')" onsubmit="event.stopPropagation()">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="text-xs bg-red-400 text-white px-1 py-0.5 rounded">🗑️</button>
                                                    </form>
                                                </div>
                                            </summary>

                                            <!-- Lições dentro do Sub-module -->
                                            <ul class="space-y-1 p-2 border-t">
                                                @forelse($subModule->lessons as $lesson)
                                                    @php $p = $progressMap[$lesson->id] ?? null; @endphp
                                                    <li class="flex items-center justify-between text-xs gap-2 p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                                        <a href="{{ route('lessons.show', $lesson) }}"
                                                           class="text-indigo-600 dark:text-indigo-400 hover:underline flex-1 truncate">
                                                            {{ $lesson->title }}
                                                        </a>
                                                        <div class="flex items-center gap-1 flex-shrink-0">
                                                            <span class="text-xs {{ ($p && $p->completed) ? 'text-green-600 font-semibold' : 'text-gray-500' }}">
                                                                {{ $p->percentage ?? 0 }}%
                                                            </span>
                                                            @if($p && $p->completed)
                                                                <span class="text-green-600">✓</span>
                                                            @endif
                                                        </div>
                                                    </li>
                                                @empty
                                                    <li class="text-xs text-gray-500 italic py-1">Nenhuma lição</li>
                                                @endforelse

                                                <li class="mt-1 pt-1 border-t">
                                                    <a href="{{ route('lessons.create', $subModule) }}" 
                                                       class="w-full block px-2 py-1 bg-indigo-500 text-white rounded font-medium text-xs min-h-8 flex items-center justify-center hover:bg-indigo-600 active:bg-indigo-700 transition">
                                                        ➕ Lição
                                                    </a>
                                                </li>
                                            </ul>
                                        </details>
                                    @empty
                                        <p class="text-xs text-gray-500 italic text-center py-2">Nenhum sub-módulo</p>
                                    @endforelse
                                </div>
                            </details>
                        @empty
                            <p class="text-gray-500 italic text-sm text-center py-6">Nenhum módulo ainda</p>
                        @endforelse
                    </div>
                </aside>

                <!-- Main Content -->
                <main class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-6">
                    <div class="text-gray-700 dark:text-gray-300 text-center">
                        <p class="mb-6 text-sm sm:text-base">Selecione uma aula ao lado para começar.</p>
                        
                        @php
                            // Collect all lessons from all submodules
                            $allLessons = collect();
                            foreach ($course->modules as $module) {
                                foreach ($module->subModules as $subModule) {
                                    $allLessons = $allLessons->merge($subModule->lessons);
                                }
                            }
                            $completedCount = $allLessons->filter(fn($l) => isset($progressMap[$l->id]) && $progressMap[$l->id]?->completed)->count();
                            $totalCount = $allLessons->count();
                            $courseProgress = $totalCount > 0 ? round(($completedCount / $totalCount) * 100) : 0;
                        @endphp
                        
                        <div class="mt-8 p-4 sm:p-6 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-600 rounded-lg">
                            <h3 class="font-bold text-base sm:text-lg mb-3 text-gray-800 dark:text-gray-100">
                                📊 Progresso Geral
                            </h3>
                            <div class="text-3xl sm:text-4xl font-bold text-blue-600 mb-3">{{ $courseProgress }}%</div>
                            <div class="w-full bg-gray-300 rounded-full h-3 dark:bg-gray-500 overflow-hidden">
                                <div class="bg-gradient-to-r from-blue-500 to-indigo-600 h-3 rounded-full transition-all duration-500" style="width: {{ $courseProgress }}%"></div>
                            </div>
                            <p class="text-xs sm:text-sm mt-4 font-medium text-gray-600 dark:text-gray-300">
                                {{ $completedCount }} de {{ $totalCount }} aulas concluídas
                            </p>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>
</x-app-layout>
