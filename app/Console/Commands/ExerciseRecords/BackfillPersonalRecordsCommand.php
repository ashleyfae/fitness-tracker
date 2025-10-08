<?php

namespace App\Console\Commands\ExerciseRecords;

use App\Models\User;
use App\Services\PersonalRecordService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillPersonalRecordsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exercise-records:backfill {userId?} {--dry-run : Preview changes without committing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill personal records from existing workout sets data';

    /**
     * Execute the console command.
     */
    public function handle(PersonalRecordService $prService)
    {
        $userId = $this->argument('userId');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('[DRY RUN MODE] No changes will be committed');
            $this->newLine();
        }

        // Get user(s) to process
        $users = $userId
            ? User::where('id', $userId)->get()
            : User::all();

        if ($users->isEmpty()) {
            $this->error('No users found');
            return 1;
        }

        $totalRecords = 0;

        foreach ($users as $user) {
            $this->info("Processing user: {$user->name} (ID: {$user->id})");

            if ($dryRun) {
                // In dry run, just count what would be updated
                $count = $this->previewBackfill($user);
            } else {
                $count = $prService->backfillAllRecords($user);
            }

            $this->line("✓ Processed {$count} exercises");
            $totalRecords += $count;
        }

        $this->newLine();
        $this->info("Total exercise records processed: {$totalRecords}");

        if ($dryRun) {
            $this->warn('✓ Dry run complete - no changes committed');
        } else {
            $this->info('✓ Backfill complete!');
        }

        return 0;
    }

    /**
     * Preview what would be updated in dry-run mode
     */
    protected function previewBackfill(User $user): int
    {
        return DB::table('workout_sets')
            ->join('workout_exercises', 'workout_sets.workout_exercise_id', '=', 'workout_exercises.id')
            ->join('workout_sessions', 'workout_exercises.workout_session_id', '=', 'workout_sessions.id')
            ->where('workout_sessions.user_id', $user->id)
            ->whereNotNull('workout_sessions.ended_at')
            ->select('workout_exercises.exercise_id')
            ->distinct()
            ->count();
    }
}
