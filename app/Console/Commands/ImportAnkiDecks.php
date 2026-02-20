<?php

namespace App\Console\Commands;

use App\Models\SubModule;
use App\Services\AnkiImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportAnkiDecks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'anki:import {--path= : Caminho base dos submodulos}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Importar automaticamente arquivos APKG de cada submodulo';

    protected $importService;

    /**
     * Create a new command instance.
     */
    public function __construct(AnkiImportService $importService)
    {
        parent::__construct();
        $this->importService = $importService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando importação de decks Anki...');
        $this->info('Procurando por arquivos APKG nas pastas dos submodulos...');

        $importedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;

        // Padrões de caminhos onde APKG podem estar
        $basePaths = [
            storage_path('app/public/videos'),  // Seu padrão atual
            storage_path('app/submodules'),     // Padrão alternativo
            base_path('storage/videos'),         // Outro padrão
        ];

        // Encontrar arquivos APKG recursivamente
        $apkgFiles = [];
        foreach ($basePaths as $basePath) {
            if (is_dir($basePath)) {
                $foundFiles = File::glob($basePath . '/**/*.apkg', GLOB_BRACE);
                $apkgFiles = array_merge($apkgFiles, $foundFiles ?: []);
            }
        }

        if (empty($apkgFiles)) {
            $this->warn("⚠️  Nenhum arquivo APKG encontrado em:");
            foreach ($basePaths as $path) {
                $this->line("   - {$path}");
            }
            $this->info("\nTente especificar o caminho:");
            $this->line("   php artisan anki:import --path=/seu/caminho");
            return 1;
        }

        $this->info("🔍 Encontrados " . count($apkgFiles) . " arquivo(s) APKG");
        $this->newLine();

        // Varrer todos os submodulos para tentar mapear
        $submodules = SubModule::all();
        
        // Se temos um path customizado
        $customPath = $this->option('path');
        if ($customPath) {
            if (!is_dir($customPath)) {
                $this->error("❌ Diretório não encontrado: {$customPath}");
                return 1;
            }
            $apkgFiles = File::glob($customPath . '/**/*.apkg', GLOB_BRACE);
        }

        // Agrupar APKGs por caminho
        foreach ($apkgFiles as $apkgFile) {
            $filename = basename($apkgFile);
            $filePath = str_replace('\\', '/', $apkgFile);
            
            $this->line("📁 Processando: {$filePath}");

            // Tentar encontrar o submodulo baseado no caminho
            $submoduleId = null;
            $possibleSubmodule = null;

            // Procurar número no caminho (ex: /01/, /02/, etc)
            if (preg_match('/\/(\d+)(?:\/|$)/', $filePath, $matches)) {
                $possibleNum = (int)$matches[1];
                $possibleSubmodule = $submodules->firstWhere('id', $possibleNum);
            }

            // Se não encontrou por número, procurar por título
            if (!$possibleSubmodule) {
                foreach ($submodules as $sub) {
                    if (stripos($filePath, strtolower($sub->title)) !== false) {
                        $possibleSubmodule = $sub;
                        break;
                    }
                }
            }

            if (!$possibleSubmodule) {
                $this->warn("   ⚠️  Não foi possível identificar o submodulo para este arquivo");
                $this->line("   💡 Dica: O ID do submodulo deve estar no caminho ou no nome");
                $skippedCount++;
                continue;
            }

            $submoduleId = $possibleSubmodule->id;
            $this->line("   ✓ Identificado submodulo: {$possibleSubmodule->title} (ID: {$submoduleId})");

            try {
                // Copiar arquivo para o storage
                $destination = 'anki-decks/' . uniqid() . '_' . $filename;
                File::copy($apkgFile, storage_path('app/' . $destination));

                // Importar
                $deck = $this->importService->importFromApkg(
                    $destination,
                    $submoduleId,
                    pathinfo($filename, PATHINFO_FILENAME)
                );

                $this->info("      ✅ Deck '{$deck->name}' importado com {$deck->total_cards} cards");
                $importedCount++;
            } catch (\Exception $e) {
                $this->error("      ❌ Erro: {$e->getMessage()}");
                $errorCount++;
            }

            $this->newLine();
        }

        $this->info(str_repeat('=', 60));
        $this->info("✨ Importação Concluída!");
        $this->info(str_repeat('=', 60));
        $this->line("  ✅ Importados: {$importedCount}");
        $this->line("  ⏭️  Pulados: {$skippedCount}");
        $this->line("  ❌ Erros: {$errorCount}");

        if ($importedCount > 0) {
            $this->info("\n📚 Para listar os decks importados:");
            $this->line("   php artisan anki:list");
        }

        return 0;
    }
}
