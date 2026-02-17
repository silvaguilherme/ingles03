<?php

namespace App\Http\Controllers;

use App\Models\SubModule;
use App\Models\AnkiDeck;
use App\Services\AnkiImportService;
use Illuminate\Http\Request;

class AnkiDeckController extends Controller
{
    protected $importService;

    public function __construct(AnkiImportService $importService)
    {
        $this->importService = $importService;
    }

    /**
     * Mostrar formulário para upload de APKG
     */
    public function create(SubModule $subModule)
    {
        return view('anki.upload', compact('subModule'));
    }

    /**
     * Armazenar novo deck
     */
    public function store(Request $request, SubModule $subModule)
    {
        $request->validate([
            'file' => 'required|file|mimes:apkg,zip',
            'deck_name' => 'nullable|string|max:255',
        ]);

        try {
            $file = $request->file('file');
            $filePath = $file->store('anki-decks', 'local');

            $deckName = $request->input('deck_name') ?? $file->getClientOriginalName();

            // Importar o arquivo
            $deck = $this->importService->importFromApkg(
                $filePath,
                $subModule->id,
                $deckName
            );

            return redirect()->route('submodules.show', $subModule)
                ->with('success', "Deck '{$deck->name}' importado com sucesso! {$deck->total_cards} cards adicionados.");
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao importar deck: ' . $e->getMessage());
        }
    }

    /**
     * Deletar um deck
     */
    public function destroy(AnkiDeck $deck)
    {
        $subModule = $deck->submodule;

        // Deletar arquivo se existir
        if ($deck->file_path) {
            \Illuminate\Support\Facades\Storage::disk('local')->delete($deck->file_path);
        }

        $deck->delete();

        return redirect()->route('submodules.show', $subModule)
            ->with('success', 'Deck deletado com sucesso');
    }
}
