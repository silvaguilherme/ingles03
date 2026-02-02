<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Progress;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::with(['modules.subModules.lessons'])->get();
        $userId = Auth::id();
        $progressMap = Progress::where('user_id', $userId)->get()->keyBy('lesson_id');

        return view('courses.index', compact('courses', 'progressMap'));
    }

    public function show(Course $course)
    {
        $course->load(['modules.subModules.lessons']);
        $userId = Auth::id();
        // Get all lessons through submodules
        $allLessons = collect();
        foreach ($course->modules as $module) {
            foreach ($module->subModules as $subModule) {
                $allLessons = $allLessons->merge($subModule->lessons);
            }
        }
        $progressMap = Progress::where('user_id', $userId)
            ->whereIn('lesson_id', $allLessons->pluck('id'))
            ->get()->keyBy('lesson_id');

        return view('courses.show', compact('course', 'progressMap'));
    }

    // Métodos CRUD para Admin/Professor
    public function create()
    {
        return view('courses.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $course = Course::create($validated);

        return redirect()->route('courses.show', $course)->with('success', 'Curso criado com sucesso!');
    }

    public function edit(Course $course)
    {
        return view('courses.edit', compact('course'));
    }

    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $course->update($validated);

        return redirect()->route('courses.show', $course)->with('success', 'Curso atualizado com sucesso!');
    }

    public function destroy(Course $course)
    {
        $course->delete();
        return redirect()->route('courses.index')->with('success', 'Curso deletado com sucesso!');
    }
}
