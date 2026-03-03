<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixAnkiMediaPublicPath extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'anki:fix-media-path {--copy : Copia sem remover arquivos antigos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Move/copy mídia dos decks Anki para storage/app/public/anki-media (acessível por /storage)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $legacyRoot = storage_path('app/anki-media');
        $publicRoot = storage_path('app/public/anki-media');
        $copyOnly = (bool) $this->option('copy');

        if (!File::exists($legacyRoot)) {
            $this->warn('Nenhuma pasta legada encontrada em storage/app/anki-media.');
            return self::SUCCESS;
        }

        File::ensureDirectoryExists($publicRoot);

        $deckDirs = File::directories($legacyRoot);
        if (empty($deckDirs)) {
            $this->warn('Nenhuma mídia legada para migrar.');
            return self::SUCCESS;
        }

        $moved = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($deckDirs as $deckDir) {
            $deckId = basename($deckDir);
            $targetDeckDir = $publicRoot . DIRECTORY_SEPARATOR . $deckId;
            File::ensureDirectoryExists($targetDeckDir);

            foreach (File::files($deckDir) as $file) {
                $source = $file->getPathname();
                $target = $targetDeckDir . DIRECTORY_SEPARATOR . $file->getFilename();

                try {
                    if (File::exists($target)) {
                        $skipped++;
                        continue;
                    }

                    if ($copyOnly) {
                        File::copy($source, $target);
                    } else {
                        File::move($source, $target);
                    }

                    $moved++;
                } catch (\Throwable $e) {
                    $errors++;
                    $this->error("Erro ao processar {$source}: {$e->getMessage()}");
                }
            }
        }

        $this->info('Migração de mídia Anki concluída.');
        $this->line("Arquivos processados: {$moved}");
        $this->line("Ignorados (já existiam): {$skipped}");
        $this->line("Erros: {$errors}");
        $this->line('Destino: storage/app/public/anki-media');

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }
}
