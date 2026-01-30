<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Progress;
use App\Services\OciObjectStorageService;
use Illuminate\Support\Facades\Auth;

class LessonController extends Controller
{
    public function show(Lesson $lesson, OciObjectStorageService $oci)
    {
        $videoUrl = $lesson->video_key ? $oci->generateTemporaryUrl($lesson->video_key, 15) : null;
        $pdfUrl   = $lesson->pdf_key ? $oci->generateTemporaryUrl($lesson->pdf_key, 15) : null;

        $progress = Progress::firstOrCreate(
            ['user_id' => Auth::id(), 'lesson_id' => $lesson->id],
            ['watched_seconds' => 0, 'percentage' => 0, 'completed' => false]
        );

        return view('lessons.show', compact('lesson','videoUrl','pdfUrl','progress'));
    }
}
