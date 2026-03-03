<?php

namespace App\Services;

use App\Models\AnkiDeck;
use App\Models\AnkiCard;
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

        // Usar file_path como chave única para permitir múltiplos decks por submodulo
        $deck = AnkiDeck::updateOrCreate(
            ['file_path' => $filePath],
            [
                'submodule_id' => $submoduleId,
                'name' => $deckName ?? basename($filePath, '.apkg'),
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

        // Conectar ao banco de dados SQLite - tentar anki21 primeiro, depois anki2
        $dbPath = null;
        if (file_exists($tempDir . '/collection.anki21')) {
            $dbPath = $tempDir . '/collection.anki21';
            \Log::info("Usando collection.anki21 para: {$fileFullPath}");
        } elseif (file_exists($tempDir . '/collection.anki2')) {
            $dbPath = $tempDir . '/collection.anki2';
            \Log::info("Usando collection.anki2 para: {$fileFullPath}");
        }

        if (!$dbPath) {
            $this->deleteDirectory($tempDir);
            throw new \Exception('Banco de dados Anki não encontrado no APKG');
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

            // Verificar se as tabelas existem
            $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
            
            if (!in_array('notes', $tables) || !in_array('cards', $tables)) {
                throw new \Exception('Arquivo APKG inválido: tabelas necessárias não encontradas');
            }

            // Copiar arquivos de midia para o storage
            $this->copyMediaFiles($mediaFiles, $tempDir, $mediaDir);

            // Consultar os notes
            $stmt = $pdo->prepare('SELECT id, flds, tags FROM notes');
            $stmt->execute();
            $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($notes)) {
                \Log::warning('APKG sem notes: ' . $fileFullPath);
            }

            $notesMap = [];
            foreach ($notes as $note) {
                $notesMap[$note['id']] = $note;
            }

            // Consultar os cards (para importar todos os cards do deck)
            $cardsStmt = $pdo->prepare('SELECT id, nid, ord FROM cards ORDER BY id');
            $cardsStmt->execute();
            $cards = $cardsStmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($cards)) {
                \Log::warning('APKG sem cards: ' . $fileFullPath);
            }

            // Limpar cards antigos do deck
            $deck->cards()->delete();

            $order = 0;
            foreach ($cards as $card) {
                $note = $notesMap[$card['nid']] ?? null;
                if (!$note) {
                    \Log::warning("Card sem note associada: card_id={$card['id']}, nid={$card['nid']}");
                    continue;
                }

                // Dividir os campos (separados por ASCII 31)
                $fields = explode("\x1f", $note['flds']);

                if (count($fields) >= 2) {
                    $front = $fields[0] ?? '';
                    $back = $fields[1] ?? '';
                    $extraFields = array_slice($fields, 2);
                    $extra = null;

                    // Ignorar mensagens de erro do Anki
                    $frontText = trim(strip_tags($front));
                    if (stripos($frontText, 'This file requires') !== false || 
                        stripos($frontText, 'newer version') !== false ||
                        stripos($frontText, 'upgrade Anki') !== false) {
                        \Log::warning("Ignorando mensagem de erro do Anki: {$frontText}");
                        continue;
                    }

                    // Verificar se os campos não estão vazios
                    if (empty($frontText) && empty(trim(strip_tags($back)))) {
                        \Log::warning("Card com campos vazios: card_id={$card['id']}");
                        continue;
                    }

                    // Processar conteudo HTML/midia
                    $front = $this->processCardContent($front, $mediaFiles, $deck->id);
                    $back = $this->processCardContent($back, $mediaFiles, $deck->id);

                    if (!empty($extraFields)) {
                        $processedExtra = [];
                        foreach ($extraFields as $fieldValue) {
                            $fieldValue = trim((string) $fieldValue);
                            if ($fieldValue === '') {
                                continue;
                            }

                            $processedExtra[] = $this->processCardContent($fieldValue, $mediaFiles, $deck->id);
                        }

                        if (!empty($processedExtra)) {
                            $extra = implode('<hr class="my-3" />', $processedExtra);
                        }
                    }

                    AnkiCard::create([
                        'anki_deck_id' => $deck->id,
                        'front' => $front,
                        'back' => $back,
                        'extra' => $extra,
                        'tags' => $note['tags'] ?? '',
                        'order' => $order++,
                    ]);
                } else {
                    \Log::warning("Note com menos de 2 campos: note_id={$note['id']}, campos=" . count($fields));
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
                $filename = $this->normalizeMediaFilename($matches[1]);
                $mediaUrl = '/storage/anki-media/' . $deckId . '/' . rawurlencode($filename);
                return '<audio controls class="w-full max-w-xs my-2"><source src="' . $mediaUrl . '">Seu navegador não suporta áudio.</audio>';
            },
            $content
        );

        // Processar tags img
        $content = preg_replace_callback(
            '/src="([^"]+)"/',
            function ($matches) use ($deckId) {
                $filename = $this->normalizeMediaFilename($matches[1]);
                if (file_exists(storage_path('app/anki-media/' . $deckId . '/' . $filename))) {
                    return 'src="/storage/anki-media/' . $deckId . '/' . rawurlencode($filename) . '"';
                }
                return $matches[0];
            },
            $content
        );

        return $content;
    }

    /**
     * Normalizar nome de arquivo de mídia vindo do APKG/HTML
     */
    private function normalizeMediaFilename($filename)
    {
        $decoded = html_entity_decode((string) $filename, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $decoded = urldecode($decoded);
        return basename(trim($decoded));
    }

    /**
     * Copiar arquivos de midia do APKG para o storage
     */
    private function copyMediaFiles($mediaFiles, $tempDir, $mediaDir)
    {
        if (empty($mediaFiles)) {
            return;
        }

        foreach ($mediaFiles as $index => $filename) {
            if (!is_string($filename) || $filename === '') {
                continue;
            }

            $safeName = $this->normalizeMediaFilename($filename);
            $source = $tempDir . DIRECTORY_SEPARATOR . $index;
            $destination = $mediaDir . DIRECTORY_SEPARATOR . $safeName;

            if (file_exists($source)) {
                @copy($source, $destination);
            }
        }
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

