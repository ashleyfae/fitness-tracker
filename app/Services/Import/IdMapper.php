<?php

namespace App\Services\Import;

use Exception;

class IdMapper
{
    private array $exerciseNameMap = []; // old_exercise_id => exercise_name

    private array $routineMap = [];

    private array $sessionMap = [];

    /**
     * Map old exercise ID to exercise name
     */
    public function mapExerciseName(int $oldId, string $exerciseName): void
    {
        $this->exerciseNameMap[$oldId] = $exerciseName;
    }

    /**
     * Get exercise name by old ID
     */
    public function getExerciseName(int $oldId): string
    {
        if (! isset($this->exerciseNameMap[$oldId])) {
            throw new Exception("Exercise name mapping not found for old ID: {$oldId}");
        }

        return $this->exerciseNameMap[$oldId];
    }

    /**
     * Check if exercise name mapping exists
     */
    public function hasExerciseName(int $oldId): bool
    {
        return isset($this->exerciseNameMap[$oldId]);
    }

    /**
     * Map old routine ID to new routine ID
     */
    public function mapRoutine(int $oldId, int $newId): void
    {
        $this->routineMap[$oldId] = $newId;
    }

    /**
     * Get new routine ID by old ID
     */
    public function getRoutineId(int $oldId): int
    {
        if (! isset($this->routineMap[$oldId])) {
            throw new Exception("Routine mapping not found for old ID: {$oldId}");
        }

        return $this->routineMap[$oldId];
    }

    /**
     * Check if routine mapping exists
     */
    public function hasRoutine(int $oldId): bool
    {
        return isset($this->routineMap[$oldId]);
    }

    /**
     * Map old session ID to new session ID
     */
    public function mapSession(int $oldId, int $newId): void
    {
        $this->sessionMap[$oldId] = $newId;
    }

    /**
     * Get new session ID by old ID
     */
    public function getSessionId(int $oldId): int
    {
        if (! isset($this->sessionMap[$oldId])) {
            throw new Exception("Session mapping not found for old ID: {$oldId}");
        }

        return $this->sessionMap[$oldId];
    }

    /**
     * Check if session mapping exists
     */
    public function hasSession(int $oldId): bool
    {
        return isset($this->sessionMap[$oldId]);
    }

    /**
     * Clear all mappings
     */
    public function clear(): void
    {
        $this->exerciseNameMap = [];
        $this->routineMap = [];
        $this->sessionMap = [];
    }

    /**
     * Get counts of all mappings
     */
    public function getCounts(): array
    {
        return [
            'exercise_names' => count($this->exerciseNameMap),
            'routines' => count($this->routineMap),
            'sessions' => count($this->sessionMap),
        ];
    }
}
