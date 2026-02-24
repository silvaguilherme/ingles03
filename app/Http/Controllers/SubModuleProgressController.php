<?php

namespace App\Http\Controllers;

use App\Models\SubModule;
use App\Models\SubModuleProgress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class SubModuleProgressController extends Controller
{
    public function toggle(SubModule $subModule): RedirectResponse
    {
        $userId = Auth::id();

        $progress = SubModuleProgress::firstOrNew([
            'user_id' => $userId,
            'sub_module_id' => $subModule->id,
        ]);

        $progress->completed = !$progress->completed;
        $progress->save();

        return back()->with('success', $progress->completed ? 'Submodulo finalizado!' : 'Submodulo reaberto.');
    }
}
