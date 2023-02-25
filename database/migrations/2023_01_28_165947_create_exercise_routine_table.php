<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exercise_routine', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Exercise::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\Routine::class)->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('number_sets')->default(3);
            $table->unsignedBigInteger('rest_seconds')->default(60);
            $table->unsignedBigInteger('sort')->default(1);

            $table->unique(['routine_id', 'exercise_id']);
            $table->index(['routine_id', 'sort']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exercise_routine');
    }
};
