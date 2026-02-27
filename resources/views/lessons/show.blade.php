<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div class="flex-1 min-w-0">
                <h2 class="font-semibold text-lg sm:text-2xl text-gray-800 dark:text-gray-200 leading-tight truncate">
                    {{ $lesson->title }}
                </h2>
                <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1 line-clamp-2">
                    {{ $lesson->subModule->module->course->title }} › {{ $lesson->subModule->module->title }}
                </p>
            </div>
            <a href="{{ route('courses.show', $lesson->subModule->module->course) }}"
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
                            <span id="progress-text-{{ $lesson->id }}" class="text-2xl font-bold text-blue-600" title="{{ $progress->percentage }}% completado">{{ $progress->percentage }}%</span>
                        </div>
                        <div class="w-full bg-gray-300 rounded-full h-3 dark:bg-gray-500 overflow-hidden relative">
                            <div id="progress-bar-{{ $lesson->id }}" class="bg-gradient-to-r from-blue-500 to-indigo-600 h-3 transition-all duration-500 ease-out" 
                                 style="width: {{ $progress->percentage }}%" title="{{ $progress->percentage }}% completado"></div>
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
                                @case('audio') 🔊 Áudio @break
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
                    @if($pdfUrl || $videoUrl || $audioUrl || !empty($audioList))
                        <div class="space-y-2 mb-4">
                            @if($pdfUrl)
                                <a href="{{ $pdfUrl }}" target="_blank" rel="noopener"
                                   class="w-full block px-4 py-3 bg-red-600 text-white rounded font-medium text-center text-sm min-h-10 flex items-center justify-center hover:bg-red-700 active:bg-red-800 transition">
                                    📄 Baixar PDF
                                </a>
                            @endif

                            @if($audioUrl || !empty($audioList))
                                @if($audioUrl)
                                    <a href="{{ $audioUrl }}" target="_blank" rel="noopener"
                                       class="w-full block px-4 py-3 bg-indigo-600 text-white rounded font-medium text-center text-sm min-h-10 flex items-center justify-center hover:bg-indigo-700 active:bg-indigo-800 transition">
                                        🔊 Baixar Áudio
                                    </a>
                                @endif
                                @if(!empty($audioList) && count($audioList) > 0)
                                    <div class="space-y-1">
                                        @foreach($audioList as $idx => $audioItem)
                                            @php
                                                $audioItemUrl = asset('storage/' . ltrim($audioItem, '/'));
                                                $audioLabel = pathinfo($audioItem, PATHINFO_FILENAME);
                                                $audioLabel = preg_replace('/\b(audio|audio completo|completo|complete)\b/i', '', $audioLabel);
                                                $audioLabel = trim(preg_replace('/\s+/', ' ', $audioLabel));
                                                if (!$audioLabel) {
                                                    $audioLabel = 'Áudio ' . ($idx + 1);
                                                }
                                            @endphp
                                            <a href="{{ $audioItemUrl }}" target="_blank" rel="noopener"
                                               class="w-full block px-3 py-2 bg-indigo-500 text-white rounded font-medium text-center text-xs min-h-9 flex items-center justify-center hover:bg-indigo-600 active:bg-indigo-700 transition truncate">
                                                🔊 {{ $audioLabel }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            @endif
                            
                            @if($lesson->content_type !== 'video')
                                <button id="markDone"
                                        class="w-full px-4 py-3 bg-green-600 text-white rounded font-medium text-sm min-h-10 flex items-center justify-center hover:bg-green-700 active:bg-green-800 transition disabled:opacity-50 disabled:cursor-not-allowed"
                                        data-loading-text="⏳ Processando...">
                                    {{ $progress->completed ? '✅ Concluído' : '✓ Marcar como Concluído' }}
                                </button>
                            @elseif($videoUrl)
                                <button id="markDone"
                                        class="w-full px-4 py-3 bg-green-600 text-white rounded font-medium text-sm min-h-10 flex items-center justify-center hover:bg-green-700 active:bg-green-800 transition disabled:opacity-50 disabled:cursor-not-allowed"
                                        data-loading-text="⏳ Processando...">
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
                            <button type="submit" class="w-full px-4 py-3 bg-red-600 text-white rounded font-medium text-sm min-h-10 hover:bg-red-700 active:bg-red-800 transition disabled:opacity-50 disabled:cursor-not-allowed" data-loading-text="⏳ Deletando...">
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

                    @case('audio')
                        @if(!empty($lesson->audio_list))
                            <div class="mb-4 sm:mb-6 space-y-3">
                                @foreach($lesson->audio_list as $audioItem)
                                    @php
                                        $audioItemUrl = asset('storage/' . ltrim($audioItem, '/'));
                                        $audioLabel = pathinfo($audioItem, PATHINFO_FILENAME);
                                        $audioLabel = preg_replace('/\b(audio|audio completo|completo|complete)\b/i', '', $audioLabel);
                                        $audioLabel = trim(preg_replace('/\s+/', ' ', $audioLabel));
                                    @endphp
                                    <div class="rounded-lg overflow-hidden border border-gray-300 dark:border-gray-600 p-4 bg-gray-50 dark:bg-gray-700">
                                        <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">{{ $audioLabel }}</p>
                                        <audio controls class="w-full lesson-audio">
                                            <source src="{{ $audioItemUrl }}" type="audio/mpeg"/>
                                            Seu navegador não suporta áudio HTML5.
                                        </audio>
                                    </div>
                                @endforeach
                            </div>
                        @elseif($audioUrl)
                            <div class="mb-4 sm:mb-6 rounded-lg overflow-hidden border border-gray-300 dark:border-gray-600 p-4 bg-gray-50 dark:bg-gray-700">
                                <audio controls class="w-full lesson-audio">
                                    <source src="{{ $audioUrl }}" type="audio/mpeg"/>
                                    Seu navegador não suporta áudio HTML5.
                                </audio>
                            </div>
                        @else
                            <div class="p-6 sm:p-8 text-center text-gray-500 rounded-lg bg-gray-100 dark:bg-gray-700 mb-4">
                                <p class="text-3xl sm:text-4xl mb-2">🔊</p>
                                <p class="text-sm sm:text-base">Áudio não disponível</p>
                            </div>
                        @endif
                        @break

                    @case('pdf')
                        @if($pdfUrl)
                            <!-- Layout: Áudio em cima (se existir) + PDF abaixo em desktop, stacked em mobile -->
                            <div>
                                <!-- Áudio Player (se houver) -->
                                @if($audioUrl || !empty($audioList))
                                    <div class="mb-4">
                                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">🔊 Áudio da Aula</h4>
                                        @if(!empty($audioList))
                                            <div class="space-y-3">
                                                @foreach($audioList as $audioItem)
                                                    @php
                                                        $audioItemUrl = asset('storage/' . ltrim($audioItem, '/'));
                                                        $audioLabel = pathinfo($audioItem, PATHINFO_FILENAME);
                                                        $audioLabel = preg_replace('/\b(audio|audio completo|completo|complete)\b/i', '', $audioLabel);
                                                        $audioLabel = trim(preg_replace('/\s+/', ' ', $audioLabel));
                                                    @endphp
                                                    <div class="rounded-lg overflow-hidden border border-gray-300 dark:border-gray-600 p-3 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-gray-700 dark:to-gray-600">
                                                        @if($audioLabel)
                                                            <p class="text-xs text-gray-600 dark:text-gray-300 mb-2 font-medium">{{ $audioLabel }}</p>
                                                        @endif
                                                        <audio controls class="w-full lesson-audio">
                                                            <source src="{{ $audioItemUrl }}" type="audio/mpeg"/>
                                                            Seu navegador não suporta áudio HTML5.
                                                        </audio>
                                                    </div>
                                                @endforeach
                                            </div>
                        @elseif($audioUrl)
                                            <div class="rounded-lg overflow-hidden border border-gray-300 dark:border-gray-600 p-3 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-gray-700 dark:to-gray-600">
                                                <audio controls class="w-full lesson-audio">
                                                    <source src="{{ $audioUrl }}" type="audio/mpeg"/>
                                                    Seu navegador não suporta áudio HTML5.
                                                </audio>
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                <!-- PDF Viewer -->
                                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">📄 Leitura</h4>
                                <div class="rounded-lg overflow-hidden border border-gray-300 dark:border-gray-600">
                                    <div id="pdf-scroll" class="overflow-y-auto" style="height: 70vh; max-height: calc(100vh - 150px);">
                                        <div id="pdf-viewer" data-pdf-url="{{ $pdfUrl }}" class="p-4 space-y-4"></div>
                                    </div>
                                    <noscript>
                                        <iframe src="{{ $pdfUrl }}" class="w-full" style="height: 500px; min-height: 60vh; max-height: calc(100vh - 150px);"></iframe>
                                    </noscript>
                                </div>
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
        const audioElements = document.querySelectorAll('.lesson-audio');
        const pdfViewer = document.getElementById('pdf-viewer');
        const pdfScroll = document.getElementById('pdf-scroll');
        const progressBar = document.getElementById('progress-bar-{{ $lesson->id }}');
        const progressText = document.getElementById('progress-text-{{ $lesson->id }}');

        function sendProgress(current, duration, completed = false) {
            const safeCurrent = Number.isFinite(current) ? current : 0;
            const safeDuration = Number.isFinite(duration) && duration > 0 ? duration : 1;

            fetch('{{ route('progress.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({
                    lesson_id: lessonId,
                    current_time: safeCurrent,
                    duration: safeDuration,
                    completed: completed
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data?.progress?.percentage !== undefined) {
                    const percentage = data.progress.percentage;
                    progressText.textContent = percentage + '%';
                    progressBar.style.width = percentage + '%';
                    
                    if (videoElement) {
                        localStorage.setItem(`video_progress_${lessonId}`, safeCurrent);
                    }
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
                saveTimer = setInterval(() => sendProgress(videoElement.currentTime || 0, videoElement.duration || 1, false), 10000);
            });
            videoElement.addEventListener('pause', () => {
                if (saveTimer) clearInterval(saveTimer);
                sendProgress(videoElement.currentTime || 0, videoElement.duration || 1, false);
            });
            videoElement.addEventListener('ended', () => {
                if (saveTimer) clearInterval(saveTimer);
                sendProgress(videoElement.currentTime || 0, videoElement.duration || 1, true);
            });
        }

        if (audioElements.length > 0) {
            const audioHandler = () => {
                // Calcula o tempo total de todos os áudios e o tempo já ouvido
                let totalDuration = 0;
                let totalCurrent = 0;
                
                Array.from(audioElements).forEach((audio) => {
                    const duration = audio.duration || 0;
                    const current = audio.currentTime || 0;
                    totalDuration += duration;
                    totalCurrent += current;
                });

                // Evita divisão por zero
                const safeDuration = totalDuration > 0 ? totalDuration : 1;
                const completed = totalCurrent > 0 && (totalCurrent / safeDuration) >= 0.95;
                
                sendProgress(totalCurrent, safeDuration, completed);
            };

            audioElements.forEach((audio) => {
                audio.addEventListener('timeupdate', audioHandler);
                audio.addEventListener('ended', audioHandler);
                audio.addEventListener('pause', audioHandler);
            });
        }

        function setupPdfTracking() {
            if (!pdfViewer || !pdfScroll) {
                return;
            }

            const pdfUrl = pdfViewer.dataset.pdfUrl;
            if (!pdfUrl) {
                return;
            }

            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js';
            script.onload = () => {
                const pdfjsLib = window['pdfjs-dist/build/pdf'];
                pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

                pdfjsLib.getDocument(pdfUrl).promise.then((pdf) => {
                    const totalPages = pdf.numPages;
                    const renderPage = (pageNum) => {
                        pdf.getPage(pageNum).then((page) => {
                            const viewport = page.getViewport({ scale: 1.2 });
                            const canvas = document.createElement('canvas');
                            const context = canvas.getContext('2d');
                            canvas.height = viewport.height;
                            canvas.width = viewport.width;
                            canvas.className = 'w-full bg-white rounded shadow';
                            pdfViewer.appendChild(canvas);

                            const renderContext = { canvasContext: context, viewport };
                            page.render(renderContext);
                        });
                    };

                    for (let i = 1; i <= totalPages; i += 1) {
                        renderPage(i);
                    }
                });
            };
            document.body.appendChild(script);

            let pdfTimer;
            const onScroll = () => {
                if (pdfTimer) clearTimeout(pdfTimer);
                pdfTimer = setTimeout(() => {
                    const scrollTop = pdfScroll.scrollTop;
                    const scrollHeight = pdfScroll.scrollHeight - pdfScroll.clientHeight;
                    const percent = scrollHeight > 0 ? Math.round((scrollTop / scrollHeight) * 100) : 0;
                    sendProgress(percent, 100, percent >= 95);
                }, 200);
            };

            pdfScroll.addEventListener('scroll', onScroll);
        }

        setupPdfTracking();

        document.getElementById('markDone')?.addEventListener('click', () => sendProgress(100, 100, true));

        function submitQuiz() {
            alert('Quiz enviado! (Função de validação será implementada)');
            sendProgress(true);
        }

        // Add loading states to all buttons
        document.querySelectorAll('button[type="submit"]').forEach(button => {
            button.addEventListener('click', function(e) {
                if (this.hasAttribute('data-loading-text')) {
                    this.disabled = true;
                    const originalText = this.textContent;
                    this.textContent = this.getAttribute('data-loading-text');
                    
                    // Re-enable button after 5 seconds if form doesn't submit
                    setTimeout(() => {
                        this.disabled = false;
                        this.textContent = originalText;
                    }, 5000);
                }
            });
        });

        // Handle form submissions
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn && submitBtn.hasAttribute('data-loading-text')) {
                    submitBtn.disabled = true;
                    const originalText = submitBtn.textContent;
                    submitBtn.textContent = submitBtn.getAttribute('data-loading-text');
                }
            });
        });
    </script>
</x-app-layout>
