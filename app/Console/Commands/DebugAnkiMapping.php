<?php

namespace App\Console\Commands;

use App\Models\SubModule;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class DebugAnkiMapping extends Command
{
    protected $signature = 'anki:debug-mapping {--path= : Caminho base}';
    protected $description = 'Debug da associação de APKGs com submodulos';

    public function handle()
    {
        $basePath = $this->option('path') ?? storage_path('app/public/videos');
        
        if (!is_dir($basePath)) {
            $this->error("Diretório não encontrado: {$basePath}");
            return 1;
        }

        $this->info('=== SUBMODULOS NO BANCO ===');
        $submodules = SubModule::all(['id', 'title', 'order']);
        $this->table(['ID', 'Title', 'Order'], $submodules->toArray());

        $this->info('');
        $this->info('=== ARQUIVOS APKG ENCONTRADOS ===');
        
        // Encontrar APKGs
        $apkgFiles = [];
        foreach (File::allFiles($basePath) as $file) {
            $path = str_replace('\\', '/', $file->getPathname());
            if (stripos($path, '/anki/') !== false && str_ends_with(strtolower($path), '.apkg')) {
                $apkgFiles[] = $path;
            }
        }

        sort($apkgFiles);

        $this->table(
            ['Arquivo', 'Pasta Before /anki/', 'Número Pasta'],
            array_map(function ($path) {
                preg_match('~(/(\d{1,3})/anki/)~', $path . '/', $matches);
                $pastaNum = $matches[2] ?? '?';
                
                $filename = basename($path);
                return [
                    $filename,
                    ($matches[1] ?? 'N/A'),
                    $pastaNum
                ];
            }, array_slice($apkgFiles, 0, 15))
        );

        $this->info('');
        $this->info('=== MAPEAMENTO ESPERADO ===');
        
        foreach ($apkgFiles as $filePath) {
            if (preg_match('~\/(\d{1,3})\/anki\/$~', $filePath . '/', $matches)) {
                $folderNum = $matches[1];
                $filename = basename($filePath);
                
                // Tentar encontrar o submodulo
                $sub = $submodules->firstWhere('title', str_pad($folderNum, 2, '0', STR_PAD_LEFT))
                    ?? $submodules->firstWhere('title', (string)(int)$folderNum);
                
                if ($sub) {
                    $this->line("Pasta {$folderNum} ({$filename}) → Submodulo ID {$sub['id']} (title: {$sub['title']})");
                } else {
                    $this->warn("Pasta {$folderNum} ({$filename}) → NÃO ENCONTRADO!");
                }
            }
        }

        return 0;
    }
}
