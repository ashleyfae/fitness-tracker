<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('workout_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\WorkoutSession::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\Exercise::class)->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('number_sets')->default(3);
            $table->unsignedBigInteger('rest_seconds')->default(60);
            $table->unsignedBigInteger('sort')->default(1);
            $table->timestamps();

            $table->unique(['workout_session_id', 'exercise_id']);
            $table->index(['workout_session_id', 'sort']);
            $table->index('exercise_id'); // For PR calculations (filtering by exercise)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workout_exercises');
    }
};
