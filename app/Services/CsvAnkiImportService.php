<?php

namespace App\Services;

use App\Models\AnkiCard;
use App\Models\AnkiDeck;
use Illuminate\Support\Facades\Log;

class CsvAnkiImportService
{
    /**
     * Importar cards de um arquivo CSV
     * Esperado: Front, Back, Audio (colunas)
     */
    public function importFromCsv($filePath, $submoduleId, $deckName = null)
    {
        $resolved = $this->resolveFilePath((string) $filePath);

        if (!file_exists($resolved)) {
            throw new \Exception('Arquivo CSV não encontrado: ' . $resolved);
        }

        $cards = $this->parseCardsFromCsv($resolved);

        if (empty($cards)) {
            throw new \Exception('Nenhum card válido foi encontrado no CSV (verifique colunas Front/Back e conteúdo).');
        }

        $normalizedPath = $this->normalizeStoredPath((string) $filePath);

        $deck = AnkiDeck::updateOrCreate(
            ['file_path' => $normalizedPath],
            [
                'submodule_id' => $submoduleId,
                'name' => $deckName ?? basename($normalizedPath, '.csv'),
            ]
        );

        $deck->cards()->delete();

        $createdCount = 0;
        $order = 0;

        foreach ($cards as $cardData) {
            try {
                $extra = null;
                if (!empty($cardData['audio'])) {
                    $extra = $this->buildAudioHtml($cardData['audio']);
                }

                AnkiCard::create([
                    'anki_deck_id' => $deck->id,
                    'front' => $cardData['front'],
                    'back' => $cardData['back'],
                    'extra' => $extra,
                    'tags' => '',
                    'order' => $order++,
                ]);

                $createdCount++;
            } catch (\Exception $e) {
                Log::warning('Erro ao criar card CSV: ' . $e->getMessage());
            }
        }

        if ($createdCount === 0) {
            throw new \Exception('Não foi possível salvar os cards do CSV.');
        }

        $deck->update(['total_cards' => $createdCount]);

        return [
            'deck_id' => $deck->id,
            'deck_name' => $deck->name,
            'cards_created' => $createdCount,
            'total_cards' => count($cards),
        ];
    }

    /**
     * Parsear cards do CSV
     * Formato esperado: Front,Back,Audio (com suporte a delimitador ; e TAB)
     */
    private function parseCardsFromCsv(string $filePath): array
    {
        [$handle, $delimiter] = $this->openCsvHandle($filePath);

        try {
            $header = $this->readCsvRow($handle, $delimiter);
            if (!$header) {
                throw new \Exception('CSV vazio ou inválido.');
            }

            $header = $this->normalizeHeader($header);

            $frontCol = $this->findColumn($header, ['front', 'pergunta', 'frente', 'question']);
            $backCol = $this->findColumn($header, ['back', 'resposta', 'verso', 'answer']);
            $audioCol = $this->findColumn($header, ['audio', 'áudio', 'som', 'file', 'arquivo', 'mp3']);

            if ($frontCol === null || $backCol === null) {
                throw new \Exception('Colunas obrigatórias não encontradas. Use cabeçalho com Front e Back.');
            }

            $cards = [];

            while (($row = $this->readCsvRow($handle, $delimiter)) !== false) {
                if (empty(array_filter($row, fn ($value) => trim((string) $value) !== ''))) {
                    continue;
                }

                $front = isset($row[$frontCol]) ? trim((string) $row[$frontCol]) : '';
                $back = isset($row[$backCol]) ? trim((string) $row[$backCol]) : '';
                $audio = ($audioCol !== null && isset($row[$audioCol])) ? trim((string) $row[$audioCol]) : '';

                if ($front === '' || $back === '') {
                    continue;
                }

                $cards[] = [
                    'front' => $front,
                    'back' => $back,
                    'audio' => $audio !== '' ? $audio : null,
                ];
            }

            return $cards;
        } finally {
            fclose($handle);
        }
    }

