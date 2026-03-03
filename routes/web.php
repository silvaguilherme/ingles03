<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\SubModuleController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\ModuleProgressController;
use App\Http\Controllers\SubModuleProgressController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AnkiController;
use App\Http\Controllers\AnkiDeckController;
use App\Http\Controllers\AnkiImportController;
use App\Http\Controllers\AnkiDebugController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\ImportAllController;

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
    Route::get('/submodules/{subModule}', [SubModuleController::class, 'show'])->name('submodules.show');
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

    // Finalizar modulo/submodulo
    Route::post('/modules/{module}/complete', [ModuleProgressController::class, 'toggle'])->name('modules.complete');
    Route::post('/submodules/{subModule}/complete', [SubModuleProgressController::class, 'toggle'])->name('submodules.complete');

    // Test
    Route::get('/test', [TestController::class, 'test'])->name('test');

    // Import All
    Route::get('/admin/import-all', [ImportAllController::class, 'index'])->name('import-all.index');
    Route::post('/admin/import-all', [ImportAllController::class, 'importAll'])->name('import-all.execute');

    // Anki
    Route::get('/anki', [AnkiController::class, 'index'])->name('anki.index');
    Route::get('/anki/import', [AnkiImportController::class, 'index'])->name('anki.import-page');
    Route::post('/anki/import', [AnkiImportController::class, 'import'])->name('anki.import');
    Route::post('/anki/import-csv', [AnkiImportController::class, 'importCsv'])->name('anki.import-csv');
    Route::post('/anki/preview-csv', [AnkiImportController::class, 'previewCsv'])->name('anki.preview-csv');
    Route::get('/anki/debug/status', [AnkiDebugController::class, 'status'])->name('anki.status');
    Route::post('/anki/debug/reassociate', [AnkiDebugController::class, 'reassociate'])->name('anki.reassociate');
    Route::get('/anki/{deck}/study', [AnkiController::class, 'study'])->name('anki.study');
    Route::post('/anki/{deck}/record-answer', [AnkiController::class, 'recordAnswer'])->name('anki.record-answer');
    Route::get('/anki/stats', [AnkiController::class, 'stats'])->name('anki.stats');

    // Anki Decks
    Route::get('/submodules/{subModule}/anki-decks/create', [AnkiDeckController::class, 'create'])->name('anki-decks.create');
    Route::post('/submodules/{subModule}/anki-decks', [AnkiDeckController::class, 'store'])->name('anki-decks.store');
    Route::delete('/anki-decks/{deck}', [AnkiDeckController::class, 'destroy'])->name('anki-decks.destroy');

    // Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
