<?php

namespace App\Console\Commands;

use App\Models\Lesson;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class DebugPdfsDetailed extends Command
{
    protected $signature = 'debug:pdfs-detailed';
    protected $description = 'Debug detalhado: procura por PDFs e lessons';

    public function handle()
    {
        $this->info('=== LESSONS NO BANCO ===');
        $lessonsCount = Lesson::count();
        $this->line("Total de lessons: {$lessonsCount}");
        
        $lessonsWithPdf = Lesson::whereNotNull('pdf_key')->count();
        $this->line("Lessons com pdf_key: {$lessonsWithPdf}");
        $this->newLine();

        // Mostrar primeiras lessons
        $this->line("Primeiras 5 lessons:");
        Lesson::limit(5)->each(function ($lesson) {
            $this->line("  ID {$lesson->id}: {$lesson->title}");
            $this->line("    video_key: " . ($lesson->video_key ?? 'null'));
            $this->line("    pdf_key: " . ($lesson->pdf_key ?? 'null'));
        });

        $this->newLine();
        $this->info('=== PROCURANDO PDFs NO FILESYSTEM ===');

        $paths = [
            storage_path('app/public/pdfs'),
            storage_path('app/public/lessons'),
            storage_path('app/public/videos'),
            base_path('storage/pdfs'),
            base_path('public/pdfs'),
        ];

        foreach ($paths as $path) {
            $this->line("Verificando: {$path}");
            if (!is_dir($path)) {
                $this->line("  ❌ Não existe");
                continue;
            }

            $files = File::files($path);
            $pdfFiles = array_filter($files, function ($file) {
                return strtolower($file->getExtension()) === 'pdf';
            });

            if (empty($pdfFiles)) {
                $this->line("  ⚠️  Nenhum PDF encontrado");
            } else {
                $this->line("  ✅ " . count($pdfFiles) . " PDF(s) encontrado(s):");
                foreach (array_slice($pdfFiles, 0, 5) as $file) {
                    $this->line("    - " . $file->getFilename());
                }
            }
        }

        $this->newLine();
        $this->info('=== PROCURANDO PDFs RECURSIVAMENTE ===');
        
        // Buscar recursivamente
        $videosPath = storage_path('app/public/videos');
        if (is_dir($videosPath)) {
            $allFiles = File::allFiles($videosPath);
            $pdfFiles = array_filter($allFiles, function ($file) {
                return strtolower($file->getExtension()) === 'pdf';
            });

            if (empty($pdfFiles)) {
                $this->warn("Nenhum PDF encontrado em {$videosPath}");
            } else {
                $pdfCount = count($pdfFiles);
                $this->line("✅ {$pdfCount} PDF(s) encontrado(s):");
                foreach (array_slice($pdfFiles, 0, 10) as $file) {
                    $relativePath = str_replace(storage_path('app/public/'), '', $file->getPathname());
                    $this->line("  - {$relativePath}");
                }
            }
        }

        return 0;
    }
}
