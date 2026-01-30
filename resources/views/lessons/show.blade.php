<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div class="flex-1 min-w-0">
                <h2 class="font-semibold text-lg sm:text-2xl text-gray-800 dark:text-gray-200 leading-tight truncate">
                    {{ $lesson->title }}
                </h2>
                <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1 line-clamp-2">
                    {{ $lesson->module->course->title }} › {{ $lesson->module->title }}
                </p>
            </div>
            <a href="{{ route('courses.show', $lesson->module->course) }}"
               class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 text-center text-sm font-medium min-h-10">
                ← Voltar
            </a>
        </div>
    </x-slot>

    <div class="py-4 sm:py-6" data-lesson-id="{{ $lesson->id }}">
        <div class="mx-auto px-3 sm:px-6 lg:px-8 max-w-full lg:max-w-7xl">
            <!-- Mobile Stacked / Desktop Grid -->
            <div class="block lg:grid lg:grid-cols-3 lg:gap-6">
                <!-- Sidebar - Full width on mobile -->
                <aside class="lg:col-span-1 bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-4 lg:mb-0 lg:sticky lg:top-4">
                    <!-- Progress -->
                    <div class="mb-5 p-4 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-600 rounded-lg">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="font-semibold text-sm text-gray-700 dark:text-gray-300">Seu Progresso</h3>
                            <span id="progress-text-{{ $lesson->id }}" class="text-2xl font-bold text-blue-600">{{ $progress->percentage }}%</span>
                        </div>
                        <div class="w-full bg-gray-300 rounded-full h-3 dark:bg-gray-500 overflow-hidden">
                            <div id="progress-bar-{{ $lesson->id }}" class="bg-gradient-to-r from-blue-500 to-indigo-600 h-3 transition-all duration-300" 
                                 style="width: {{ $progress->percentage }}%"></div>
                        </div>
                        @if($progress->completed)
                            <p class="text-sm text-green-600 dark:text-green-400 mt-3 font-semibold">✅ Aula Concluída!</p>
                        @endif
                    </div>

                    <!-- Content Type -->
                    <div class="mb-4 p-3 bg-gray-50 dark:bg-gray-700 rounded text-center">
                        <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">Tipo de Conteúdo</p>
                        <p class="font-semibold text-sm capitalize">
                            @switch($lesson->content_type)
                                @case('video') 🎥 Vídeo @break
                                @case('pdf') 📄 PDF @break
                                @case('quiz') 🧪 Quiz @break
                                @case('text') 📝 Texto @break
                                @default {{ $lesson->content_type }}
                            @endswitch
                        </p>
                    </div>

                    <!-- Duration -->
                    @if($lesson->duration_seconds && $lesson->content_type === 'video')
                        <div class="mb-4 p-3 bg-gray-50 dark:bg-gray-700 rounded text-center">
                            <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">Duração</p>
                            <p class="font-semibold text-sm">⏱️ {{ floor($lesson->duration_seconds / 60) }}m {{ $lesson->duration_seconds % 60 }}s</p>
                        </div>
                    @endif

                    <!-- Resources -->
                    @if($pdfUrl || $videoUrl)
                        <div class="space-y-2 mb-4">
                            @if($pdfUrl)
                                <a href="{{ $pdfUrl }}" target="_blank" rel="noopener"
                                   class="w-full block px-4 py-3 bg-red-600 text-white rounded font-medium text-center text-sm min-h-10 flex items-center justify-center hover:bg-red-700 active:bg-red-800 transition">
                                    📄 Baixar PDF
                                </a>
                            @endif
                            
                            @if($lesson->content_type === 'video' && $videoUrl)
                                <button id="markDone"
                                        class="w-full px-4 py-3 bg-green-600 text-white rounded font-medium text-sm min-h-10 flex items-center justify-center hover:bg-green-700 active:bg-green-800 transition">
                                    {{ $progress->completed ? '✅ Concluído' : '✓ Marcar como Concluído' }}
                                </button>
                            @endif
                        </div>
                    @endif

                    <!-- Edit/Delete -->
                    <div class="pt-4 border-t border-gray-300 dark:border-gray-600 space-y-2">
                        <a href="{{ route('lessons.edit', $lesson) }}" 
                           class="w-full block px-4 py-3 bg-blue-600 text-white rounded font-medium text-center text-sm min-h-10 flex items-center justify-center hover:bg-blue-700 active:bg-blue-800 transition">
                            ✏️ Editar
                        </a>
                        <form method="POST" action="{{ route('lessons.destroy', $lesson) }}" class="inline-block w-full" onclick="return confirm('Tem certeza que deseja deletar esta aula?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-full px-4 py-3 bg-red-600 text-white rounded font-medium text-sm min-h-10 hover:bg-red-700 active:bg-red-800 transition">
                                🗑️ Deletar
                            </button>
                        </form>
                    </div>
            </aside>

            <!-- Main Content -->
            <main class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow p-3 sm:p-4 lg:p-6">
                <div class="mb-4">
                    @if($lesson->sub_title)
                        <h3 class="text-sm sm:text-base lg:text-lg text-gray-600 dark:text-gray-400">{{ $lesson->sub_title }}</h3>
                    @endif
                </div>

                <!-- Conteúdo Dinâmico -->
                @switch($lesson->content_type)
                    @case('video')
                        @if($videoUrl)
                            <div class="mb-4 sm:mb-6 rounded-lg overflow-hidden bg-black">
                                <video id="lesson-video" controls class="w-full" preload="metadata">
                                    <source src="{{ $videoUrl }}" type="video/mp4"/>
                                    Seu navegador não suporta vídeo HTML5.
                                </video>
                            </div>
                        @else
                            <div class="p-6 sm:p-8 text-center text-gray-500 rounded-lg bg-gray-100 dark:bg-gray-700 mb-4">
                                <p class="text-3xl sm:text-4xl mb-2">🎥</p>
                                <p class="text-sm sm:text-base">Vídeo não disponível</p>
                            </div>
                        @endif
                        @break

                    @case('pdf')
                        @if($pdfUrl)
                            <div class="rounded-lg overflow-hidden border border-gray-300 dark:border-gray-600">
                                <iframe src="{{ $pdfUrl }}" class="w-full" style="height: 500px; min-height: 60vh;"></iframe>
                            </div>
                        @else
                            <div class="p-6 sm:p-8 text-center text-gray-500 rounded-lg bg-gray-100 dark:bg-gray-700">
                                <p class="text-3xl sm:text-4xl mb-2">📄</p>
                                <p class="text-sm sm:text-base">PDF não disponível</p>
                            </div>
                        @endif
                        @break

                    @case('quiz')
                        @if($lesson->quiz_data)
                            <div id="quiz-container" class="space-y-3 sm:space-y-4">
                                @php
                                    $quiz = is_array($lesson->quiz_data) ? $lesson->quiz_data : json_decode($lesson->quiz_data, true);
                                @endphp
                                @if(isset($quiz['questions']) && is_array($quiz['questions']))
                                    @foreach($quiz['questions'] as $index => $question)
                                        <div class="p-3 sm:p-4 border rounded-lg bg-gray-50 dark:bg-gray-700">
                                            <h4 class="font-semibold mb-3 text-sm sm:text-base">{{ $index + 1 }}. {{ $question['question'] ?? 'Pergunta' }}</h4>
                                            <div class="space-y-2">
                                                @foreach(($question['options'] ?? []) as $optIndex => $option)
                                                    <label class="flex items-start p-3 border rounded hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer transition">
                                                        <input type="radio" name="question_{{ $index }}" value="{{ $optIndex }}" class="mr-3 mt-1 flex-shrink-0">
                                                        <span class="text-sm sm:text-base">{{ $option }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                    <button type="submit" class="w-full px-4 py-3 bg-blue-600 text-white rounded font-medium text-sm sm:text-base min-h-10 hover:bg-blue-700 active:bg-blue-800 transition" onclick="submitQuiz()">
                                        ✓ Enviar Respostas
                                    </button>
                                @else
                                    <p class="text-gray-500 text-sm sm:text-base">Quiz não configurado</p>
                                @endif
                            </div>
                        @endif
                        @break

                    @default
                        <div class="prose dark:prose-invert max-w-none">
                            <p class="text-gray-700 dark:text-gray-300 text-sm sm:text-base">
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
