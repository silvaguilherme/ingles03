<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixAnkiCardsSchema extends Command
{
    protected $signature = 'anki:fix-schema';
    protected $description = 'Aumenta o tamanho da coluna front para LONGTEXT';

    public function handle()
    {
        $this->info('Alterando schema da tabela anki_cards...');

        try {
            // Executar ALTER TABLE diretamente
            DB::statement('ALTER TABLE anki_cards MODIFY COLUMN front LONGTEXT NULL');
            
            $this->info('✅ Coluna front alterada para LONGTEXT com sucesso!');
            
            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Erro ao alterar coluna:');
            $this->error($e->getMessage());
            
            return 1;
        }
    }
}
