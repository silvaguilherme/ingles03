<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Module;
use App\Models\Progress;
use App\Services\OciObjectStorageService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

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

    public function create(Module $module)
    {
        return view('lessons.create', compact('module'));
    }

    public function store(Request $request, Module $module)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'sub_title' => 'nullable|string|max:255',
            'content_type' => 'required|in:video,pdf,quiz,text',
            'video_key' => 'nullable|string',
            'pdf_key' => 'nullable|string',
            'duration_seconds' => 'nullable|integer|min:0',
            'quiz_data' => 'nullable|json',
            'order' => 'required|integer|min:1',
        ]);

        $validated['module_id'] = $module->id;
        Lesson::create($validated);

        return redirect()->route('courses.show', $module->course)->with('success', 'Lição criada com sucesso!');
    }

    public function edit(Lesson $lesson)
    {
        return view('lessons.edit', compact('lesson'));
    }

    public function update(Request $request, Lesson $lesson)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'sub_title' => 'nullable|string|max:255',
            'content_type' => 'required|in:video,pdf,quiz,text',
            'video_key' => 'nullable|string',
            'pdf_key' => 'nullable|string',
            'duration_seconds' => 'nullable|integer|min:0',
            'quiz_data' => 'nullable|json',
            'order' => 'required|integer|min:1',
        ]);

        $lesson->update($validated);

        return redirect()->route('courses.show', $lesson->module->course)->with('success', 'Lição atualizada com sucesso!');
    }

    public function destroy(Lesson $lesson)
    {
        $module = $lesson->module;
        $lesson->delete();

        return redirect()->route('courses.show', $module->course)->with('success', 'Lição deletada com sucesso!');
    }
}
