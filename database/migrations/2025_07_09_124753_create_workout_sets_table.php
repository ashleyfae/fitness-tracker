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
        Schema::create('workout_sets', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\WorkoutExercise::class)->constrained()->cascadeOnDelete();
            $table->float('weight_kg', 2)->default(0);
            $table->unsignedBigInteger('number_reps')->default(0);
            $table->datetime('completed_at')->nullable();
            $table->timestamps();

            $table->index(['weight_kg', 'completed_at']); // For PR calculations (ordering by weight)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workout_sets');
    }
};
