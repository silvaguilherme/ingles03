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
    if (strtolower($file->getExtension()) !== 'mp4') continue;

    $relative = str_replace($basePath . DIRECTORY_SEPARATOR, '', $file->getPathname());
    $parts = explode(DIRECTORY_SEPARATOR, $relative);
    if (count($parts) !== 4) {
        echo "Ignorando: $relative (esperado: CURSO/MODULO/SUBMODULO/ARQUIVO.mp4)\n";
        continue;
    }
    list($curso, $modulo, $submodulo, $video) = $parts;
    $filesByGroup[$curso][$modulo][$submodulo][] = $video;
}

foreach ($filesByGroup as $curso => $modulos) {
    // 1. Course
    $course = Course::firstOrCreate([
        'title' => $curso,
    ], [
        'description' => $curso,
        'order' => 1,
    ]);

    foreach ($modulos as $modulo => $submodulos) {
        // 2. Module
        $module = Module::firstOrCreate([
            'title' => $modulo,
            'course_id' => $course->id,
        ], [
            'order' => 1,
            'description' => $modulo,
        ]);

        foreach ($submodulos as $submodulo => $videos) {
            // 3. SubModule
            $subModule = SubModule::firstOrCreate([
                'title' => $submodulo,
                'module_id' => $module->id,
            ], [
                'order' => 1,
                'description' => $submodulo,
            ]);

            // Ordenar os vídeos alfabeticamente
            sort($videos, SORT_NATURAL | SORT_FLAG_CASE);
            $order = 1;
            foreach ($videos as $video) {
                $lessonTitle = preg_replace('/\.mp4$/i', '', $video);
                $lesson = Lesson::firstOrCreate([
                    'title' => $lessonTitle,
                    'sub_module_id' => $subModule->id,
                ], [
                    'order' => $order,
                    'content_type' => 'video',
                    'video_key' => 'videos/' . $curso . '/' . $modulo . '/' . $submodulo . '/' . $video,
                    'duration_seconds' => 0,
                ]);
                echo "Importado: $curso / $modulo / $submodulo / $lessonTitle\n";
                $order++;
            }
        }
    }
}

echo "\nImportação concluída!\n";
