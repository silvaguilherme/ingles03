<?php

namespace App\Services;

use App\Models\AnkiDeck;
use App\Models\AnkiCard;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser;

class PdfAnkiImportService
{
    /**
     * Importar cards de um arquivo PDF
     */
    public function importFromPdf($filePath, $submoduleId, $deckName = null)
    {
        $fileFullPath = storage_path('app/' . $filePath);

        if (!file_exists($fileFullPath)) {
            throw new \Exception('Arquivo PDF não encontrado: ' . $fileFullPath);
        }

        // Extrair texto do PDF
        $text = $this->extractTextFromPdf($fileFullPath);

        if (empty($text)) {
            throw new \Exception('Não foi possível extrair texto do PDF.');
        }

        // Parsear cards do texto
        $cards = $this->parseCardsFromText($text);

        if (empty($cards)) {
            throw new \Exception('Nenhum card foi encontrado no formato Front:/Back:');
        }

        // Criar ou atualizar deck
        $deck = AnkiDeck::updateOrCreate(
            ['file_path' => $filePath],
            [
                'submodule_id' => $submoduleId,
                'name' => $deckName ?? basename($filePath, '.pdf'),
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
     * Extrair texto do PDF usando smalot/pdfparser
     */
    private function extractTextFromPdf($filePath)
    {
        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($filePath);
            
            // Extrair texto de todas as páginas
            $text = $pdf->getText();
            
            if (empty($text)) {
                throw new \Exception('Nenhum texto encontrado no PDF');
            }
            
            return $text;
        } catch (\Exception $e) {
            Log::error('Erro ao extrair PDF: ' . $e->getMessage());
            throw new \Exception('Erro ao extrair texto do PDF: ' . $e->getMessage());
        }
    }

    /**
     * Parsear cards do texto usando padrão Front:/Back:
     */
    private function parseCardsFromText($text)
    {
        $cards = [];
        
        // Dividir por linha de dashes
        $sections = preg_split('/\-{20,}/', $text);

        foreach ($sections as $section) {
            $section = trim($section);
            
            if (empty($section)) {
                continue;
            }

            // Encontrar Front: e Back:
            $frontMatch = [];
            $backMatch = [];

            // Padrão mais robusto para extrair Front e Back
            if (preg_match('/Front:\s*\n\s*(.+?)(?=\n\s*Back:)/is', $section, $frontMatch)) {
                $front = trim($frontMatch[1]);
                
                if (preg_match('/Back:\s*\n\s*(.+?)$/is', $section, $backMatch)) {
                    $back = trim($backMatch[1]);

                    // Remover copyright lines que podem estar no back
                    $back = preg_replace('/©.*?\n/', '', $back);
                    $back = preg_replace('/^[A-Za-z0-9+\/=]{20,}$/', '', $back); // Remove base64 encoded strings
                    $back = trim($back);

                    if (!empty($front) && !empty($back)) {
                        // Remover trash/metadata do front e back
                        $front = $this->cleanCardText($front);
                        $back = $this->cleanCardText($back);

                        if (!empty($front) && !empty($back)) {
                            $cards[] = [
                                'front' => $front,
                                'back' => $back,
                            ];
                        }
                    }
                }
            }
        }

        return $cards;
    }

    /**
     * Limpar texto do card removendo lixo
     */
    private function cleanCardText($text)
    {
        // Remover headers tipo CIMV 5.0
        $text = preg_replace('/^(CIMV|Grammar Lesson).*$/im', '', $text);
        
        // Remover linhas de metadados
        $text = preg_replace('/^(©|[A-Za-z0-9+\/=]{20,}|\d+)$/m', '', $text);
        
        // Remover linhas vazias múltiplas
        $text = preg_replace('/\n\s*\n+/', "\n", $text);
        
        return trim($text);
    }

    /**
     * Obter informações do PDF
     */
    public function getPdfInfo($filePath)
    {
        $fileFullPath = storage_path('app/' . $filePath);

        if (!file_exists($fileFullPath)) {
            return null;
        }

        try {
            $text = $this->extractTextFromPdf($fileFullPath);
            $cards = $this->parseCardsFromText($text);

            return [
                'file_path' => $filePath,
                'file_name' => basename($filePath),
                'file_size' => filesize($fileFullPath),
                'estimated_cards' => count($cards),
            ];
        } catch (\Exception $e) {
            return null;
        }
    }
}
