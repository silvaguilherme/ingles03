<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class ImportAllController extends Controller
{
    /**
     * Mostrar página de importação centralizada
     */
    public function index()
    {
        return view('admin.import-all');
    }

    /**
     * Executar todos os imports
     */
    public function importAll(Request $request)
    {
        $results = [];

        try {
            // 1. Importar vídeos/aulas
            $results['videos'] = $this->importVideos();

            // 2. Importar PDFs
            $results['pdfs'] = $this->importPdfs();

            // 3. Importar Anki Decks
            $results['anki'] = $this->importAnkiDecks();

            // 4. Importar Áudios
            $results['audios'] = $this->importAudios();

            return response()->json([
                'success' => true,
                'message' => 'Todos os imports foram executados com sucesso!',
                'data' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao executar imports: ' . $e->getMessage(),
                'data' => $results,
            ], 400);
        }
    }

    /**
     * Importar vídeos/aulas
     */
    private function importVideos()
    {
        try {
            $output = shell_exec('cd ' . base_path() . ' && php import_videos.php 2>&1');

            return [
                'status' => 'success',
                'message' => 'Vídeos importados com sucesso',
                'output' => $output ? trim($output) : 'Concluído',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro ao importar vídeos: ' . $e->getMessage(),
                'output' => $e->getMessage(),
            ];
        }
    }

    /**
     * Importar PDFs
     */
    private function importPdfs()
    {
        try {
            $exitCode = Artisan::call('import:pdfs');
            $output = Artisan::output();

            return [
                'status' => $exitCode === 0 ? 'success' : 'warning',
                'message' => $exitCode === 0 ? 'PDFs importados com sucesso' : 'PDFs processados com avisos',
                'output' => $output ?: 'Concluído',
                'exit_code' => $exitCode,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro ao importar PDFs: ' . $e->getMessage(),
                'output' => $e->getMessage(),
            ];
        }
    }

    /**
     * Executar apenas import de Anki com deduplicação
     */
    public function importAnkiOnly(Request $request)
    {
        $results = [];

        try {
            // 1. Importar Anki Decks
            $results['anki'] = $this->importAnkiDecks();

            return response()->json([
                'success' => true,
                'message' => 'Import Anki concluído!',
                'data' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao importar Anki: ' . $e->getMessage(),
                'data' => $results,
            ], 400);
        }
    }

    /**
     * Importar Anki Decks com deduplicação
     */
    private function importAnkiDecks()
    {
        try {
            $exitCode = Artisan::call('anki:import', [
                '--path' => '/var/www/ingles03/storage/app/public/videos',
            ]);
            $output = Artisan::output();

            return [
                'status' => $exitCode === 0 ? 'success' : 'warning',
                'message' => $exitCode === 0 ? 'Decks Anki importados com sucesso' : 'Decks Anki processados com avisos',
                'output' => $output ?: 'Concluído',
                'exit_code' => $exitCode,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro ao importar Anki Decks: ' . $e->getMessage(),
                'output' => $e->getMessage(),
            ];
        }
    }

    /**
     * Importar Áudios
     */
    private function importAudios()
    {
        try {
            $exitCode = Artisan::call('import:audios');
            $output = Artisan::output();

            return [
                'status' => $exitCode === 0 ? 'success' : 'warning',
                'message' => $exitCode === 0 ? 'Áudios importados com sucesso' : 'Áudios processados com avisos',
                'output' => $output ?: 'Concluído',
                'exit_code' => $exitCode,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro ao importar Áudios: ' . $e->getMessage(),
                'output' => $e->getMessage(),
            ];
        }
    }
}
