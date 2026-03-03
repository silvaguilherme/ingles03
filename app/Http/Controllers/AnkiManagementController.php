<?php

namespace App\Http\Controllers;

use App\Models\AnkiDeck;
use App\Models\AnkiCard;
use Illuminate\Http\Request;

class AnkiManagementController extends Controller
{
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
    public function editDeck(AnkiDeck $deck)
    {
        $cards = $deck->cards()->paginate(20);
        return view('anki.management.edit-deck', compact('deck', 'cards'));
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
            return view('anki.management.edit-card', compact('card'));
        }

        // POST - Atualizar card
        $request->validate([
            'front' => 'required|string',
            'back' => 'required|string',
            'audio_path' => 'nullable|string',
        ]);

        $card->update([
            'front' => $request->input('front'),
            'back' => $request->input('back'),
            'audio_path' => $request->input('audio_path'),
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
}
