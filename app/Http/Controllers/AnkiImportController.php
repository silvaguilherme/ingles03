<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\Services\PdfAnkiImportService;

class AnkiImportController extends Controller
{
    protected $pdfService;

    public function __construct(PdfAnkiImportService $pdfService)
    {
        $this->pdfService = $pdfService;
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
     * Importar PDF e criar cards Anki
     */
    public function importPdf(Request $request)
    {
        $request->validate([
            'path' => 'required|string|ends_with:.pdf',
            'submodule_id' => 'required|integer|exists:sub_modules,id',
            'deck_name' => 'nullable|string|max:255',
            'audio_path' => 'nullable|string',
        ]);

        try {
            $result = $this->pdfService->importFromPdf(
                $request->input('path'),
                $request->input('submodule_id'),
                $request->input('deck_name'),
                $request->input('audio_path')
            );

            return response()->json([
                'success' => true,
                'message' => "Importação concluída! {$result['cards_created']} cards criados.",
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao importar PDF: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Preview de um PDF antes de importar
     */
    public function previewPdf(Request $request)
    {
        $request->validate([
            'path' => 'required|string|ends_with:.pdf',
        ]);

        try {
            $info = $this->pdfService->getPdfInfo($request->input('path'));

            if (!$info) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não foi possível processar o PDF',
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => $info,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar PDF: ' . $e->getMessage(),
            ], 400);
        }
    }
}
