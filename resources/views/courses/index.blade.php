<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Meus Cursos') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid md:grid-cols-3 gap-6">
            @forelse($courses as $course)
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

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg p-6">
                    <h3 class="text-lg font-bold mb-2">{{ $course->title }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-4 line-clamp-3">
                        {{ $course->description }}
                    </p>

                    <div class="w-full bg-gray-200 rounded-full h-2.5 mb-2">
                        <div class="bg-indigo-600 h-2.5 rounded-full" style="width: {{ $avg }}%"></div>
                    </div>
                    <p class="text-xs text-gray-500 mb-4">
                        {{ $avg }}% concluído ({{ $done }}/{{ $total }} aulas)
                    </p>

                    <a href="{{ route('courses.show', $course) }}"
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                        Abrir curso
                    </a>
                </div>
            @empty
                <p class="text-gray-600 dark:text-gray-300">Nenhum curso cadastrado ainda.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
