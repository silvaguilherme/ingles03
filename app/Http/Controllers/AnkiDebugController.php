<?php

namespace App\Http\Controllers;

use App\Models\AnkiDeck;
use App\Models\SubModule;

class AnkiDebugController extends Controller
{
    /**
     * Mostrar status dos decks importados
     */
    public function status()
    {
        $decks = AnkiDeck::with('submodule')->get();
        $submodules = SubModule::all();

        return view('anki.debug-status', [
            'decks' => $decks,
            'submodules' => $submodules,
        ]);
    }

    /**
     * Tentar reasociar decks aos submodulos
     */
    public function reassociate()
    {
        $decks = AnkiDeck::all();
        $submodules = SubModule::all();
        
        $reassociated = 0;
        $failed = 0;
        $messages = [];

        foreach ($decks as $deck) {
            $deckName = strtolower($deck->name);
            $found = false;

            // Tentar encontrar um submodulo que corresponda ao nome do deck
            foreach ($submodules as $sub) {
                $subTitle = strtolower($sub->title);
                if (stripos($deckName, $subTitle) !== false || stripos($subTitle, $deckName) !== false) {
                    if ($deck->submodule_id !== $sub->id) {
                        $old = $deck->submodule_id;
                        $deck->update(['submodule_id' => $sub->id]);
                        $messages[] = "✓ Deck '{$deck->name}' reasociado: {$old} → {$sub->id} ({$sub->title})";
                        $reassociated++;
                    }
                    $found = true;
                    break;
                }
            }

            // Se não encontrou por nome, tentar por número
            if (!$found) {
                if (preg_match('/(\d{1,3})/', $deck->name, $matches)) {
                    $num = (int)$matches[1];
                    $subByOrder = $submodules->firstWhere('order', $num);
                    $subById = $submodules->firstWhere('id', $num);
                    
                    if ($subByOrder) {
                        $deck->update(['submodule_id' => $subByOrder->id]);
                        $messages[] = "✓ Deck '{$deck->name}' reasociado por order: {$subByOrder->id} ({$subByOrder->title})";
                        $reassociated++;
                        $found = true;
                    } elseif ($subById) {
                        $deck->update(['submodule_id' => $subById->id]);
                        $messages[] = "✓ Deck '{$deck->name}' reasociado por ID: {$subById->id} ({$subById->title})";
                        $reassociated++;
                        $found = true;
                    }
                }
            }

            if (!$found) {
                $messages[] = "✗ Deck '{$deck->name}' (ID: {$deck->id}) não pôde ser reasociado";
                $failed++;
            }
        }

        return response()->json([
            'reassociated' => $reassociated,
            'failed' => $failed,
            'messages' => $messages,
        ]);
    }
}
