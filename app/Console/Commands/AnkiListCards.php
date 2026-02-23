<?php

namespace App\Console\Commands;

use App\Models\AnkiDeck;
use App\Models\AnkiCard;
use Illuminate\Console\Command;

class AnkiListCards extends Command
{
    protected $signature = 'anki:list-cards {--deck-id= : ID do deck específico}';
    protected $description = 'Lista os cards importados no banco de dados';

    public function handle()
    {
        $deckId = $this->option('deck-id');

        if ($deckId) {
            $deck = AnkiDeck::find($deckId);
            if (!$deck) {
                $this->error("Deck ID {$deckId} não encontrado");
                return 1;
            }
            $this->showDeckCards($deck);
        } else {
            $decks = AnkiDeck::with('cards')->get();
            
            if ($decks->isEmpty()) {
                $this->warn('Nenhum deck encontrado no banco de dados');
                return 0;
            }

            foreach ($decks as $deck) {
                $this->showDeckCards($deck);
                $this->line('');
                $this->line(str_repeat('-', 60));
                $this->line('');
            }
        }

        return 0;
    }

    private function showDeckCards(AnkiDeck $deck)
    {
        $this->info("Deck: {$deck->name} (ID: {$deck->id})");
        $this->line("Total de cards: {$deck->cards->count()}");
        $this->line("Submodule: " . ($deck->submodule ? $deck->submodule->title : 'N/A'));
        $this->line('');

        if ($deck->cards->isEmpty()) {
            $this->warn('  Nenhum card neste deck');
            return;
        }

        $this->table(
            ['ID', 'Front (preview)', 'Back (preview)', 'Tags'],
            $deck->cards->take(10)->map(function ($card) {
                return [
                    $card->id,
                    mb_substr(strip_tags($card->front), 0, 50) . (mb_strlen(strip_tags($card->front)) > 50 ? '...' : ''),
                    mb_substr(strip_tags($card->back), 0, 50) . (mb_strlen(strip_tags($card->back)) > 50 ? '...' : ''),
                    mb_substr($card->tags, 0, 30),
                ];
            })->toArray()
        );

        if ($deck->cards->count() > 10) {
            $this->line("  ... e mais " . ($deck->cards->count() - 10) . " cards");
        }
    }
}
