<?php

namespace App\Console\Commands\Exercises;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MergeExercisesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exercises:merge {keepId} {mergeId} {--dry-run : Preview changes without committing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Merges two exercises into one, updating all references';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $keepId = $this->argument('keepId');
        $mergeId = $this->argument('mergeId');
        $dryRun = $this->option('dry-run');

        // Validate input
        if ($keepId === $mergeId) {
            $this->error('Keep ID and Merge ID cannot be the same');
            return 1;
        }

        // Fetch exercises
        $keepExercise = DB::table('exercises')->where('id', $keepId)->first();
        $mergeExercise = DB::table('exercises')->where('id', $mergeId)->first();

        if (!$keepExercise) {
            $this->error("Exercise with ID {$keepId} not found");
            return 1;
        }

        if (!$mergeExercise) {
            $this->error("Exercise with ID {$mergeId} not found");
            return 1;
        }

        // Check same user
        if ($keepExercise->user_id !== $mergeExercise->user_id) {
            $this->error('Both exercises must belong to the same user');
            return 1;
        }

        // Show merge preview
        $this->info('Merging exercises...');
        $this->line("  <fg=green>Keep:</> \"{$keepExercise->name}\" (ID: {$keepId})");
        $this->line("  <fg=yellow>Merge:</> \"{$mergeExercise->name}\" (ID: {$mergeId})");
        $this->newLine();

        // Get usage counts
        $keepRoutineCount = DB::table('exercise_routine')->where('exercise_id', $keepId)->count();
        $mergeRoutineCount = DB::table('exercise_routine')->where('exercise_id', $mergeId)->count();
        $keepWorkoutCount = DB::table('workout_exercises')->where('exercise_id', $keepId)->count();
        $mergeWorkoutCount = DB::table('workout_exercises')->where('exercise_id', $mergeId)->count();

        $this->info("Keep exercise: {$keepRoutineCount} routines, {$keepWorkoutCount} workout uses");
        $this->info("Merge exercise: {$mergeRoutineCount} routines, {$mergeWorkoutCount} workout uses");
        $this->newLine();

        // Confirm
        if (!$dryRun && !$this->confirm('Proceed with merge?', false)) {
            $this->info('Merge cancelled');
            return 0;
        }

        // Perform merge
        try {
            if ($dryRun) {
                $this->warn('[DRY RUN] No changes will be committed');
                $this->newLine();
            }

            $stats = $this->performMerge($keepId, $mergeId, $dryRun);

            $this->newLine();
            $this->info('Updated:');
            foreach ($stats as $table => $count) {
                $this->line("  - {$table}: {$count} rows updated");
            }

            if (!$dryRun) {
                $this->newLine();
                $this->info("Deleted exercise ID: {$mergeId}");
                $this->info('✓ Merge complete!');
            } else {
                $this->newLine();
                $this->warn('✓ Dry run complete - no changes committed');
            }

            return 0;
        } catch (\Exception $e) {
            $this->error('Merge failed: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Perform the actual merge operation
     */
    protected function performMerge(int $keepId, int $mergeId, bool $dryRun): array
    {
        $stats = [];

        $callback = function () use ($keepId, $mergeId, &$stats) {
            // 1. Update exercise_routine pivot
            // First, update rows that don't conflict
            $updated = DB::table('exercise_routine')
                ->where('exercise_id', $mergeId)
                ->whereNotExists(function ($query) use ($keepId) {
                    $query->select(DB::raw(1))
                        ->from('exercise_routine as er2')
                        ->whereColumn('er2.routine_id', 'exercise_routine.routine_id')
                        ->where('er2.exercise_id', $keepId);
                })
                ->update(['exercise_id' => $keepId]);

            $stats['exercise_routine_updated'] = $updated;

            // Delete duplicates (where routine already has keepId)
            $deleted = DB::table('exercise_routine')
                ->where('exercise_id', $mergeId)
                ->delete();

            $stats['exercise_routine_duplicates_removed'] = $deleted;

            // 2. Update workout_exercises
            $updated = DB::table('workout_exercises')
                ->where('exercise_id', $mergeId)
                ->update(['exercise_id' => $keepId]);

            $stats['workout_exercises'] = $updated;

            // 3. Delete the merged exercise
            DB::table('exercises')->where('id', $mergeId)->delete();
        };

        if ($dryRun) {
            // Run in transaction but rollback
            DB::beginTransaction();
            try {
                $callback();
            } finally {
                DB::rollBack();
            }
        } else {
            // Actually commit
            DB::transaction($callback);
        }

        return $stats;
    }
}
