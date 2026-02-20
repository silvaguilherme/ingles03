<?php

namespace App\Console\Commands;

use App\Models\AnkiDeck;
use App\Models\AnkiCardProgress;
use Illuminate\Console\Command;

class ListAnkiDecks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'anki:list {--user= : ID do usuário para filtrar}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Listar todos os decks Anki com estatísticas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $query = AnkiDeck::with('submodule.module', 'cards');

        $decks = $query->get();

        if ($decks->isEmpty()) {
            $this->warn('Nenhum deck encontrado!');
            return;
        }

        $userId = $this->option('user');

        $rows = [];

        foreach ($decks as $deck) {
            $totalCards = $deck->cards()->count();

            $learned = 0;
            $due = 0;

            if ($userId) {
                $learned = $deck->cards()
                    ->whereHas('progress', function ($q) use ($userId) {
                        $q->where('user_id', $userId)
                            ->where('repetitions', '>', 0);
                    })->count();

                $due = $deck->cards()
                    ->whereHas('progress', function ($q) use ($userId) {
                        $q->where('user_id', $userId)
                            ->where(function ($subQ) {
                                $subQ->whereIn('status', ['new', 'learning'])
                                    ->orWhere(function ($q) {
                                        $q->where('status', 'review')
                                            ->where('next_review', '<=', now());
                                    });
                            });
                    })->count();
            }

            $rows[] = [
                'ID' => $deck->id,
                'Nome' => $deck->name,
                'Submodulo' => $deck->submodule->title,
                'Módulo' => $deck->submodule->module->title,
                'Cards' => $totalCards,
                'Aprendidos' => $userId ? $learned : '-',
                'Prontos' => $userId ? $due : '-',
                'Criado' => $deck->created_at->format('d/m/Y H:i'),
            ];
        }

        $this->table(
            array_keys($rows[0]),
            $rows
        );

        // Resumo
        $this->newLine();
        $this->info('Resumo:');
        $this->line('  Total de Decks: ' . count($rows));
        $this->line('  Total de Cards: ' . AnkiDeck::sum('total_cards'));

        if ($userId) {
            $totalReviews = AnkiCardProgress::where('user_id', $userId)->count();
            $this->line("  Reviews do Usuário {$userId}: {$totalReviews}");
        }
    }
}
