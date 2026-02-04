<?php

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Support\Str;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Course;
use App\Models\Module;
use App\Models\SubModule;
use App\Models\Lesson;


$basePath = storage_path('app/public/videos');

function slugify($text) {
    return Str::slug($text, '-');
}



// Agrupar arquivos por curso/modulo/submodulo
$filesByGroup = [];
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($basePath));
foreach ($rii as $file) {
    if ($file->isDir()) continue;
    $ext = strtolower($file->getExtension());
    if (!in_array($ext, ['mp4', 'pdf', 'mp3', 'wav', 'm4a', 'apkg'])) continue;

    $relative = str_replace($basePath . DIRECTORY_SEPARATOR, '', $file->getPathname());
    $parts = explode(DIRECTORY_SEPARATOR, $relative);
    if (count($parts) !== 4) {
        echo "Ignorando: $relative (esperado: CURSO/MODULO/SUBMODULO/ARQUIVO)\n";
        continue;
    }
    list($curso, $modulo, $submodulo, $arquivo) = $parts;
    $filesByGroup[$curso][$modulo][$submodulo][] = $arquivo;
}


foreach ($filesByGroup as $curso => $modulos) {
    echo "[DEBUG] Curso: $curso\n";
    // 1. Course
    $course = Course::firstOrCreate([
        'title' => $curso,
    ], [
        'description' => $curso,
        'order' => 1,
    ]);

    foreach ($modulos as $modulo => $submodulos) {
        echo "  [DEBUG] Módulo: $modulo\n";
        // 2. Module
        $module = Module::firstOrCreate([
            'title' => $modulo,
            'course_id' => $course->id,
        ], [
            'order' => 1,
            'description' => $modulo,
        ]);

        foreach ($submodulos as $submodulo => $videos) {
            echo "    [DEBUG] Submódulo: $submodulo\n";
            // 3. SubModule
            $subModule = SubModule::firstOrCreate([
                'title' => $submodulo,
                'module_id' => $module->id,
            ], [
                'order' => 1,
                'description' => $submodulo,
            ]);

            // Buscar todos os arquivos suportados (mp4, pdf, mp3, wav, m4a, apkg)
            $allFiles = [];
            $audioFiles = [];
            $ankiFiles = [];
            $dirPath = $basePath . DIRECTORY_SEPARATOR . $curso . DIRECTORY_SEPARATOR . $modulo . DIRECTORY_SEPARATOR . $submodulo;
            
            foreach (scandir($dirPath) as $fileName) {
                if ($fileName === '.' || $fileName === '..') continue;
                $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                
                // Separar por tipo
                if (in_array($ext, ['mp4', 'pdf'])) {
                    $allFiles[] = $fileName;
                } elseif (in_array($ext, ['mp3', 'wav', 'm4a'])) {
                    $audioFiles[] = $fileName;
                } elseif ($ext === 'apkg') {
                    $ankiFiles[] = $fileName;
                }
            }
            
            // Ordenar alfabeticamente
            sort($allFiles, SORT_NATURAL | SORT_FLAG_CASE);
            sort($audioFiles, SORT_NATURAL | SORT_FLAG_CASE);
            sort($ankiFiles, SORT_NATURAL | SORT_FLAG_CASE);
            
            echo "      [DEBUG] Ordem dos arquivos:".PHP_EOL;
            foreach ($allFiles as $idx => $fileDebug) {
                echo "        [".($idx+1)."] $fileDebug".PHP_EOL;
            }
            
            $order = 1;
            
            // 1. Importar vídeos e PDFs
            foreach ($allFiles as $fileName) {
                $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $lessonTitle = preg_replace('/\.(mp4|pdf)$/i', '', $fileName);
                $contentType = $ext === 'pdf' ? 'pdf' : 'video';
                $lesson = Lesson::firstOrCreate([
                    'title' => $lessonTitle,
                    'sub_module_id' => $subModule->id,
                ], [
                    'order' => $order,
                    'content_type' => $contentType,
                    'video_key' => 'videos/' . $curso . '/' . $modulo . '/' . $submodulo . '/' . $fileName,
                    'duration_seconds' => 0,
                ]);
                echo "      Importado: $curso / $modulo / $submodulo / $lessonTitle (order: $order, tipo: $contentType)\n";
                $order++;
            }
            
            // 2. Criar lição com áudios (se houver)
            if (!empty($audioFiles)) {
                $audioLessonTitle = 'Áudios';
                $audioList = implode("\n", array_map(fn($f) => '- ' . preg_replace('/\.(mp3|wav|m4a)$/i', '', $f), $audioFiles));
                
                $audioLesson = Lesson::firstOrCreate([
                    'title' => $audioLessonTitle,
                    'sub_module_id' => $subModule->id,
                ], [
                    'order' => $order,
                    'content_type' => 'audio',
                    'video_key' => 'audios/' . $curso . '/' . $modulo . '/' . $submodulo . '/',
                    'duration_seconds' => 0,
                    'description' => "Arquivos de áudio disponíveis:\n\n$audioList",
                ]);
                echo "      Importado: $curso / $modulo / $submodulo / $audioLessonTitle (order: $order, tipo: audio, arquivos: " . count($audioFiles) . ")\n";
                $order++;
            }
            
            // 3. Criar lição com Anki (se houver)
            if (!empty($ankiFiles)) {
                $ankiLessonTitle = 'Revisão Anki';
                $ankiList = implode("\n", array_map(fn($f) => '- ' . preg_replace('/\.apkg$/i', '', $f), $ankiFiles));
                
                $ankiLesson = Lesson::firstOrCreate([
                    'title' => $ankiLessonTitle,
                    'sub_module_id' => $subModule->id,
                ], [
                    'order' => $order,
                    'content_type' => 'anki',
                    'video_key' => 'anki/' . $curso . '/' . $modulo . '/' . $submodulo . '/',
                    'duration_seconds' => 0,
                    'description' => "Arquivos Anki disponíveis para revisão:\n\n$ankiList",
                ]);
                echo "      Importado: $curso / $modulo / $submodulo / $ankiLessonTitle (order: $order, tipo: anki, arquivos: " . count($ankiFiles) . ")\n";
                $order++;
            }
        }
    }
}

echo "\nImportação concluída!\n";
