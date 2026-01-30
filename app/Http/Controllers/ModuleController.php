<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Module;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function create(Course $course)
    {
        return view('modules.create', compact('course'));
    }

    public function store(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'order' => 'required|integer|min:1',
        ]);

        $validated['course_id'] = $course->id;
        Module::create($validated);

        return redirect()->route('courses.show', $course)->with('success', 'Módulo criado com sucesso!');
    }

    public function edit(Module $module)
    {
        return view('modules.edit', compact('module'));
    }

    public function update(Request $request, Module $module)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'order' => 'required|integer|min:1',
        ]);

        $module->update($validated);

        return redirect()->route('courses.show', $module->course)->with('success', 'Módulo atualizado com sucesso!');
    }

    public function destroy(Module $module)
    {
        $course = $module->course;
        $module->delete();

        return redirect()->route('courses.show', $course)->with('success', 'Módulo deletado com sucesso!');
    }
}
