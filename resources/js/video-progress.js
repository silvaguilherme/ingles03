/**
 * Video Progress Tracker
 * Salva o progresso do vídeo automaticamente
 */

class VideoProgressTracker {
    constructor(videoElementId, lessonId, token) {
        this.video = document.getElementById(videoElementId);
        this.lessonId = lessonId;
        this.token = token;
        this.saveInterval = null;
        this.lastSavedTime = 0;
        this.saveThreshold = 10; // Salva a cada 10 segundos

        if (!this.video) {
            console.warn('Video element not found:', videoElementId);
            return;
        }

        this.initListeners();
    }

    initListeners() {
        // Salva progresso enquanto assiste
        this.video.addEventListener('timeupdate', () => this.trackProgress());
        
        // Salva quando pausa
        this.video.addEventListener('pause', () => this.saveProgress());
        
        // Salva quando fecha/navegando
        window.addEventListener('beforeunload', () => this.saveProgress());
        
        // Restaura posição salva se houver
        this.restoreProgress();
    }

    trackProgress() {
        const currentTime = this.video.currentTime;
        const duration = this.video.duration;

        // Salva a cada N segundos para não sobrecarregar servidor
        if (Math.abs(currentTime - this.lastSavedTime) >= this.saveThreshold) {
            this.saveProgress();
        }
    }

    saveProgress() {
        const currentTime = this.video.currentTime;
        const duration = this.video.duration;
        const completed = currentTime / duration >= 0.95; // 95% = concluído

        // Evita requisições simultâneas
        if (this.lastSavedTime === currentTime) return;
        this.lastSavedTime = currentTime;

        fetch('/progress', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': this.token,
            },
            body: JSON.stringify({
                lesson_id: this.lessonId,
                current_time: currentTime,
                duration: duration,
                completed: completed,
            }),
        })
        .then(response => response.json())
        .then(data => {
            console.log('Progress saved:', data);
            // Atualiza UI se necessário
            this.updateProgressBar(data.progress.percentage);
        })
        .catch(error => console.error('Error saving progress:', error));
    }

    restoreProgress() {
        // Tenta carregar de localStorage (client-side)
        const saved = localStorage.getItem(`video_progress_${this.lessonId}`);
        if (saved) {
            const time = parseFloat(saved);
            this.video.currentTime = time;
            console.log('Progress restored:', time);
        }
    }

    updateProgressBar(percentage) {
        const progressBar = document.getElementById(`progress-bar-${this.lessonId}`);
        if (progressBar) {
            progressBar.style.width = percentage + '%';
            document.getElementById(`progress-text-${this.lessonId}`).textContent = percentage + '%';
        }

        // Salva também em localStorage para referência rápida
        localStorage.setItem(`video_progress_${this.lessonId}`, this.video.currentTime);
    }
}

// Inicializa quando o DOM carregar
document.addEventListener('DOMContentLoaded', function() {
    const videoElement = document.getElementById('lesson-video');
    const lessonId = document.body.dataset.lessonId;
    const token = document.querySelector('meta[name="csrf-token"]').content;

    if (videoElement && lessonId) {
        new VideoProgressTracker('lesson-video', lessonId, token);
    }
});
