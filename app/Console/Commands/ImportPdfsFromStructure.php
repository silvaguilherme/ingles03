<?php

namespace App\Console\Commands;

use App\Models\SubModule;
use App\Models\Lesson;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportPdfsFromStructure extends Command
{
    protected $signature = 'import:pdfs {--base-path= : Caminho base dos submodulos}';
    protected $description = 'Importar PDFs da estrutura /XX/pdf/ e associar às lessons';

    public function handle()
    {
        $basePath = $this->option('base-path') ?? storage_path('app/public/videos/ingles/01-fundacao');

        if (!is_dir($basePath)) {
            $this->error("Diretório não encontrado: {$basePath}");
            return 1;
        }

        $this->info('=== IMPORTANDO PDFs ===');
        $this->newLine();

        $subModules = SubModule::all();
        $linked = 0;
        $notFound = 0;

        foreach ($subModules as $subModule) {
            $folderNum = str_pad($subModule->title, 2, '0', STR_PAD_LEFT);
            $pdfDir = $basePath . '/' . $folderNum . '/pdf';

            if (!is_dir($pdfDir)) {
                $this->line("ℹ️  {$folderNum}/pdf - Pasta não existe");
                continue;
            }

            $pdfFiles = File::files($pdfDir);
            if (empty($pdfFiles)) {
                $this->line("ℹ️  {$folderNum}/pdf - Nenhum PDF encontrado");
                continue;
            }

            $pdfCount = count($pdfFiles);
            $this->line("📂 {$folderNum}/pdf - {$pdfCount} PDF(s):");

            foreach ($pdfFiles as $pdfFile) {
                $filename = $pdfFile->getFilename();
                $relativePath = "videos/ingles/01-fundacao/{$folderNum}/pdf/{$filename}";

                // Tentar encontrar a lesson pelo título
                $lesson = Lesson::where('title', 'like', trim($pdfFile->getBasename('.' . $pdfFile->getExtension())) . '%')
                    ->orWhere('title', 'like', '%' . trim($pdfFile->getBasename('.' . $pdfFile->getExtension())) . '%')
                    ->first();

                if ($lesson) {
                    $lesson->update(['pdf_key' => $relativePath]);
                    $this->line("  ✅ {$filename} → Lesson #{$lesson->id}: {$lesson->title}");
                    $linked++;
                } else {
                    $this->warn("  ❌ {$filename} → Nenhuma lesson encontrada");
                    $notFound++;
                }
            }

            $this->newLine();
        }

        $this->info('=== RESULTADO ===');
        $this->line("✅ Associados: {$linked}");
        $this->warn("❌ Não encontrados: {$notFound}");

        return 0;
    }
}
