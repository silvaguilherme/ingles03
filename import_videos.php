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
    $lessonTitle = preg_replace('/\.mp4$/i', '', $video);

    // 1. Course
    $course = Course::firstOrCreate([
        'title' => $curso,
    ], [
        'description' => $curso,
        'order' => 1,
    ]);

    // 2. Module
    $module = Module::firstOrCreate([
        'title' => $modulo,
        'course_id' => $course->id,
    ], [
        'order' => 1,
        'description' => $modulo,
    ]);

    // 3. SubModule
    $subModule = SubModule::firstOrCreate([
        'title' => $submodulo,
        'module_id' => $module->id,
    ], [
        'order' => 1,
        'description' => $submodulo,
    ]);

    // 4. Lesson
    $lesson = Lesson::firstOrCreate([
        'title' => $lessonTitle,
        'sub_module_id' => $subModule->id,
    ], [
        'order' => 1,
        'content_type' => 'video',
        'video_key' => 'videos/' . $curso . '/' . $modulo . '/' . $submodulo . '/' . $video,
        'duration_seconds' => 0,
    ]);

    echo "Importado: $curso / $modulo / $submodulo / $lessonTitle\n";
}

echo "\nImportação concluída!\n";
