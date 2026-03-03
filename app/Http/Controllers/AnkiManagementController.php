<?php

namespace App\Http\Controllers;

use App\Models\AnkiDeck;
use App\Models\AnkiCard;
use Illuminate\Http\Request;

class AnkiManagementController extends Controller
{
    private const ANKI_ERROR_PHRASES = [
        'This file requires',
        'newer version',
        'upgrade Anki',
        'Please update',
        'latest anki',
        'import the .apkg file again',
        'not supported',
    ];

    /**
     * Listar todos os decks com options para gerenciar
     */
    public function decks()
    {
        $decks = AnkiDeck::with('subModule')
            ->withCount('cards')
            ->paginate(10);

        return view('anki.management.decks', compact('decks'));
    }

    /**
     * Editar um deck
     */
    public function editDeck(Request $request, AnkiDeck $deck)
    {
        $cardsQuery = $deck->cards();
        $withoutAudioOnly = $request->boolean('without_audio');

        if ($withoutAudioOnly) {
            $cardsQuery->whereRaw("LOWER(COALESCE(front, '')) NOT LIKE ?", ['%<audio%'])
                ->whereRaw("LOWER(COALESCE(back, '')) NOT LIKE ?", ['%<audio%'])
                ->whereRaw("LOWER(COALESCE(extra, '')) NOT LIKE ?", ['%<audio%']);
        }

        $cards = $cardsQuery->paginate(20)->withQueryString();

        return view('anki.management.edit-deck', compact('deck', 'cards', 'withoutAudioOnly'));
    }

    /**
     * Atualizar informações do deck
     */
    public function updateDeck(Request $request, AnkiDeck $deck)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $deck->update([
            'name' => $request->input('name'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Deck atualizado com sucesso!',
        ]);
    }

    /**
     * Deletar um deck
     */
    public function deleteDeck(Request $request, AnkiDeck $deck)
    {
        $deckName = $deck->name;
        $cardCount = $deck->cards()->count();

        $deck->delete();

        return response()->json([
            'success' => true,
            'message' => "Deck '$deckName' deletado! ($cardCount cards removidos)",
        ]);
    }

    /**
     * Editar um card
     */
    public function editCard(Request $request, AnkiCard $card)
    {
        if ($request->method() === 'GET') {
            $audioUrl = $this->extractFirstAudioUrl($card);
            return view('anki.management.edit-card', compact('card', 'audioUrl'));
        }

        // POST - Atualizar card
        $request->validate([
            'front' => 'required|string',
            'back' => 'required|string',
            'audio_url' => 'nullable|string|max:2048',
        ]);

        $audioUrl = trim((string) $request->input('audio_url', ''));
        $audioUrl = $audioUrl !== '' ? $audioUrl : null;
        $extra = $this->upsertManagedAudioBlock($card->extra, $audioUrl);

        $card->update([
            'front' => $request->input('front'),
            'back' => $request->input('back'),
            'extra' => $extra,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Card atualizado com sucesso!',
        ]);
    }

    /**
     * Deletar um card
     */
    public function deleteCard(AnkiCard $card)
    {
        $deckName = $card->deck->name;
        $cardId = $card->id;

        $card->delete();

        return response()->json([
            'success' => true,
            'message' => "Card removido do deck '$deckName'",
        ]);
    }

    /**
     * Remover cards com mensagens de erro do Anki em um deck
     */
    public function cleanErrorCards(AnkiDeck $deck)
    {
        $cards = $deck->cards()->get(['id', 'front', 'back']);
        $idsToDelete = [];

        foreach ($cards as $card) {
            $frontText = trim(strip_tags((string) $card->front));
            $backText = trim(strip_tags((string) $card->back));

            foreach (self::ANKI_ERROR_PHRASES as $phrase) {
                if (stripos($frontText, $phrase) !== false || stripos($backText, $phrase) !== false) {
                    $idsToDelete[] = $card->id;
                    break;
                }
            }
        }

        $deleted = 0;
        if (!empty($idsToDelete)) {
            $deleted = AnkiCard::whereIn('id', $idsToDelete)->delete();
        }

        $deck->update([
            'total_cards' => $deck->cards()->count(),
        ]);

        return response()->json([
            'success' => true,
            'message' => $deleted > 0
                ? "Limpeza concluída: {$deleted} card(s) de erro removido(s)."
                : 'Nenhum card de erro encontrado neste baralho.',
            'deleted' => $deleted,
        ]);
    }

    /**
     * Deletar decks duplicados
     */
    public function deduplicateDecks()
    {
        // Encontrar decks duplicados por file_path e submodule_id
        $duplicates = AnkiDeck::selectRaw('file_path, submodule_id, COUNT(*) as count')
            ->groupBy('file_path', 'submodule_id')
            ->having('count', '>', 1)
            ->get();

        $deletedCount = 0;
        $deletedCards = 0;

        foreach ($duplicates as $duplicate) {
            $decks = AnkiDeck::where('file_path', $duplicate->file_path)
                ->where('submodule_id', $duplicate->submodule_id)
                ->orderBy('created_at', 'desc')
                ->skip(1) // Manter o mais recente
                ->get();

            foreach ($decks as $deck) {
                $deletedCards += $deck->cards()->count();
                $deck->delete();
                $deletedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Deduplicação concluída! $deletedCount decks removidos ($deletedCards cards deletados)",
            'stats' => [
                'duplicates_found' => $duplicates->count(),
                'decks_deleted' => $deletedCount,
                'cards_deleted' => $deletedCards,
            ],
        ]);
    }

    /**
     * Buscar cards por termo
     */
    public function searchCards(Request $request)
    {
        $search = $request->input('q', '');
        
        if (strlen($search) < 2) {
            return response()->json(['results' => []]);
        }

        $cards = AnkiCard::with('deck')
            ->where('front', 'like', "%{$search}%")
            ->orWhere('back', 'like', "%{$search}%")
            ->limit(10)
            ->get();

        return response()->json([
            'results' => $cards->map(fn($card) => [
                'id' => $card->id,
                'front' => substr($card->front, 0, 50),
                'back' => substr($card->back, 0, 50),
                'deck' => $card->deck->name,
            ]),
        ]);
    }

    private function extractFirstAudioUrl(AnkiCard $card): ?string
    {
        $content = implode("\n", [
            (string) $card->front,
            (string) $card->back,
            (string) $card->extra,
        ]);

        if (preg_match('/<source[^>]*src=["\']([^"\']+)["\']/i', $content, $matches)) {
            return $matches[1] ?? null;
        }

        if (preg_match('/<audio[^>]*src=["\']([^"\']+)["\']/i', $content, $matches)) {
            return $matches[1] ?? null;
        }

        return null;
    }

    private function upsertManagedAudioBlock(?string $extra, ?string $audioUrl): ?string
    {
        $normalizedExtra = (string) ($extra ?? '');
        $normalizedExtra = preg_replace(
            '/<div class="manual-audio" data-manual-audio="1">.*?<\/div>/is',
            '',
            $normalizedExtra
        ) ?? '';
        $normalizedExtra = trim($normalizedExtra);

        if ($audioUrl === null) {
            return $normalizedExtra !== '' ? $normalizedExtra : null;
        }

        $safeUrl = htmlspecialchars($audioUrl, ENT_QUOTES, 'UTF-8');
        $audioBlock = '<div class="manual-audio" data-manual-audio="1"><audio controls preload="none"><source src="' . $safeUrl . '"></audio></div>';

        if ($normalizedExtra === '') {
            return $audioBlock;
        }

        return $normalizedExtra . '<hr class="my-3" />' . $audioBlock;
    }
}
