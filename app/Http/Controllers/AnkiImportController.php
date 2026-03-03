<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\Services\PdfAnkiImportService;
use App\Services\CsvAnkiImportService;

class AnkiImportController extends Controller
{
    protected $csvService;

    public function __construct(CsvAnkiImportService $csvService)
    {
        $this->csvService = $csvService;
    }

    /**
     * Mostrar página de importação
     */
    public function index()
    {
        return view('anki.import');
    }

    /**
     * Executar importação via web (APKG)
     */
    public function import(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        try {
            // Executar o comando com o path fornecido
            $exitCode = Artisan::call('anki:import', [
                '--path' => $request->input('path'),
            ]);

            $output = Artisan::output();

            return response()->json([
                'success' => $exitCode === 0,
                'output' => $output,
                'message' => $exitCode === 0 ? 'Importação concluída com sucesso!' : 'Erro durante a importação',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao executar importação: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Importar CSV e criar cards Anki
     */
    public function importCsv(Request $request)
    {
        $request->validate([
            'path' => ['required', 'string', 'regex:/\.csv$/i'],
            'submodule_id' => 'required|integer|exists:sub_modules,id',
            'deck_name' => 'nullable|string|max:255',
        ]);

        try {
            $result = $this->csvService->importFromCsv(
                $request->input('path'),
                $request->input('submodule_id'),
                $request->input('deck_name')
            );

            return response()->json([
                'success' => true,
                'message' => "Importação concluída! {$result['cards_created']} cards criados.",
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao importar CSV: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Preview de um CSV antes de importar
     */
    public function previewCsv(Request $request)
    {
        $request->validate([
            'path' => ['required', 'string', 'regex:/\.csv$/i'],
        ]);

        try {
            $info = $this->csvService->getCsvInfo($request->input('path'));

            return response()->json([
                'success' => true,
                'data' => $info,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar CSV: ' . $e->getMessage(),
            ], 400);
        }
    }
}

