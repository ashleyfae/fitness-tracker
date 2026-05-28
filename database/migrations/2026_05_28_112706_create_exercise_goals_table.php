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
        Schema::create('exercise_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exercise_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('sort')->default(0);
            $table->unsignedTinyInteger('target_sets');
            $table->decimal('target_weight_kg', 6, 2);
            $table->unsignedSmallInteger('target_reps');
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('completed_in_workout_session_id')
                ->nullable()
                ->constrained('workout_sessions')
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercise_goals');
    }
};
