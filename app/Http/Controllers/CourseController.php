<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Progress;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::with(['modules.lessons'])->get();
        $userId = Auth::id();
        $progressMap = Progress::where('user_id', $userId)->get()->keyBy('lesson_id');

        return view('courses.index', compact('courses', 'progressMap'));
    }

    public function show(Course $course)
    {
        $course->load(['modules.lessons']);
        $userId = Auth::id();
        $progressMap = Progress::where('user_id', $userId)
            ->whereIn('lesson_id', $course->lessons->pluck('id'))
            ->get()->keyBy('lesson_id');

        return view('courses.show', compact('course', 'progressMap'));
    }
}
