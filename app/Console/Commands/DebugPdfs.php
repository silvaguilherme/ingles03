<?php

namespace App\Console\Commands;

use App\Models\Lesson;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DebugPdfs extends Command
{
    protected $signature = 'debug:pdfs';
    protected $description = 'Debug de PDFs nas lessons';

    public function handle()
    {
        $this->info('=== VERIFICANDO PDFs ===');
        $this->newLine();

        $lessons = Lesson::whereNotNull('pdf_key')->get(['id', 'title', 'pdf_key']);

        if ($lessons->isEmpty()) {
            $this->warn('Nenhuma lesson com pdf_key encontrada!');
            return;
        }

        $this->line("Total de lessons com PDF: {$lessons->count()}");
        $this->newLine();

        foreach ($lessons as $lesson) {
            $pdfKey = ltrim($lesson->pdf_key, '/');
            $exists = Storage::disk('public')->exists($pdfKey);
            $diskPath = storage_path('app/public/' . $pdfKey);
            $realExists = file_exists($diskPath);
            
            $status = $realExists ? '✅' : '❌';
            $this->line("{$status} Lesson ID {$lesson->id}: {$lesson->title}");
            $this->line("   PDF Key: {$lesson->pdf_key}");
            $this->line("   Storage::exists(): {$exists}");
            $this->line("   File exists: {$realExists}");
            
            if ($realExists) {
                $size = filesize($diskPath);
                $this->line("   File size: " . number_format($size) . " bytes");
                $this->line("   URL: " . asset('storage/' . $pdfKey));
            }
            
            $this->newLine();
        }

        // Verificar se o storage link existe
        $this->info('=== VERIFICAÇÃO DE STORAGE LINK ===');
        $linkPath = public_path('storage');
        if (is_link($linkPath)) {
            $this->line("✅ Storage link EXISTS");
            $this->line("   Points to: " . readlink($linkPath));
        } else {
            $this->warn("❌ Storage link NÃO EXISTE!");
            $this->line("Execute: php artisan storage:link");
        }

        return 0;
    }
}
