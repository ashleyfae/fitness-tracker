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
        Schema::table('workout_sessions', function (Blueprint $table) {
            $table->dropForeign(['routine_id']);
            $table->foreignIdFor(\App\Models\Routine::class)->nullable()->change();
            $table->foreign('routine_id')->references('id')->on('routines')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workout_sessions', function (Blueprint $table) {
            $table->dropForeign(['routine_id']);
            $table->foreignIdFor(\App\Models\Routine::class)->nullable(false)->change();
            $table->foreign('routine_id')->references('id')->on('routines')->cascadeOnDelete();
        });
    }
};
