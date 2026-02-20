<?php

namespace App\Services;

use App\Models\AnkiDeck;
use App\Models\AnkiCard;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use PDO;

class AnkiImportService
{
    /**
     * Importar cards de um arquivo APKG
     */
    public function importFromApkg($filePath, $submoduleId, $deckName = null)
    {
        $fileFullPath = storage_path('app/' . $filePath);

        if (!file_exists($fileFullPath)) {
            throw new \Exception('Arquivo APKG não encontrado: ' . $fileFullPath);
        }

        // Criar ou atualizar o deck
        $deck = AnkiDeck::updateOrCreate(
            ['submodule_id' => $submoduleId],
            [
                'name' => $deckName ?? basename($filePath, '.apkg'),
                'file_path' => $filePath,
            ]
        );

        // Extrair o banco de dados do arquivo APKG
        $tempDir = storage_path('app/temp_anki_' . uniqid());
        mkdir($tempDir, 0755, true);

        $zip = new ZipArchive();
        if ($zip->open($fileFullPath) === true) {
            $zip->extractTo($tempDir);
            $zip->close();
        } else {
            rmdir($tempDir);
            throw new \Exception('Erro ao extrair arquivo APKG');
        }

        // Conectar ao banco de dados SQLite
        $dbPath = $tempDir . '/collection.anki2';
        if (!file_exists($dbPath)) {
            $this->deleteDirectory($tempDir);
            throw new \Exception('Banco de dados collection.anki2 não encontrado no APKG');
        }

        // Extrair arquivos de mídia
        $mediaDir = storage_path('app/anki-media/' . $deck->id);
        if (!is_dir($mediaDir)) {
            mkdir($mediaDir, 0755, true);
        }

        $mediaFiles = [];
        $mediaJsonPath = $tempDir . '/media';
        if (file_exists($mediaJsonPath)) {
            $mediaJson = json_decode(file_get_contents($mediaJsonPath), true) ?? [];
            $mediaFiles = $mediaJson ?? [];
        }

        try {
            $pdo = new PDO('sqlite:' . $dbPath);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Consultar os notes
            $stmt = $pdo->prepare('SELECT id, flds, tags FROM notes ORDER BY id');
            $stmt->execute();
            $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Limpar cards antigos do deck
            $deck->cards()->delete();

            $order = 0;
            foreach ($notes as $note) {
                // Dividir os campos (separados por ASCII 31)
                $fields = explode("\x1f", $note['flds']);

                if (count($fields) >= 2) {
                    $front = $fields[0] ?? '';
                    $back = $fields[1] ?? '';
                    $extra = isset($fields[2]) ? $fields[2] : null;

                    // Processar conteúdo HTML/mídia
                    $front = $this->processCardContent($front, $mediaFiles, $deck->id);
                    $back = $this->processCardContent($back, $mediaFiles, $deck->id);
                    if ($extra) {
                        $extra = $this->processCardContent($extra, $mediaFiles, $deck->id);
                    }

                    AnkiCard::create([
                        'anki_deck_id' => $deck->id,
                        'front' => $front,
                        'back' => $back,
                        'extra' => $extra,
                        'tags' => $note['tags'] ?? '',
                        'order' => $order++,
                    ]);
                }
            }

            $deck->update(['total_cards' => $order]);

            $pdo = null;
        } catch (\Exception $e) {
            $this->deleteDirectory($tempDir);
            throw $e;
        }

        // Limpar arquivos temporários
        $this->deleteDirectory($tempDir);

        return $deck;
    }

    /**
     * Processar conteúdo do card para extrair mídia
     */
    private function processCardContent($content, $mediaFiles, $deckId)
    {
        // Extrair referências de mídia (imagens e áudio)
        // Formato Anki: <img src="filename.jpg"> ou [sound:filename.mp3]

        // Processar tags sound
        $content = preg_replace_callback(
            '/\[sound:([^\]]+)\]/',
            function ($matches) use ($deckId) {
                $filename = $matches[1];
                return '<audio controls class="w-full max-w-xs my-2"><source src="/storage/anki-media/' . $deckId . '/' . $filename . '" type="audio/mpeg">Seu navegador não suporta áudio.</audio>';
            },
            $content
        );

        // Processar tags img
        $content = preg_replace_callback(
            '/src="([^"]+)"/',
            function ($matches) use ($deckId) {
                $filename = $matches[1];
                if (file_exists(storage_path('app/anki-media/' . $deckId . '/' . $filename))) {
                    return 'src="/storage/anki-media/' . $deckId . '/' . $filename . '"';
                }
                return $matches[0];
            },
            $content
        );

        return $content;
    }

    /**
     * Importar de um arquivo CSV simples (pergunta|resposta)
     */
    public function importFromCsv($filePath, $submoduleId, $deckName = null)
    {
        $fileFullPath = storage_path('app/' . $filePath);

        if (!file_exists($fileFullPath)) {
            throw new \Exception('Arquivo CSV não encontrado: ' . $fileFullPath);
        }

        // Criar ou atualizar o deck
        $deck = AnkiDeck::updateOrCreate(
            ['submodule_id' => $submoduleId],
            [
                'name' => $deckName ?? basename($filePath, '.csv'),
                'file_path' => $filePath,
            ]
        );

        // Limpar cards antigos
        $deck->cards()->delete();

        $file = fopen($fileFullPath, 'r');
        $order = 0;

        while (($line = fgetcsv($file, 0, '|')) !== false) {
            if (count($line) >= 2 && !empty($line[0])) {
                AnkiCard::create([
                    'anki_deck_id' => $deck->id,
                    'front' => trim($line[0]),
                    'back' => trim($line[1]),
                    'tags' => $line[2] ?? '',
                    'order' => $order++,
                ]);
            }
        }

        fclose($file);

        $deck->update(['total_cards' => $order]);

        return $deck;
    }

    /**
     * Deletar um diretório recursivamente
     */
    private function deleteDirectory($dir)
    {
        if (!is_dir($dir)) {
            return true;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            (is_dir($path)) ? $this->deleteDirectory($path) : unlink($path);
        }

        return rmdir($dir);
    }
}

