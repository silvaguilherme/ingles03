<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\ModuleProgress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ModuleProgressController extends Controller
{
    public function toggle(Module $module): RedirectResponse
    {
        $userId = Auth::id();

        $progress = ModuleProgress::firstOrNew([
            'user_id' => $userId,
            'module_id' => $module->id,
        ]);

        $progress->completed = !$progress->completed;
        $progress->save();

        return back()->with('success', $progress->completed ? 'Modulo finalizado!' : 'Modulo reaberto.');
    }
}
