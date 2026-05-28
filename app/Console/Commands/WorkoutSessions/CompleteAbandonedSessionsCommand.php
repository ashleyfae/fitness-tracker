<?php

namespace App\Console\Commands\WorkoutSessions;

use App\Models\WorkoutSession;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CompleteAbandonedSessionsCommand extends Command
{
    protected $signature = 'workout-sessions:complete-abandoned {--dry-run : Preview changes without committing}';

    protected $description = 'Complete workout sessions that were started but never finished, using the timestamp of the last logged set';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('[DRY RUN MODE] No changes will be committed');
            $this->newLine();
        }

        /** @var Collection<WorkoutSession> $sessions */
        $sessions = WorkoutSession::whereNull('ended_at')
            ->whereDate('started_at', '<=', now()->subDay())
            ->whereHas('exercises.sets')
            ->get();

        if ($sessions->isEmpty()) {
            $this->info('No abandoned sessions found.');
            return 0;
        }

        $completed = 0;

        foreach ($sessions as $session) {
            $lastSetAt = DB::table('workout_sets')
                ->join('workout_exercises', 'workout_sets.workout_exercise_id', '=', 'workout_exercises.id')
                ->where('workout_exercises.workout_session_id', $session->id)
                ->max('workout_sets.completed_at');

            if (! $lastSetAt) {
                continue;
            }

            $this->line("Session ID {$session->id} (user {$session->user_id}, started {$session->started_at}): ending at {$lastSetAt}");

            if (! $dryRun) {
                $session->update(['ended_at' => $lastSetAt]);
            }

            $completed++;
        }

        $this->newLine();

        if ($dryRun) {
            $this->warn("Found {$completed} abandoned session(s) — no changes committed.");
        } else {
            $this->info("Completed {$completed} abandoned session(s).");
        }

        return 0;
    }
}
