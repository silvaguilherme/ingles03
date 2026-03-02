<?php

namespace App\Console\Commands;

use App\Services\PdfAnkiImportService;
use Illuminate\Console\Command;

class ImportAnkiFromPdf extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'anki:import-pdf {--path= : Caminho do arquivo PDF} {--submodule-id= : ID do submodulo} {--deck-name= : Nome do deck (opcional)}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Importar cards Anki de um arquivo PDF com padrão Front:/Back:';

    protected $importService;

    /**
     * Create a new command instance.
     */
    public function __construct(PdfAnkiImportService $importService)
    {
        parent::__construct();
        $this->importService = $importService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Iniciando importação de Anki a partir de PDF...\n');

        $path = $this->option('path');
        $submoduleId = $this->option('submodule-id');
        $deckName = $this->option('deck-name');

        // Se não forneceu path, pedir
        if (!$path) {
            $path = $this->ask('Qual o caminho do arquivo PDF? (ex: anki-materials/grammar.pdf)');
        }

        if (!$submoduleId) {
            $submoduleId = $this->ask('Qual o ID do submodulo?');
        }

        try {
            $this->line("📄 Processando: {$path}");
            $this->newLine();

            $result = $this->importService->importFromPdf($path, $submoduleId, $deckName);

            $this->info("✅ Importação concluída com sucesso!");
            $this->newLine();
            $this->table(
                ['Propriedade', 'Valor'],
                [
                    ['Deck ID', $result['deck_id']],
                    ['Deck Nome', $result['deck_name']],
                    ['Cards Criados', $result['cards_created']],
                    ['Total Encontrado', $result['total_cards']],
                ]
            );

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Erro: ' . $e->getMessage());
            return 1;
        }
    }
}
