<?php

namespace App\Http\Controllers;

use App\Models\AnkiDeck;
use App\Models\AnkiCard;
use App\Models\AnkiCardProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnkiController extends Controller
{
    /**
     * Dashboard geral de Anki - mostra todos os cards de todos os submodulos
     */
    public function index()
    {
        $user = Auth::user();

        // Obter todos os decks
        $decks = AnkiDeck::with('submodule.module')
            ->get();

        // Calcular estatísticas
        $totalCards = AnkiCard::count();
        $cardsDueReview = AnkiCardProgress::where('user_id', $user->id)
            ->whereIn('status', ['new', 'learning'])
            ->orWhere(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->where('status', 'review')
                    ->whereNotNull('next_review')
                    ->where('next_review', '<=', now());
            })
            ->count();

        $cardsStudied = AnkiCardProgress::where('user_id', $user->id)
            ->where('repetitions', '>', 0)
            ->count();

        // Obter decks com progresso
        $decksWithProgress = $decks->map(function ($deck) use ($user) {
            $deckCards = $deck->cards()->count();
            $learnedCards = $deck->cards()
                ->whereHas('progress', function ($query) use ($user) {
                    $query->where('user_id', $user->id)
                        ->where('repetitions', '>', 0);
                })->count();

            $dueCards = $deck->cards()
                ->whereHas('progress', function ($query) use ($user) {
                    $query->where('user_id', $user->id)
                        ->where(function ($subQuery) {
                            $subQuery->whereIn('status', ['new', 'learning'])
                                ->orWhere(function ($q) {
                                    $q->where('status', 'review')
                                        ->where('next_review', '<=', now());
                                });
                        });
                })->count();

            return [
                'deck' => $deck,
                'total_cards' => $deckCards,
                'learned_cards' => $learnedCards,
                'due_cards' => $dueCards,
                'progress_percentage' => $deckCards > 0 ? round(($learnedCards / $deckCards) * 100) : 0,
            ];
        });

        return view('anki.index', compact(
            'decksWithProgress',
            'totalCards',
            'cardsDueReview',
            'cardsStudied'
        ));
    }

    /**
     * Página de estudos de um deck específico
     */
    public function study(AnkiDeck $deck)
    {
        $user = Auth::user();

        // Obter cards que estão prontos para revisão
        $cards = $deck->cards()
            ->with(['progress' => function ($query) use ($user) {
                $query->where('user_id', $user->id);
            }])
            ->get()
            ->filter(function ($card) use ($user) {
                $progress = $card->progress->first();
                if (!$progress) {
                    return true; // Card novo
                }
                return $progress->isReadyForReview();
            })
            ->values();

        if ($cards->isEmpty()) {
            return view('anki.no-cards', compact('deck'));
        }

        // Começar com o primeiro card
        $currentCard = $cards->first();

        return view('anki.study', compact('deck', 'cards', 'currentCard'));
    }

    /**
     * Registrar resposta do usuário
     */
    public function recordAnswer(Request $request, AnkiDeck $deck)
    {
        $request->validate([
            'card_id' => 'required|exists:anki_cards,id',
            'quality' => 'required|integer|min:0|max:3', // 0 = fail, 1 = hard, 2 = ok, 3 = easy
        ]);

        $user = Auth::user();
        $card = AnkiCard::findOrFail($request->card_id);

        // Obter ou criar progresso
        $progress = AnkiCardProgress::firstOrCreate(
            [
                'user_id' => $user->id,
                'anki_card_id' => $card->id,
            ],
            [
                'status' => 'new',
                'ease_factor' => 2.5,
                'interval' => 1,
                'repetitions' => 0,
                'lapses' => 0,
            ]
        );

        // Registrar review com o algoritmo SM-2
        $progress->recordReview($request->quality);

        return response()->json([
            'success' => true,
            'message' => 'Resposta registrada com sucesso',
        ]);
    }

    /**
     * Obter estatísticas de progresso
     */
    public function stats()
    {
        $user = Auth::user();

        $totalCards = AnkiCard::count();
        $cardsDueReview = AnkiCardProgress::where('user_id', $user->id)
            ->where(function ($query) {
                $query->whereIn('status', ['new', 'learning'])
                    ->orWhere(function ($q) {
                        $q->where('status', 'review')
                            ->where('next_review', '<=', now());
                    });
            })
            ->count();

        $cardsLearned = AnkiCardProgress::where('user_id', $user->id)
            ->where('status', 'review')
            ->count();

        $recentActivity = AnkiCardProgress::where('user_id', $user->id)
            ->where('last_reviewed', '>=', now()->subDays(30))
            ->count();

        return view('anki.stats', compact(
            'totalCards',
            'cardsDueReview',
            'cardsLearned',
            'recentActivity'
        ));
    }
}
