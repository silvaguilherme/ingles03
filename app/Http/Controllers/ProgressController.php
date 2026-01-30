<?php

namespace App\Http\Controllers;

use App\Models\Progress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgressController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'lesson_id'    => ['required','integer','exists:lessons,id'],
            'current_time' => ['nullable','numeric','min:0'],
            'duration'     => ['nullable','numeric','min:1'],
            'completed'    => ['nullable','boolean'],
        ]);

        $percentage = 0;
        if (!empty($data['current_time']) && !empty($data['duration'])) {
            $percentage = (int) round(($data['current_time'] / $data['duration']) * 100);
            $percentage = max(0, min(100, $percentage));
        }

        $completed = $data['completed'] ?? ($percentage >= 95);

        $progress = Progress::updateOrCreate(
            ['user_id' => Auth::id(), 'lesson_id' => $data['lesson_id']],
            [
                'watched_seconds' => (int)($data['current_time'] ?? 0),
                'percentage'      => $percentage,
                'completed'       => $completed,
            ]
        );

        return response()->json(['ok' => true, 'progress' => $progress]);
    }
}
