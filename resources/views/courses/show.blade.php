<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $course->title }}
            </h2>
            <a href="{{ route('courses.index') }}" class="text-indigo-600 hover:underline">
                ← Voltar
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid md:grid-cols-3 gap-6">
            <aside class="md:col-span-1 bg-white dark:bg-gray-800 rounded-lg shadow p-4 h-fit">
                @foreach($course->modules as $module)
                    <details class="mb-3" open>
                        <summary class="cursor-pointer font-semibold text-gray-800 dark:text-gray-200">
                            {{ $module->title }}
                        </summary>
                        <ul class="mt-2 space-y-1">
                            @foreach($module->lessons as $lesson)
                                @php $p = $progressMap[$lesson->id] ?? null; @endphp
                                <li class="flex items-center justify-between">
                                    <a href="{{ route('lessons.show', $lesson) }}"
                                       class="text-indigo-600 hover:underline">
                                        {{ $lesson->title }}
                                    </a>
                                    <span class="text-xs {{ ($p && $p->completed) ? 'text-green-600' : 'text-gray-500' }}">
                                        {{ $p->percentage ?? 0 }}% {{ ($p && $p->completed) ? '✓' : '' }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </details>
                @endforeach
            </aside>

            <main class="md:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <p class="text-gray-700 dark:text-gray-300">
                    Selecione uma aula ao lado para começar.
                </p>
            </main>
        </div>
    </div>
</x-app-layout>
