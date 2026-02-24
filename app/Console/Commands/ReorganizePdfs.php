<?php

namespace App\Console\Commands;

use App\Models\SubModule;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ReorganizePdfs extends Command
{
    protected $signature = 'organize:pdfs {--base-path= : Caminho base dos submodulos} {--dry-run : Simular sem mover}';
    protected $description = 'Reorganizar PDFs existentes para as pastas /pdf de cada submodulo';

    public function handle()
    {
        $basePath = $this->option('base-path') ?? storage_path('app/public/videos/ingles/01-fundacao');
        $dryRun = $this->option('dry-run');

        if (!is_dir($basePath)) {
            $this->error("Diretório não encontrado: {$basePath}");
            return 1;
        }

        $this->info('=== REORGANIZANDO PDFs ===');
        if ($dryRun) {
            $this->warn('(DRY RUN - nenhum arquivo será movido)');
        }
        $this->newLine();

        // Procurar todos os PDFs no diretório
        $allFiles = File::allFiles($basePath);
        $pdfFiles = array_filter($allFiles, function ($file) {
            return strtolower($file->getExtension()) === 'pdf';
        });

        $this->line("PDFs encontrados: " . count($pdfFiles));
        $this->newLine();

        $moved = 0;
        $failed = 0;

        // Agrupar PDFs por submodulo
        foreach ($pdfFiles as $pdfFile) {
            $pdfPath = str_replace('\\', '/', $pdfFile->getPathname());
            $filename = $pdfFile->getFilename();

            // Extrair o número do submodulo do caminho
            // Esperado: /01-fundacao/00/arquivo.pdf ou /01-fundacao/01/arquivo.pdf
            if (preg_match('~\/(\d{1,3})\/[^\/]*\.pdf$~', $pdfPath, $matches)) {
                $folderNum = $matches[1];
                $subModule = SubModule::where('title', str_pad($folderNum, 2, '0', STR_PAD_LEFT))->first()
                    ?? SubModule::where('title', (string)(int)$folderNum)->first();

                if (!$subModule) {
                    $this->warn("❌ {$filename} - Submodulo não encontrado para pasta {$folderNum}");
                    $failed++;
                    continue;
                }

                // Destino: /XX/pdf/arquivo.pdf
                $destDir = $basePath . '/' . str_pad($folderNum, 2, '0', STR_PAD_LEFT) . '/pdf';
                $destPath = $destDir . '/' . $filename;

                // Pular se o arquivo já está no destino
                if (dirname($pdfPath) === $destDir) {
                    $this->line("ℹ️  {$filename} - Já está no destino ({$folderNum}/pdf/)");
                    continue;
                }

                if (file_exists($destPath)) {
                    $this->warn("⚠️  {$filename} - Arquivo já existe no destino, pulando");
                    continue;
                }

                if ($dryRun) {
                    $this->line("→ {$filename}");
                    $this->line("  De: {$pdfPath}");
                    $this->line("  Para: {$destPath}");
                } else {
                    // Criar diretório se não existir
                    if (!is_dir($destDir)) {
                        mkdir($destDir, 0755, true);
                    }

                    // Mover arquivo
                    if (rename($pdfPath, $destPath)) {
                        $this->line("✅ {$filename} → {$folderNum}/pdf/");
                        $moved++;
                    } else {
                        $this->warn("❌ {$filename} - Erro ao mover");
                        $failed++;
                    }
                }
            }
        }

        $this->newLine();
        $this->info('=== RESULTADO ===');
        if ($dryRun) {
            $this->line("(DRY RUN - Nenhum arquivo foi movido)");
        } else {
            $this->line("✅ Movidos: {$moved}");
            $this->warn("❌ Falhados: {$failed}");
        }

        return 0;
    }
}