    /**
     * Obter preview de um CSV
     */
    public function getCsvInfo($filePath, $limit = 3)
    {
        $resolved = $this->resolveFilePath((string) $filePath);

        if (!file_exists($resolved)) {
            throw new \Exception('Arquivo CSV não encontrado: ' . $resolved);
        }

        [$handle, $delimiter] = $this->openCsvHandle($resolved);

        try {
            $header = $this->readCsvRow($handle, $delimiter);
            if (!$header) {
                throw new \Exception('CSV vazio ou inválido.');
            }

            $header = $this->normalizeHeader($header);

            $frontCol = $this->findColumn($header, ['front', 'pergunta', 'frente', 'question']);
            $backCol = $this->findColumn($header, ['back', 'resposta', 'verso', 'answer']);
            $audioCol = $this->findColumn($header, ['audio', 'áudio', 'som', 'file', 'arquivo', 'mp3']);

            if ($frontCol === null || $backCol === null) {
                throw new \Exception('Colunas obrigatórias não encontradas. Use cabeçalho com Front e Back.');
            }

            $preview = [];
            $totalCards = 0;

            while (($row = $this->readCsvRow($handle, $delimiter)) !== false) {
                if (empty(array_filter($row, fn ($value) => trim((string) $value) !== ''))) {
                    continue;
                }

                $front = isset($row[$frontCol]) ? trim((string) $row[$frontCol]) : '';
                $back = isset($row[$backCol]) ? trim((string) $row[$backCol]) : '';
                $audio = ($audioCol !== null && isset($row[$audioCol])) ? trim((string) $row[$audioCol]) : '';

                if ($front === '' || $back === '') {
                    continue;
                }

                $totalCards++;

                if (count($preview) < $limit) {
                    $preview[] = [
                        'front' => $front,
                        'back' => $back,
                        'audio' => $audio !== '' ? $audio : null,
                    ];
                }
            }

            return [
                'file_path' => $this->normalizeStoredPath((string) $filePath),
                'file_name' => basename($resolved),
                'file_size' => filesize($resolved),
                'delimiter' => $delimiter === "\t" ? 'TAB' : $delimiter,
                'total_cards' => $totalCards,
                'preview' => $preview,
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao ler CSV: ' . $e->getMessage());
            throw $e;
        } finally {
            fclose($handle);
        }
    }

    private function resolveFilePath(string $path): string
    {
        $normalized = str_replace('\\', '/', trim($path));

        if ($normalized === '') {
            return storage_path('app/');
        }

        if (preg_match('/^[A-Za-z]:\//', $normalized) && file_exists($normalized)) {
            return $normalized;
        }

        if (str_starts_with($normalized, 'storage/app/')) {
            $relative = substr($normalized, strlen('storage/app/'));
            return storage_path('app/' . ltrim($relative, '/'));
        }

        if (str_starts_with($normalized, 'app/')) {
            $relative = substr($normalized, strlen('app/'));
            return storage_path('app/' . ltrim($relative, '/'));
        }

        return storage_path('app/' . ltrim($normalized, '/'));
    }

    private function normalizeStoredPath(string $path): string
    {
        $normalized = str_replace('\\', '/', trim($path));

        if (str_starts_with($normalized, 'storage/app/')) {
            return ltrim(substr($normalized, strlen('storage/app/')), '/');
        }

        if (str_starts_with($normalized, 'app/')) {
            return ltrim(substr($normalized, strlen('app/')), '/');
        }

        return ltrim($normalized, '/');
    }

    private function openCsvHandle(string $filePath): array
    {
        if (!($handle = fopen($filePath, 'r'))) {
            throw new \Exception('Não foi possível abrir o arquivo CSV.');
        }

        $delimiter = $this->detectDelimiter($filePath);

        return [$handle, $delimiter];
    }

    private function detectDelimiter(string $filePath): string
    {
        $sample = file_get_contents($filePath, false, null, 0, 4096) ?: '';
        $firstLine = strtok($sample, "\n") ?: $sample;

        $candidates = [',', ';', "\t", '|'];
        $best = ',';
        $bestCount = -1;

        foreach ($candidates as $candidate) {
            $count = substr_count($firstLine, $candidate);
            if ($count > $bestCount) {
                $best = $candidate;
                $bestCount = $count;
            }
        }

        return $best;
    }

    private function readCsvRow($handle, string $delimiter)
    {
        return fgetcsv($handle, 0, $delimiter);
    }

    private function normalizeHeader(array $header): array
    {
        if (!empty($header[0])) {
            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header[0]) ?? $header[0];
        }

        return array_map(fn ($column) => trim((string) $column), $header);
    }

    private function findColumn(array $header, array $possibleNames): ?int
    {
        foreach ($header as $idx => $col) {
            $colLower = $this->normalizeWord($col);
            foreach ($possibleNames as $name) {
                if ($colLower === $this->normalizeWord($name)) {
                    return $idx;
                }
            }
        }

        return null;
    }

    private function normalizeWord(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = strtr($value, [
            'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a',
            'é' => 'e', 'ê' => 'e',
            'í' => 'i',
            'ó' => 'o', 'ô' => 'o', 'õ' => 'o',
            'ú' => 'u',
            'ç' => 'c',
        ]);

        return $value;
    }

    private function buildAudioHtml(string $audio): string
    {
        $audio = trim($audio);

        if (stripos($audio, '<audio') !== false) {
            return $audio;
        }

        if (preg_match('/^\[sound:(.+)\]$/i', $audio, $matches)) {
            $audio = trim($matches[1]);
        }

        $safeUrl = htmlspecialchars($audio, ENT_QUOTES, 'UTF-8');

        return '<audio controls preload="none"><source src="' . $safeUrl . '"></audio>';
    }
}
