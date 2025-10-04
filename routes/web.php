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
    Route::resource('routines', \App\Http\Controllers\RoutineController::class);
    Route::resource('workouts', \App\Http\Controllers\WorkoutSessionController::class);
});
