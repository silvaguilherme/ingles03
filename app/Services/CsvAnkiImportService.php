<?php

namespace App\Services;

use App\Models\AnkiDeck;
use App\Models\AnkiCard;
use Illuminate\Support\Facades\Log;

class CsvAnkiImportService
{
    /**
     * Importar cards de um arquivo CSV
     * Esperado: Front, Back, Audio (colunas)
     */
    public function importFromCsv($filePath, $submoduleId, $deckName = null)
    {
        $fileFullPath = storage_path('app/' . $filePath);

        if (!file_exists($fileFullPath)) {
            throw new \Exception('Arquivo CSV não encontrado: ' . $fileFullPath);
        }

        // Parsear CSV
        $cards = $this->parseCardsFromCsv($fileFullPath);

        if (empty($cards)) {
            throw new \Exception('Nenhum card foi encontrado no CSV');
        }

        // Criar ou atualizar deck
        $deck = AnkiDeck::updateOrCreate(
            ['file_path' => $filePath],
            [
                'submodule_id' => $submoduleId,
                'name' => $deckName ?? basename($filePath, '.csv'),
            ]
        );

        // Limpar cards antigos
        AnkiCard::where('deck_id', $deck->id)->delete();

        // Inserir novos cards
        $createdCount = 0;
        foreach ($cards as $cardData) {
            try {
                AnkiCard::create([
                    'deck_id' => $deck->id,
                    'front' => $cardData['front'],
                    'back' => $cardData['back'],
                    'audio_path' => $cardData['audio'] ?? null,
                    'difficulty' => 0,
                    'ease_factor' => 2.5,
                    'interval' => 0,
                    'next_review' => now(),
                ]);
                $createdCount++;
            } catch (\Exception $e) {
                Log::warning('Erro ao criar card: ' . $e->getMessage());
            }
        }

        return [
            'deck_id' => $deck->id,
            'deck_name' => $deck->name,
            'cards_created' => $createdCount,
            'total_cards' => count($cards),
        ];
    }

    /**
     * Parsear cards do CSV
     * Esperado formato: Front,Back,Audio
     */
    private function parseCardsFromCsv($filePath)
    {
        $cards = [];
        
        if (!($handle = fopen($filePath, 'r'))) {
            throw new \Exception('Não foi possível abrir o arquivo CSV');
        }

        // Ler cabeçalho
        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            throw new \Exception('CSV vazio ou inválido');
        }

        // Mapear colunas
        $frontCol = $this->findColumn($header, ['front', 'pergunta', 'frente', 'question']);
        $backCol = $this->findColumn($header, ['back', 'resposta', 'verso', 'answer']);
        $audioCol = $this->findColumn($header, ['audio', 'áudio', 'som', 'file']);

        if ($frontCol === null || $backCol === null) {
            fclose($handle);
            throw new \Exception('Colunas "Front" e "Back" não encontradas no CSV');
        }

        // Ler linhas
        $lineNumber = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $lineNumber++;
            
            // Pular linhas vazias
            if (empty(array_filter($row))) {
                continue;
            }

            try {
                $front = isset($row[$frontCol]) ? trim($row[$frontCol]) : '';
                $back = isset($row[$backCol]) ? trim($row[$backCol]) : '';
                $audio = isset($row[$audioCol]) ? trim($row[$audioCol]) : '';

                if (!empty($front) && !empty($back)) {
                    $cards[] = [
                        'front' => $front,
                        'back' => $back,
                        'audio' => !empty($audio) ? $audio : null,
                    ];
                }
            } catch (\Exception $e) {
                Log::warning("Erro ao processar linha {$lineNumber}: " . $e->getMessage());
            }
        }

        fclose($handle);
        return $cards;
    }

    /**
     * Encontrar índice de coluna pelo nome
     */
    private function findColumn($header, $possibleNames)
    {
        foreach ($header as $idx => $col) {
            $colLower = mb_strtolower(trim($col));
            foreach ($possibleNames as $name) {
                if ($colLower === mb_strtolower($name)) {
                    return $idx;
                }
            }
        }
        return null;
    }

    /**
     * Obter preview de um CSV
     */
    public function getCsvInfo($filePath, $limit = 3)
    {
        $fileFullPath = storage_path('app/' . $filePath);

        if (!file_exists($fileFullPath)) {
            return null;
        }

        try {
            if (!($handle = fopen($fileFullPath, 'r'))) {
                return null;
            }

            // Ler cabeçalho
            $header = fgetcsv($handle);
            if (!$header) {
                fclose($handle);
                return null;
            }

            // Mapear colunas
            $frontCol = $this->findColumn($header, ['front', 'pergunta', 'frente', 'question']);
            $backCol = $this->findColumn($header, ['back', 'resposta', 'verso', 'answer']);
            $audioCol = $this->findColumn($header, ['audio', 'áudio', 'som', 'file']);

            if ($frontCol === null || $backCol === null) {
                fclose($handle);
                return null;
            }

            // Ler preview
            $preview = [];
            $totalCards = 0;

            while (($row = fgetcsv($handle)) !== false) {
                // Pular linhas vazias
                if (empty(array_filter($row))) {
                    continue;
                }

                $front = isset($row[$frontCol]) ? trim($row[$frontCol]) : '';
                $back = isset($row[$backCol]) ? trim($row[$backCol]) : '';
                $audio = isset($row[$audioCol]) ? trim($row[$audioCol]) : '';

                if (!empty($front) && !empty($back)) {
                    $totalCards++;
                    
                    if (count($preview) < $limit) {
                        $preview[] = [
                            'front' => $front,
                            'back' => $back,
                            'audio' => !empty($audio) ? $audio : null,
                        ];
                    }
                }
            }

            fclose($handle);

            return [
                'file_path' => $filePath,
                'file_name' => basename($filePath),
                'file_size' => filesize($fileFullPath),
                'total_cards' => $totalCards,
                'preview' => $preview,
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao ler CSV: ' . $e->getMessage());
            return null;
        }
    }
}
