<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ReorganizeAudios extends Command
{
    protected $signature = 'organize:audios {--base-path= : Caminho base dos submodulos} {--dry-run : Simular sem mover}';
    protected $description = 'Reorganizar audios existentes para as pastas /audio de cada submodulo';

    public function handle()
    {
        $basePath = $this->option('base-path') ?? storage_path('app/public/videos');
        $dryRun = $this->option('dry-run');

        if (!is_dir($basePath)) {
            $this->error("Diretório não encontrado: {$basePath}");
            return 1;
        }

        $this->info('=== REORGANIZANDO AUDIOS ===');
        if ($dryRun) {
            $this->warn('(DRY RUN - nenhum arquivo sera movido)');
        }
        $this->newLine();

        $moduleRoots = $this->getModuleRoots($basePath);
        if (empty($moduleRoots)) {
            $this->warn('Nenhuma pasta de modulo encontrada.');
            return 0;
        }

        $audioExtensions = ['mp3', 'wav', 'm4a', 'ogg'];
        $allFiles = File::allFiles($basePath);
        $audioFiles = array_filter($allFiles, function ($file) use ($audioExtensions) {
            return in_array(strtolower($file->getExtension()), $audioExtensions, true);
        });

        $this->line('Audios encontrados: ' . count($audioFiles));
        $this->newLine();

        $moved = 0;
        $failed = 0;

        foreach ($audioFiles as $audioFile) {
            $audioPath = str_replace('\\', '/', $audioFile->getPathname());
            $filename = $audioFile->getFilename();

            if (preg_match('~\/(\d{1,3})\/[^\/]*\.(mp3|wav|m4a|ogg)$~i', $audioPath, $matches)) {
                $folderNum = $matches[1];
                $moduleRoot = $this->findModuleRoot($audioPath, $moduleRoots);
                if (!$moduleRoot) {
                    $this->warn("❌ {$filename} - Modulo nao identificado");
                    $failed++;
                    continue;
                }

                $destDir = $moduleRoot . '/' . str_pad($folderNum, 2, '0', STR_PAD_LEFT) . '/audio';
                $destPath = $destDir . '/' . $filename;

                if (dirname($audioPath) === $destDir) {
                    $this->line("ℹ️  {$filename} - Ja esta no destino ({$folderNum}/audio/)");
                    continue;
                }

                if (file_exists($destPath)) {
                    $this->warn("⚠️  {$filename} - Arquivo ja existe no destino, pulando");
                    continue;
                }

                if ($dryRun) {
                    $this->line("→ {$filename}");
                    $this->line("  De: {$audioPath}");
                    $this->line("  Para: {$destPath}");
                } else {
                    if (!is_dir($destDir)) {
                        mkdir($destDir, 0755, true);
                    }

                    if (rename($audioPath, $destPath)) {
                        $this->line("✅ {$filename} → {$folderNum}/audio/");
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
            $this->line('(DRY RUN - Nenhum arquivo foi movido)');
        } else {
            $this->line("✅ Movidos: {$moved}");
            $this->warn("❌ Falhados: {$failed}");
        }

        return 0;
    }

    private function getModuleRoots(string $basePath): array
    {
        $roots = [];
        $candidates = File::directories($basePath);

        foreach ($candidates as $dir) {
            if ($this->looksLikeModuleRoot($dir)) {
                $roots[] = $dir;
                continue;
            }

            foreach (File::directories($dir) as $child) {
                if ($this->looksLikeModuleRoot($child)) {
                    $roots[] = $child;
                }
            }
        }

        return array_values(array_unique($roots));
    }

    private function looksLikeModuleRoot(string $path): bool
    {
        foreach (File::directories($path) as $dir) {
            $name = basename($dir);
            if (preg_match('/^\d{1,3}$/', $name)) {
                return true;
            }
        }

        return false;
    }

    private function findModuleRoot(string $filePath, array $moduleRoots): ?string
    {
        $filePath = str_replace('\\', '/', $filePath);
        $matched = null;

        foreach ($moduleRoots as $root) {
            $root = str_replace('\\', '/', $root);
            if (str_starts_with($filePath, $root . '/')) {
                if (!$matched || strlen($root) > strlen($matched)) {
                    $matched = $root;
                }
            }
        }

        return $matched;
    }
}
