<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\SubModuleController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\ProfileController;

Route::redirect('/', '/courses');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route('courses.index');
    })->name('dashboard');

    // Cursos
    Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/create', [CourseController::class, 'create'])->name('courses.create');
    Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');
    Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');
    Route::get('/courses/{course}/edit', [CourseController::class, 'edit'])->name('courses.edit');
    Route::patch('/courses/{course}', [CourseController::class, 'update'])->name('courses.update');
    Route::delete('/courses/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');

    // Módulos
    Route::get('/courses/{course}/modules/create', [ModuleController::class, 'create'])->name('modules.create');
    Route::post('/courses/{course}/modules', [ModuleController::class, 'store'])->name('modules.store');
    Route::get('/modules/{module}/edit', [ModuleController::class, 'edit'])->name('modules.edit');
    Route::patch('/modules/{module}', [ModuleController::class, 'update'])->name('modules.update');
    Route::delete('/modules/{module}', [ModuleController::class, 'destroy'])->name('modules.destroy');

    // Sub-Módulos
    Route::get('/modules/{module}/submodules/create', [SubModuleController::class, 'create'])->name('submodules.create');
    Route::post('/modules/{module}/submodules', [SubModuleController::class, 'store'])->name('submodules.store');
    Route::get('/submodules/{subModule}/edit', [SubModuleController::class, 'edit'])->name('submodules.edit');
    Route::patch('/submodules/{subModule}', [SubModuleController::class, 'update'])->name('submodules.update');
    Route::delete('/submodules/{subModule}', [SubModuleController::class, 'destroy'])->name('submodules.destroy');

    // Lições
    Route::get('/submodules/{subModule}/lessons/create', [LessonController::class, 'create'])->name('lessons.create');
    Route::post('/submodules/{subModule}/lessons', [LessonController::class, 'store'])->name('lessons.store');
    Route::get('/lessons/{lesson}', [LessonController::class, 'show'])->name('lessons.show');
    Route::get('/lessons/{lesson}/edit', [LessonController::class, 'edit'])->name('lessons.edit');
    Route::patch('/lessons/{lesson}', [LessonController::class, 'update'])->name('lessons.update');
    Route::delete('/lessons/{lesson}', [LessonController::class, 'destroy'])->name('lessons.destroy');

    // Progresso
    Route::post('/progress', [ProgressController::class, 'store'])->name('progress.store');

    // Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
