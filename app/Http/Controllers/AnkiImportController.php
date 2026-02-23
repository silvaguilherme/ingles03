<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class AnkiImportController extends Controller
{
    /**
     * Mostrar página de importação
     */
    public function index()
    {
        return view('anki.import');
    }

    /**
     * Executar importação via web
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
}
