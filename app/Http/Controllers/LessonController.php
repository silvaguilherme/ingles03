<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Module;
use App\Models\SubModule;
use App\Models\Progress;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    public function show(Lesson $lesson)
    {
        // Generate storage URLs for video and PDF files
        // Remove leading slash from paths if present
        $videoUrl = $lesson->video_key ? asset('storage/' . ltrim($lesson->video_key, '/')) : null;
        $pdfUrl   = $lesson->pdf_key ? asset('storage/' . ltrim($lesson->pdf_key, '/')) : null;
        $audioUrl = $lesson->audio_key ? asset('storage/' . ltrim($lesson->audio_key, '/')) : null;

        // Prepara lista de áudios (pode ser array vazio)
        $audioList = $lesson->audio_list ?? [];

        $progress = Progress::firstOrCreate(
            ['user_id' => Auth::id(), 'lesson_id' => $lesson->id],
            ['watched_seconds' => 0, 'percentage' => 0, 'completed' => false]
        );

        return view('lessons.show', compact('lesson','videoUrl','pdfUrl','audioUrl','progress','audioList'));
    }

    public function create(SubModule $subModule)
    {
        return view('lessons.create', [
            'subModule' => $subModule,
            'module' => $subModule->module,
            'course' => $subModule->module->course,
        ]);
    }

    public function store(Request $request, SubModule $subModule)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'sub_title' => 'nullable|string|max:255',
            'content_type' => 'required|in:video,pdf,quiz,text,audio,anki',
            'video_key' => 'nullable|string',
            'pdf_key' => 'nullable|string',
            'audio_key' => 'nullable|string',
            'duration_seconds' => 'nullable|integer|min:0',
            'quiz_data' => 'nullable|json',
            'order' => 'required|integer|min:1',
        ]);

        $validated['sub_module_id'] = $subModule->id;
        Lesson::create($validated);

        return redirect()
            ->route('courses.show', $subModule->module->course)
            ->with('success', 'Lição criada com sucesso!');
    }

    public function edit(Lesson $lesson)
    {
        return view('lessons.edit', [
            'lesson' => $lesson,
            'subModule' => $lesson->subModule,
            'module' => $lesson->subModule->module,
            'course' => $lesson->subModule->module->course,
        ]);
    }

    public function update(Request $request, Lesson $lesson)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'sub_title' => 'nullable|string|max:255',
            'content_type' => 'required|in:video,pdf,quiz,text,audio,anki',
            'video_key' => 'nullable|string',
            'pdf_key' => 'nullable|string',
            'audio_key' => 'nullable|string',
            'duration_seconds' => 'nullable|integer|min:0',
            'quiz_data' => 'nullable|json',
            'order' => 'required|integer|min:1',
        ]);

        $lesson->update($validated);

        return redirect()
            ->route('courses.show', $lesson->subModule->module->course)
            ->with('success', 'Lição atualizada com sucesso!');
    }

    public function destroy(Lesson $lesson)
    {
        $course = $lesson->subModule->module->course;
        $lesson->delete();

        return redirect()
            ->route('courses.show', $course)
            ->with('success', 'Lição deletada com sucesso!');
    }
}

