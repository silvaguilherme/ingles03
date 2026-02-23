<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Module;
use App\Models\SubModule;
use Illuminate\Http\Request;

class SubModuleController extends Controller
{
    /**
     * Display the submodule
     */
    public function show(SubModule $subModule)
    {
        return view('submodules.show', [
            'subModule' => $subModule,
            'module' => $subModule->module,
            'course' => $subModule->module->course,
        ]);
    }

    /**
     * Show the form for creating a new submodule
     */
    public function create(Module $module)
    {
        return view('submodules.create', [
            'module' => $module,
            'course' => $module->course,
        ]);
    }

    /**
     * Store a newly created submodule in storage
     */
    public function store(Request $request, Module $module)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'required|integer|min:1',
        ]);

        $validated['module_id'] = $module->id;

        SubModule::create($validated);

        return redirect()
            ->route('courses.show', $module->course)
            ->with('success', 'Submódulo criado com sucesso!');
    }

    /**
     * Show the form for editing the submodule
     */
    public function edit(SubModule $subModule)
    {
        return view('submodules.edit', [
            'subModule' => $subModule,
            'module' => $subModule->module,
            'course' => $subModule->module->course,
        ]);
    }

    /**
     * Update the submodule in storage
     */
    public function update(Request $request, SubModule $subModule)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'required|integer|min:1',
        ]);

        $subModule->update($validated);

        return redirect()
            ->route('courses.show', $subModule->module->course)
            ->with('success', 'Submódulo atualizado com sucesso!');
    }

    /**
     * Remove the submodule from storage
     */
    public function destroy(SubModule $subModule)
    {
        $course = $subModule->module->course;
        $subModule->delete();

        return redirect()
            ->route('courses.show', $course)
            ->with('success', 'Submódulo deletado com sucesso!');
    }
}
