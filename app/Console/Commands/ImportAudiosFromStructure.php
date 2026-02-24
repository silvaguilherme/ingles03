<?php

namespace App\Console\Commands;

use App\Models\Lesson;
use App\Models\Module;
use App\Models\SubModule;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportAudiosFromStructure extends Command
{
    protected $signature = 'import:audios {--base-path= : Caminho base dos submodulos}';
    protected $description = 'Importar audios da estrutura /XX/audio/ e associar as lessons';

    public function handle()
    {
        $basePath = $this->option('base-path') ?? storage_path('app/public/videos');

        if (!is_dir($basePath)) {
            $this->error("Diretorio nao encontrado: {$basePath}");
            return 1;
        }

        $this->info('=== IMPORTANDO AUDIOS ===');
        $this->newLine();

        $moduleRoots = $this->getModuleRoots($basePath);
        if (empty($moduleRoots)) {
            $this->warn('Nenhuma pasta de modulo encontrada.');
            return 0;
        }

        $linked = 0;
        $notFound = 0;
        $audioExtensions = ['mp3', 'wav', 'm4a', 'ogg'];

        foreach ($moduleRoots as $moduleRoot) {
            $moduleName = basename($moduleRoot);
            $this->info("Modulo: {$moduleName}");

            $module = $this->resolveModule($moduleRoot);
            $subModules = $module ? $module->subModules : SubModule::all();

            foreach ($subModules as $subModule) {
                $folderNum = str_pad($subModule->title, 2, '0', STR_PAD_LEFT);
                $audioDir = $moduleRoot . '/' . $folderNum . '/audio';

                if (!is_dir($audioDir)) {
                    $this->line("ℹ️  {$folderNum}/audio - Pasta nao existe");
                    continue;
                }

                $audioFiles = array_filter(File::files($audioDir), function ($file) use ($audioExtensions) {
                    return in_array(strtolower($file->getExtension()), $audioExtensions, true);
                });

                if (empty($audioFiles)) {
                    $this->line("ℹ️  {$folderNum}/audio - Nenhum audio encontrado");
                    continue;
                }

                $audioCount = count($audioFiles);
                $this->line("🔊 {$folderNum}/audio - {$audioCount} audio(s):");

                $lessons = Lesson::where('sub_module_id', $subModule->id)
                    ->get(['id', 'title'])
                    ->map(function ($lesson) {
                        return [
                            'id' => $lesson->id,
                            'title' => $lesson->title,
                            'normalized' => $this->normalizeLessonTitle($lesson->title),
                            'numbers' => $this->extractNumbers($lesson->title),
                            'is_complete' => $this->isCompleteTitle($lesson->title),
                        ];
                    })
                    ->all();

                $audiosLesson = $this->findAudiosLesson($lessons);
                if ($audiosLesson) {
                    $audioList = array_values(array_map(function ($audioFile) {
                        $path = str_replace('\\', '/', $audioFile->getPathname());
                        return str_replace(storage_path('app/public/'), '', $path);
                    }, $audioFiles));

                    Lesson::where('id', $audiosLesson['id'])->update([
                        'audio_key' => $audioList[0] ?? null,
                        'audio_list' => $audioList,
                    ]);

                    $this->line("  ✅ Lista de audio vinculada a Lesson #{$audiosLesson['id']}: {$audiosLesson['title']}");
                    $linked += count($audioList);
                    $this->newLine();
                    continue;
                }

                foreach ($audioFiles as $audioFile) {
                    $filename = $audioFile->getFilename();
                    $relativePath = str_replace(storage_path('app/public/'), '', str_replace('\\', '/', $audioFile->getPathname()));
                    $baseName = trim($audioFile->getBasename('.' . $audioFile->getExtension()));

                    $audioInfo = $this->parseAudioInfo($baseName);
                    $normalizedAudio = $audioInfo['normalized'];
                    if ($normalizedAudio === '') {
                        $this->warn("  ❌ {$filename} → Nome do audio invalido");
                        $notFound++;
                        continue;
                    }

                    $match = $this->findBestLessonMatch($audioInfo, $lessons);

                    if ($match) {
                        Lesson::where('id', $match['id'])->update(['audio_key' => $relativePath]);
                        $this->line("  ✅ {$filename} → Lesson #{$match['id']}: {$match['title']}");
                        $linked++;
                    } else {
                        $this->warn("  ❌ {$filename} → Nenhuma lesson encontrada");
                        $notFound++;
                    }
                }

                $this->newLine();
            }

            $this->newLine();
        }

        $this->info('=== RESULTADO ===');
        $this->line("✅ Associados: {$linked}");
        $this->warn("❌ Nao encontrados: {$notFound}");

        return 0;
    }

    private function getModuleRoots(string $basePath): array
    {
        $roots = [];
        $candidates = File::directories($basePath);

        foreach ($candidates as $dir) {
            if ($this->looksLikeModuleRoot($dir)) {
                $roots[] = $dir;
                continue;
            }

            foreach (File::directories($dir) as $child) {
                if ($this->looksLikeModuleRoot($child)) {
                    $roots[] = $child;
                }
            }
        }

        return array_values(array_unique($roots));
    }

    private function looksLikeModuleRoot(string $path): bool
    {
        foreach (File::directories($path) as $dir) {
            $name = basename($dir);
            if (preg_match('/^\d{1,3}$/', $name)) {
                return true;
            }
        }

        return false;
    }

    private function resolveModule(string $moduleRoot): ?Module
    {
        $dirName = basename($moduleRoot);
        if (preg_match('/^(\d{1,3})/', $dirName, $matches)) {
            $order = (int) $matches[1];
            $module = Module::where('order', $order)->first();
            if ($module) {
                return $module;
            }
        }

        $normalized = $this->normalizeName($dirName);
        return Module::all()->first(function ($module) use ($normalized) {
            return $this->normalizeName($module->title) === $normalized;
        });
    }

    private function normalizeName(string $value): string
    {
        $value = $this->normalizeText($value);
        return preg_replace('/[^a-z0-9]+/', '', $value);
    }

    private function parseAudioInfo(string $value): array
    {
        $isComplete = $this->isCompleteTitle($value);
        $numbers = $this->extractNumbers($value);
        $number = $numbers[0] ?? null;

        $value = $this->normalizeText($value);
        $value = preg_replace('/\b(audio|audiocompleto|completo|complete|completeaudio)\b/', ' ', $value);
        $value = preg_replace('/\b(audio)\b/', ' ', $value);
        $value = preg_replace('/\b(pdf|video)\b/', ' ', $value);
        $value = preg_replace('/\b\d{1,3}\b/', ' ', $value);

        $speakers = [
            'jake', 'moira', 'harry', 'daniel', 'natalie', 'kathy', 'charlie',
            'josh', 'jacob', 'steve', 'jobs',
        ];
        foreach ($speakers as $speaker) {
            $value = preg_replace('/\b' . preg_quote($speaker, '/') . '\b/', ' ', $value);
        }

        $value = preg_replace('/[^a-z0-9]+/', ' ', $value);
        $value = trim(preg_replace('/\s+/', ' ', $value));

        return [
            'normalized' => $value,
            'number' => $number,
            'is_complete' => $isComplete,
        ];
    }

    private function normalizeLessonTitle(string $value): string
    {
        $value = $this->normalizeText($value);
        $value = preg_replace('/\b(pdf|audio|video)\b/', ' ', $value);
        $value = preg_replace('/\b\d{1,3}\b/', ' ', $value);
        $value = preg_replace('/[^a-z0-9]+/', ' ', $value);
        $value = trim(preg_replace('/\s+/', ' ', $value));

        return $value;
    }

    private function findBestLessonMatch(array $audioInfo, array $lessons): ?array
    {
        $normalizedAudio = $audioInfo['normalized'];
        $audioNumber = $audioInfo['number'];
        $audioIsComplete = $audioInfo['is_complete'];
        $candidates = [];

        if ($audioIsComplete) {
            foreach ($lessons as $lesson) {
                if (in_array($lesson['normalized'], ['audio', 'audios'], true)) {
                    return $lesson;
                }
            }
        }

        foreach ($lessons as $lesson) {
            $normalizedLesson = $lesson['normalized'];
            if ($normalizedLesson === '') {
                continue;
            }

            if ($audioNumber !== null && !in_array($audioNumber, $lesson['numbers'], true)) {
                continue;
            }

            if ($audioIsComplete && !$lesson['is_complete']) {
                continue;
            }

            if (str_contains($normalizedLesson, $normalizedAudio) || str_contains($normalizedAudio, $normalizedLesson)) {
                $score = abs(strlen($normalizedLesson) - strlen($normalizedAudio));
                if ($audioNumber !== null) {
                    $score -= 5;
                }
                if ($audioIsComplete && $lesson['is_complete']) {
                    $score -= 5;
                }
                $candidates[] = ['score' => $score, 'lesson' => $lesson];
            }
        }

        if (empty($candidates)) {
            return null;
        }

        usort($candidates, function ($a, $b) {
            return $a['score'] <=> $b['score'];
        });

        return $candidates[0]['lesson'];
    }

    private function extractNumbers(string $value): array
    {
        if (!preg_match_all('/\b(\d{1,3})\b/', $value, $matches)) {
            return [];
        }

        return array_map('intval', $matches[1]);
    }

    private function isCompleteTitle(string $value): bool
    {
        $value = $this->normalizeText($value);
        return (bool) preg_match('/\b(completo|complete)\b/', $value);
    }

    private function normalizeText(string $value): string
    {
        $value = @iconv('UTF-8', 'ASCII//TRANSLIT', $value) ?: $value;
        return strtolower($value);
    }

    private function findAudiosLesson(array $lessons): ?array
    {
        foreach ($lessons as $lesson) {
            if (in_array($lesson['normalized'], ['audio', 'audios'], true)) {
                return $lesson;
            }
        }

        return null;
    }
}
