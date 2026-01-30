<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\ProgressController;

Route::redirect('/', '/courses');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route('courses.index');
    })->name('dashboard');

    Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');

    Route::get('/lessons/{lesson}', [LessonController::class, 'show'])->name('lessons.show');
    Route::post('/progress', [ProgressController::class, 'store'])->name('progress.store');
});

require __DIR__.'/auth.php';
