<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ $lesson->title }}
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    {{ $lesson->module->course->title }} › {{ $lesson->module->title }}
                </p>
            </div>
            <a href="{{ route('courses.show', $lesson->module->course) }}"
               class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                ← Voltar
            </a>
        </div>
    </x-slot>

    <div class="py-6" data-lesson-id="{{ $lesson->id }}">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid md:grid-cols-3 gap-6">
            <!-- Sidebar -->
            <aside class="md:col-span-1 bg-white dark:bg-gray-800 rounded-lg shadow p-4 h-fit">
                <!-- Progresso -->
                <div class="mb-6">
                    <div class="flex justify-between mb-2">
                        <h3 class="font-semibold text-gray-700 dark:text-gray-300">Seu Progresso</h3>
                        <span id="progress-text-{{ $lesson->id }}" class="font-bold text-blue-600">{{ $progress->percentage }}%</span>
                    </div>
                    <div class="w-full bg-gray-300 rounded-full h-4 dark:bg-gray-600 overflow-hidden">
                        <div id="progress-bar-{{ $lesson->id }}" class="bg-blue-600 h-4 transition-all duration-300" 
                             style="width: {{ $progress->percentage }}%"></div>
                    </div>
                    @if($progress->completed)
                        <p class="text-sm text-green-600 mt-2 font-semibold">✓ Aula Concluída!</p>
                    @endif
                </div>

                <!-- Tipo de Conteúdo -->
                <div class="mb-6 p-3 bg-gray-50 dark:bg-gray-700 rounded">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Tipo:</p>
                    <p class="font-semibold capitalize">
                        @switch($lesson->content_type)
                            @case('video') 🎥 Vídeo @break
                            @case('pdf') 📄 PDF @break
                            @case('quiz') 🧪 Quiz @break
                            @case('text') 📝 Texto @break
                            @default {{ $lesson->content_type }}
                        @endswitch
                    </p>
                </div>

                <!-- Duração do Vídeo -->
                @if($lesson->duration_seconds && $lesson->content_type === 'video')
                    <div class="mb-6 p-3 bg-gray-50 dark:bg-gray-700 rounded">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Duração:</p>
                        <p class="font-semibold">{{ floor($lesson->duration_seconds / 60) }}m {{ $lesson->duration_seconds % 60 }}s</p>
                    </div>
                @endif

                <!-- Recursos -->
                @if($pdfUrl || $videoUrl)
                    <div class="space-y-2">
                        @if($pdfUrl)
                            <a href="{{ $pdfUrl }}" target="_blank" rel="noopener"
                               class="w-full block px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-center text-sm">
                                📄 Baixar PDF
                            </a>
                        @endif
                        
                        @if($lesson->content_type === 'video' && $videoUrl)
                            <button id="markDone"
                                    class="w-full px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                                {{ $progress->completed ? '✓ Concluído' : 'Marcar como Concluído' }}
                            </button>
                        @endif
                    </div>
                @endif

                <!-- Editar (Admin) -->
                <div class="mt-6 pt-6 border-t border-gray-300 dark:border-gray-600">
                    <a href="{{ route('lessons.edit', $lesson) }}" 
                       class="w-full block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-center text-sm mb-2">
                        ✏️ Editar
                    </a>
                    <form method="POST" action="{{ route('lessons.destroy', $lesson) }}" class="inline-block w-full" onclick="return confirm('Tem certeza?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-sm">
                            🗑️ Deletar
                        </button>
                    </form>
                </div>
            </aside>

            <!-- Main Content -->
            <main class="md:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="mb-4">
                    @if($lesson->sub_title)
                        <h3 class="text-lg text-gray-600 dark:text-gray-400">{{ $lesson->sub_title }}</h3>
                    @endif
                </div>

                <!-- Conteúdo Dinâmico -->
                @switch($lesson->content_type)
                    @case('video')
                        @if($videoUrl)
                            <video id="lesson-video" controls class="w-full rounded-lg mb-4" preload="metadata">
                                <source src="{{ $videoUrl }}" type="video/mp4"/>
                                Seu navegador não suporta vídeo HTML5.
                            </video>
                        @else
                            <div class="p-8 text-center text-gray-500">
                                <p>🎥 Vídeo não disponível</p>
                            </div>
                        @endif
                        @break

                    @case('pdf')
                        @if($pdfUrl)
                            <iframe src="{{ $pdfUrl }}" class="w-full rounded-lg" style="height: 600px;"></iframe>
                        @else
                            <div class="p-8 text-center text-gray-500">
                                <p>📄 PDF não disponível</p>
                            </div>
                        @endif
                        @break

                    @case('quiz')
                        @if($lesson->quiz_data)
                            <div id="quiz-container" class="space-y-6">
                                <!-- Quiz renderizado dinamicamente -->
                                @php
                                    $quiz = is_array($lesson->quiz_data) ? $lesson->quiz_data : json_decode($lesson->quiz_data, true);
                                @endphp
                                @if(isset($quiz['questions']) && is_array($quiz['questions']))
                                    @foreach($quiz['questions'] as $index => $question)
                                        <div class="p-4 border rounded-lg">
                                            <h4 class="font-semibold mb-3">{{ $index + 1 }}. {{ $question['question'] ?? 'Pergunta' }}</h4>
                                            <div class="space-y-2">
                                                @foreach(($question['options'] ?? []) as $optIndex => $option)
                                                    <label class="flex items-center p-2 border rounded hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer">
                                                        <input type="radio" name="question_{{ $index }}" value="{{ $optIndex }}" class="mr-3">
                                                        <span>{{ $option }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                    <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700" onclick="submitQuiz()">
                                        Enviar Respostas
                                    </button>
                                @else
                                    <p class="text-gray-500">Quiz não configurado</p>
                                @endif
                            </div>
                        @endif
                        @break

                    @default
                        <div class="prose dark:prose-invert max-w-none">
                            <p class="text-gray-700 dark:text-gray-300">
                                Conteúdo da lição: {{ $lesson->title }}
                            </p>
                        </div>
                @endswitch
            </main>
        </div>
    </div>

    <script>
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const lessonId = {{ $lesson->id }};
        const videoElement = document.getElementById('lesson-video');
        const progressBar = document.getElementById('progress-bar-{{ $lesson->id }}');
        const progressText = document.getElementById('progress-text-{{ $lesson->id }}');

        function sendProgress(completed = false) {
            const current = videoElement ? (videoElement.currentTime || 0) : 0;
            const duration = videoElement ? (videoElement.duration || 1) : 1;

            fetch('{{ route('progress.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
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
                    const percentage = data.progress.percentage;
                    progressText.textContent = percentage + '%';
                    progressBar.style.width = percentage + '%';
                    
                    // Salva em localStorage também
                    localStorage.setItem(`video_progress_${lessonId}`, current);
                }
            })
            .catch(error => console.error('Erro ao salvar progresso:', error));
        }

        // Rastreia vídeo
        if (videoElement) {
            // Restaura posição anterior se disponível
            const savedTime = localStorage.getItem(`video_progress_${lessonId}`);
            if (savedTime) {
                videoElement.currentTime = parseFloat(savedTime);
            }

            let saveTimer;
            videoElement.addEventListener('play', () => {
                if (saveTimer) clearInterval(saveTimer);
                saveTimer = setInterval(() => sendProgress(false), 10000); // Salva a cada 10s
            });
            videoElement.addEventListener('pause', () => {
                if (saveTimer) clearInterval(saveTimer);
                sendProgress(false);
            });
            videoElement.addEventListener('ended', () => {
                if (saveTimer) clearInterval(saveTimer);
                sendProgress(true);
            });
        }

        document.getElementById('markDone')?.addEventListener('click', () => sendProgress(true));

        function submitQuiz() {
            alert('Quiz enviado! (Função de validação será implementada)');
            sendProgress(true);
        }
    </script>
</x-app-layout>
