<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PDO;
use ZipArchive;

class AnkiDebugApkg extends Command
{
    protected $signature = 'anki:debug {apkgPath}';
    protected $description = 'Debug APKG file structure and content';

    public function handle()
    {
        $apkgPath = $this->argument('apkgPath');

        if (!file_exists($apkgPath)) {
            $this->error("Arquivo não encontrado: {$apkgPath}");
            return 1;
        }

        $this->info("Analisando: {$apkgPath}");
        $this->info(str_repeat('=', 60));

        // Extrair APKG
        $tempDir = storage_path('app/temp/anki-debug-' . time());
        mkdir($tempDir, 0755, true);

        $zip = new ZipArchive;
        if ($zip->open($apkgPath) !== TRUE) {
            $this->error('Falha ao abrir arquivo APKG');
            return 1;
        }

        $zip->extractTo($tempDir);
        $zip->close();

        // Listar arquivos extraídos
        $this->line('');
        $this->info('Arquivos no APKG:');
        $files = scandir($tempDir);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $size = filesize($tempDir . '/' . $file);
                $this->line("  - {$file} (" . number_format($size) . " bytes)");
            }
        }

        // Verificar banco de dados
        $dbPath = $tempDir . '/collection.anki2';
        if (!file_exists($dbPath)) {
            $dbPath = $tempDir . '/collection.anki21';
        }

        if (!file_exists($dbPath)) {
            $this->error('Banco de dados não encontrado!');
            $this->line('Arquivos disponíveis:');
            foreach (scandir($tempDir) as $file) {
                if ($file !== '.' && $file !== '..') {
                    $this->line("  - {$file}");
                }
            }
            $this->deleteDirectory($tempDir);
            return 1;
        }

        $this->line('');
        $this->info('Conectando ao banco SQLite...');

        try {
            $pdo = new PDO('sqlite:' . $dbPath);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Listar tabelas
            $this->line('');
            $this->info('Tabelas no banco:');
            $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
            foreach ($tables as $table) {
                $count = $pdo->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
                $this->line("  - {$table} ({$count} registros)");
            }

            // Analisar notes
            if (in_array('notes', $tables)) {
                $this->line('');
                $this->info('Primeiras 3 notes:');
                $notes = $pdo->query("SELECT id, flds, tags FROM notes LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($notes as $i => $note) {
                    $this->line('');
                    $noteNum = $i + 1;
                    $this->line("Note #{$noteNum} (ID: {$note['id']}):");
                    
                    $fields = explode("\x1f", $note['flds']);
                    $this->line("  Campos: " . count($fields));
                    foreach ($fields as $j => $field) {
                        $preview = mb_substr(strip_tags($field), 0, 100);
                        if (mb_strlen(strip_tags($field)) > 100) {
                            $preview .= '...';
                        }
                        $this->line("    Campo {$j}: " . ($preview ?: '(vazio)'));
                    }
                    
                    if ($note['tags']) {
                        $this->line("  Tags: {$note['tags']}");
                    }
                }
            }

            // Analisar cards
            if (in_array('cards', $tables)) {
                $this->line('');
                $this->info('Cards:');
                $cardCount = $pdo->query("SELECT COUNT(*) FROM cards")->fetchColumn();
                $this->line("  Total: {$cardCount} cards");
                
                if ($cardCount > 0) {
                    $cards = $pdo->query("SELECT id, nid, ord FROM cards LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($cards as $card) {
                        $this->line("  Card ID {$card['id']}: note_id={$card['nid']}, ord={$card['ord']}");
                    }
                }
            }

            // Verificar collection info
            if (in_array('col', $tables)) {
                $this->line('');
                $this->info('Informações da coleção:');
                $col = $pdo->query("SELECT * FROM col LIMIT 1")->fetch(PDO::FETCH_ASSOC);
                if ($col) {
                    foreach ($col as $key => $value) {
                        if (strlen($value) < 200) {
                            $this->line("  {$key}: {$value}");
                        } else {
                            $this->line("  {$key}: (dados longos - " . strlen($value) . " caracteres)");
                        }
                    }
                }
            }

        } catch (\Exception $e) {
            $this->error('Erro ao ler banco de dados:');
            $this->error($e->getMessage());
            $this->line('');
            $this->error('Stack trace:');
            $this->error($e->getTraceAsString());
        }

        // Limpar
        $this->deleteDirectory($tempDir);
        
        $this->line('');
        $this->info(str_repeat('=', 60));
        $this->info('Análise concluída!');

        return 0;
    }

    private function deleteDirectory($dir)
    {
        if (!file_exists($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}
