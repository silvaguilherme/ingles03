<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $lesson->module->course->title }} › {{ $lesson->module->title }} › {{ $lesson->title }}
            </h2>
            <a href="{{ route('courses.show', $lesson->module->course) }}"
               class="text-indigo-600 hover:underline">
                ← Voltar ao curso
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid md:grid-cols-3 gap-6">
            <aside class="md:col-span-1 bg-white dark:bg-gray-800 rounded-lg shadow p-4 h-fit">
                <p class="mb-3 text-sm text-gray-600 dark:text-gray-300">
                    Progresso desta aula: <strong id="pct">{{ $progress->percentage }}%</strong>
                </p>
                @if($pdfUrl)
                    <a href="{{ $pdfUrl }}"
                       target="_blank"
                       class="inline-flex items-center px-3 py-2 bg-slate-200 dark:bg-slate-700 text-slate-900 dark:text-slate-100 rounded hover:bg-slate-300">
                        Abrir PDF
                    </a>
                @endif
                <button id="markDone"
                        class="ml-2 inline-flex items-center px-3 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700">
                    Marcar como concluída
                </button>
            </aside>

            <main class="md:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                @if($videoUrl)
                    <video id="player" controls class="w-full rounded" preload="metadata">
                        <source src="{{ $videoUrl }}" type="video/mp4"/>
                        Seu navegador não suporta vídeo HTML5.
                    </video>
                @else
                    <div class="p-6 text-gray-600 dark:text-gray-300">
                        Vídeo não disponível para esta aula.
                    </div>
                @endif
            </main>
        </div>
    </div>

    <script>
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const lessonId = {{ $lesson->id }};
        const player = document.getElementById('player');
        const pct = document.getElementById('pct');

        function sendProgress(completed=false) {
            const current = player ? (player.currentTime || 0) : 0;
            const duration = player ? (player.duration || 1) : 1;

            fetch('{{ route('progress.store') }}', {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf},
                body: JSON.stringify({
                    lesson_id: lessonId,
                    current_time: current,
                    duration: duration,
                    completed: completed
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data?.progress?.percentage !== undefined) {
                    pct.textContent = data.progress.percentage + '%';
                }
            })
            .catch(console.error);
        }

        if (player) {
            let timer = null;
            player.addEventListener('play', () => { if (timer) clearInterval(timer); timer = setInterval(()=>sendProgress(false), 10000); });
            player.addEventListener('pause', () => { if (timer) clearInterval(timer); sendProgress(false); });
            player.addEventListener('ended', () => { if (timer) clearInterval(timer); sendProgress(true); });
        }
        document.getElementById('markDone')?.addEventListener('click', () => sendProgress(true));
    </script>
</x-app-layout>
