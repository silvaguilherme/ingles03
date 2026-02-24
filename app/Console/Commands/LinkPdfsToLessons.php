<?php

namespace App\Console\Commands;

use App\Models\Lesson;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class LinkPdfsToLessons extends Command
{
    protected $signature = 'link:pdfs';
    protected $description = 'Associar PDFs às lessons baseado no nome do arquivo';

    public function handle()
    {
        $this->info('=== ASSOCIANDO PDFs ÀS LESSONS ===');
        $this->newLine();

        $videosPath = storage_path('app/public/videos');
        if (!is_dir($videosPath)) {
            $this->error("Diretório não encontrado: {$videosPath}");
            return 1;
        }

        // Procurar todos os PDFs
        $allFiles = File::allFiles($videosPath);
        $pdfFiles = array_filter($allFiles, function ($file) {
            return strtolower($file->getExtension()) === 'pdf';
        });

        $this->line("PDFs encontrados: " . count($pdfFiles));
        $this->newLine();

        $linked = 0;
        $notFound = 0;

        foreach ($pdfFiles as $pdfFile) {
            $pdfPath = str_replace('\\', '/', $pdfFile->getPathname());
            $relativePath = str_replace(storage_path('app/public/'), '', $pdfPath);
            $filename = $pdfFile->getFilename();
            $nameWithoutExt = $pdfFile->getBasename('.' . $pdfFile->getExtension());

            // Tentar encontrar a lesson pelo título (sem extensão)
            // Estratégia: procurar por LIKE para flexibilidade
            $lesson = Lesson::where('title', 'like', trim($nameWithoutExt) . '%')
                ->orWhere('title', 'like', '%' . trim($nameWithoutExt) . '%')
                ->first();

            if ($lesson) {
                // Atualizar o pdf_key
                $lesson->update(['pdf_key' => $relativePath]);
                $this->line("✅ {$filename}");
                $this->line("   → Lesson: {$lesson->title} (ID: {$lesson->id})");
                $linked++;
            } else {
                $this->warn("❌ {$filename}");
                $this->line("   Nenhuma lesson encontrada para: {$nameWithoutExt}");
                $notFound++;
            }
        }

        $this->newLine();
        $this->info('=== RESULTADO ===');
        $this->line("✅ Associados: {$linked}");
        $this->warn("❌ Não encontrados: {$notFound}");

        return 0;
    }
}
