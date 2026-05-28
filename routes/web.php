<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', \App\Http\Controllers\HomepageController::class)->name('home');

Route::middleware(['auth'])->group(function () {
    Route::resource('exercises', \App\Http\Controllers\ExerciseController::class);
    Route::prefix('exercises/{exercise}/goals')->name('exercises.goals.')->scopeBindings()->group(function () {
        Route::post('/', [\App\Http\Controllers\ExerciseGoalController::class, 'store'])->name('store');
        Route::patch('/{goal}', [\App\Http\Controllers\ExerciseGoalController::class, 'update'])->name('update');
        Route::delete('/{goal}', [\App\Http\Controllers\ExerciseGoalController::class, 'destroy'])->name('destroy');
        Route::patch('/{goal}/reorder', [\App\Http\Controllers\ExerciseGoalController::class, 'reorder'])->name('reorder');
    });
    Route::resource('routines', \App\Http\Controllers\RoutineController::class);
    Route::resource('workouts', \App\Http\Controllers\WorkoutSessionController::class)
        ->parameters(['workouts' => 'workoutSession']);

    // Routes for AJAX interactions during workout
    // These support both JSON (AJAX) and HTML responses via content negotiation
    Route::prefix('workouts/{workoutSession}')->group(function () {
        // Create workout exercise and first set atomically
        Route::post('exercises', [\App\Http\Controllers\WorkoutExerciseController::class, 'store'])
            ->name('workouts.exercises.store');

        // Add a set to an existing workout exercise
        Route::post('exercises/{workoutExercise}/sets', [\App\Http\Controllers\WorkoutSetController::class, 'store'])
            ->name('workouts.exercises.sets.store');

        // Update an existing set
        Route::patch('exercises/{workoutExercise}/sets/{workoutSet}', [\App\Http\Controllers\WorkoutSetController::class, 'update'])
            ->name('workouts.exercises.sets.update');

        // Delete a set
        Route::delete('exercises/{workoutExercise}/sets/{workoutSet}', [\App\Http\Controllers\WorkoutSetController::class, 'destroy'])
            ->name('workouts.exercises.sets.destroy');

        // Complete the workout (sets ended_at timestamp)
        Route::post('complete', [\App\Http\Controllers\WorkoutSessionController::class, 'complete'])
            ->name('workouts.complete');
    });
});
