<?php

namespace App\Console\Commands;

use App\Models\Module;
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
        $basePath = $this->option('base-path') ?? storage_path('app/public/videos');

        if (!is_dir($basePath)) {
            $this->error("Diretório não encontrado: {$basePath}");
            return 1;
        }

        $this->info('=== IMPORTANDO PDFs ===');
        $this->newLine();

        $moduleRoots = $this->getModuleRoots($basePath);
        if (empty($moduleRoots)) {
            $this->warn('Nenhuma pasta de modulo encontrada.');
            return 0;
        }

        $linked = 0;
        $notFound = 0;

        foreach ($moduleRoots as $moduleRoot) {
            $moduleName = basename($moduleRoot);
            $this->info("Modulo: {$moduleName}");

            $module = $this->resolveModule($moduleRoot);
            $subModules = $module ? $module->subModules : SubModule::all();

            foreach ($subModules as $subModule) {
                $folderNum = str_pad($subModule->title, 2, '0', STR_PAD_LEFT);
                $pdfDir = $moduleRoot . '/' . $folderNum . '/pdf';

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
                    $relativePath = str_replace(storage_path('app/public/'), '', str_replace('\\', '/', $pdfFile->getPathname()));
                    $baseName = trim($pdfFile->getBasename('.' . $pdfFile->getExtension()));

                    $lesson = Lesson::where('sub_module_id', $subModule->id)
                        ->where(function ($query) use ($baseName) {
                            $query->where('title', 'like', $baseName . '%')
                                ->orWhere('title', 'like', '%' . $baseName . '%');
                        })
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

            $this->newLine();
        }

        $this->info('=== RESULTADO ===');
        $this->line("✅ Associados: {$linked}");
        $this->warn("❌ Não encontrados: {$notFound}");

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

    private function resolveModule(string $moduleRoot): ?Module
    {
        $dirName = basename($moduleRoot);
        if (preg_match('/^(\d{1,3})/', $dirName, $matches)) {
            $order = (int) $matches[1];
            $module = Module::where('order', $order)->first();
            if ($module) {
                return $module;
            }
        }

        $normalized = $this->normalizeName($dirName);
        return Module::all()->first(function ($module) use ($normalized) {
            return $this->normalizeName($module->title) === $normalized;
        });
    }

    private function normalizeName(string $value): string
    {
        $value = strtolower($value);
        return preg_replace('/[^a-z0-9]+/', '', $value);
    }
}
