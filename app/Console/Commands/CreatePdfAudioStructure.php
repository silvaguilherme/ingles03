<?php

namespace App\Console\Commands;

use App\Models\SubModule;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CreatePdfAudioStructure extends Command
{
    protected $signature = 'create:pdf-audio-structure {--base-path= : Caminho base dos submodulos}';
    protected $description = 'Criar estrutura de pastas pdf/ e audio/ para cada submodulo';

    public function handle()
    {
        $basePath = $this->option('base-path') ?? storage_path('app/public/videos/ingles/01-fundacao');

        if (!is_dir($basePath)) {
            $this->error("Diretório não encontrado: {$basePath}");
            return 1;
        }

        $this->info('=== CRIANDO ESTRUTURA PDF E AUDIO ===');
        $this->newLine();

        // Obter todos os submodulos
        $subModules = SubModule::all();
        $created = 0;

        foreach ($subModules as $subModule) {
            // Estrutura esperada: /00, /01, /03, etc baseado no title
            $subFolderNum = str_pad($subModule->title, 2, '0', STR_PAD_LEFT);
            $subFolderPath = $basePath . '/' . $subFolderNum;

            if (!is_dir($subFolderPath)) {
                $this->warn("Pasta não existe: {$subFolderPath}");
                continue;
            }

            // Criar pasta pdf
            $pdfPath = $subFolderPath . '/pdf';
            if (!is_dir($pdfPath)) {
                mkdir($pdfPath, 0755, true);
                $this->line("✅ Criada: {$pdfPath}");
                $created++;
            } else {
                $this->line("ℹ️  Existe: {$pdfPath}");
            }

            // Criar pasta audio
            $audioPath = $subFolderPath . '/audio';
            if (!is_dir($audioPath)) {
                mkdir($audioPath, 0755, true);
                $this->line("✅ Criada: {$audioPath}");
                $created++;
            } else {
                $this->line("ℹ️  Existe: {$audioPath}");
            }

            $this->newLine();
        }

        $this->info("Total de pastas criadas: {$created}");

        return 0;
    }
}
