<?php

namespace App\Services\Import;

class LogsParser
{
    /**
     * Parse logs string into array of sets
     *
     * Input: "22.5x5,22.5x8,22.5x8"
     * Output: [
     *   ['weight_kg' => 22.5, 'number_reps' => 5],
     *   ['weight_kg' => 22.5, 'number_reps' => 8],
     *   ['weight_kg' => 22.5, 'number_reps' => 8],
     * ]
     */
    public function parse(string $logs): array
    {
        if (empty($logs) || $logs === '0x0') {
            return [];
        }

        $sets = $this->splitIntoSets($logs);
        $parsed = [];

        foreach ($sets as $set) {
            $setData = $this->parseSet($set);
            if ($setData !== null) {
                $parsed[] = $setData;
            }
        }

        return $parsed;
    }

    /**
     * Split logs into individual set strings
     */
    private function splitIntoSets(string $logs): array
    {
        return array_filter(explode(',', $logs), fn ($set) => ! empty(trim($set)));
    }

    /**
     * Parse single set string (e.g., "22.5x8")
     */
    private function parseSet(string $set): ?array
    {
        $set = trim($set);

        // Check if time-based (3 parts separated by 'x')
        if ($this->isTimeBased($set)) {
            return $this->parseTimeBased($set);
        }

        // Standard format: weightxreps
        $parts = explode('x', $set);

        if (count($parts) !== 2) {
            return null;
        }

        return [
            'weight_kg' => (float) $parts[0],
            'number_reps' => (int) $parts[1],
        ];
    }

    /**
     * Check if set is time-based (e.g., "0x5x30")
     */
    private function isTimeBased(string $set): bool
    {
        return substr_count($set, 'x') > 1;
    }

    /**
     * Parse time-based set (special handling)
     *
     * Format: 0x5x30 = 5 minutes 30 seconds
     * We'll skip these for now as they don't fit the weight/reps model
     */
    private function parseTimeBased(string $set): ?array
    {
        // Skip time-based exercises for now
        return null;
    }
}
