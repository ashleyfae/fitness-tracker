<?php

namespace App\DataTransferObjects;

use App\Models\Exercise;
use App\Models\WorkoutSet;
use Illuminate\Support\Collection;

/**
 * @property Collection<WorkoutSet> $actualSets
 * @property Collection<WorkoutSet>|null $previousSets
 */
readonly class WorkoutExerciseData
{
    public function __construct(
        public Exercise $exercise,
        public int $expectedSets,
        public int $restSeconds,
        public int $sort,
        public Collection $actualSets,  // Collection<WorkoutSet>
        public ?int $workoutExerciseId, // null if not yet created
        public bool $fromRoutine,       // true if from routine, false if added manually
        public ?Collection $previousSets = null, // Collection<WorkoutSet> from last workout, or null
    ) {}

    /**
     * Maximum number of sets to render in UI.
     * Takes the larger of expected sets (from routine) or actual sets completed.
     */
    public function maxSets(): int
    {
        return max($this->expectedSets, $this->actualSets->count());
    }

    /**
     * Check if this exercise has been started (has any completed sets).
     */
    public function isStarted(): bool
    {
        return $this->actualSets->isNotEmpty();
    }

    /**
     * Check if this exercise is completed (actual sets >= expected sets).
     */
    public function isCompleted(): bool
    {
        return $this->actualSets->count() >= $this->expectedSets;
    }
}
