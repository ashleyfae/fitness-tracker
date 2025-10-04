<?php

namespace App\Services\Import;

use Exception;
use Illuminate\Support\Facades\File;

class CsvSplitter
{
    private const SECTION_MARKERS = [
        'ROUTINES' => '### ROUTINES',
        'WORKOUT_SESSIONS' => '### WORKOUT SESSIONS',
        'EXERCISE_LOGS' => '### EXERCISE LOGS',
        'EXERCISE_RECORDS' => '### EXERCISE RECORDS',
        'CUSTOM_EXERCISES' => '### CUSTOM EXERCISES',
    ];

    private const OUTPUT_FILENAMES = [
        'CUSTOM_EXERCISES' => 'custom-exercises.csv',
        'WORKOUT_SESSIONS' => 'workout-sessions.csv',
        'EXERCISE_LOGS' => 'exercise-logs.csv',
        'EXERCISE_RECORDS' => 'exercise-records.csv',
    ];

    private array $sourceLines = [];

    public function __construct(
        private string $sourcePath,
        private string $outputDirectory
    ) {
        if (! File::exists($sourcePath)) {
            throw new Exception("Source file not found: {$sourcePath}");
        }

        if (! File::exists($outputDirectory)) {
            File::makeDirectory($outputDirectory, 0755, true);
        }
    }

    /**
     * Split the monolithic CSV into separate files
     *
     * @return array Map of section name => output file path
     */
    public function split(): array
    {
        $this->sourceLines = file($this->sourcePath, FILE_IGNORE_NEW_LINES);
        $sections = $this->identifySections();
        $results = [];

        foreach ($sections as $sectionName => $bounds) {
            if ($sectionName === 'ROUTINES') {
                $routineResults = $this->extractRoutineSection($bounds['start'], $bounds['end']);
                $results = array_merge($results, $routineResults);
            } else {
                $results[$sectionName] = $this->extractSection(
                    $sectionName,
                    $bounds['start'],
                    $bounds['end']
                );
            }
        }

        return $results;
    }

    /**
     * Read source file and identify sections
     *
     * @return array Map of section name => [start => line, end => line]
     */
    private function identifySections(): array
    {
        $sections = [];
        $sectionKeys = array_keys(self::SECTION_MARKERS);

        foreach ($this->sourceLines as $lineNum => $line) {
            foreach (self::SECTION_MARKERS as $key => $marker) {
                if (str_contains($line, $marker)) {
                    $sections[$key] = ['start' => $lineNum];
                }
            }
        }

        // Calculate end lines
        $sectionKeys = array_keys($sections);
        for ($i = 0; $i < count($sectionKeys); $i++) {
            $currentKey = $sectionKeys[$i];
            $nextKey = $sectionKeys[$i + 1] ?? null;

            if ($nextKey) {
                $sections[$currentKey]['end'] = $sections[$nextKey]['start'] - 1;
            } else {
                $sections[$currentKey]['end'] = count($this->sourceLines) - 1;
            }
        }

        return $sections;
    }

    /**
     * Extract specific section from source file
     */
    private function extractSection(string $sectionName, int $startLine, int $endLine): string
    {
        $headers = $this->findSectionHeaders($startLine, $endLine);

        if (empty($headers)) {
            return '';
        }

        $headerLine = $headers['line'];
        $headerColumns = $headers['columns'];

        // Collect data rows (skip blank lines)
        $rows = [];
        for ($i = $headerLine + 1; $i <= $endLine; $i++) {
            $line = trim($this->sourceLines[$i]);

            // Stop at next section or blank separator
            if (empty($line) || str_starts_with($line, '###')) {
                break;
            }

            $rows[] = $this->sourceLines[$i];
        }

        return $this->writeCsv(
            self::OUTPUT_FILENAMES[$sectionName],
            $headerColumns,
            $rows
        );
    }

    /**
     * Parse routine section (has 3 tiers)
     */
    private function extractRoutineSection(int $startLine, int $endLine): array
    {
        $results = [];
        $routineDays = [];
        $routineExercises = [];

        $currentLine = $startLine;

        // Skip tier 1 (top-level routine - we don't care)
        // Find tier 2 and tier 3 blocks

        while ($currentLine <= $endLine) {
            $line = trim($this->sourceLines[$currentLine]);

            // Look for tier 2 headers (routine days)
            if (str_contains($line, 'package,_id,name,day,dayIndex')) {
                $tier2Headers = str_getcsv($line);
                $currentLine++;

                // Collect tier 2 data rows
                while ($currentLine <= $endLine) {
                    $dataLine = trim($this->sourceLines[$currentLine]);

                    if (empty($dataLine) || str_starts_with($dataLine, '###')) {
                        break;
                    }

                    // Check if this is a tier 3 header
                    if (str_contains($dataLine, 'belongSys,superset,_id,exercise_id')) {
                        break;
                    }

                    // Check if this is tier 1 header (skip it)
                    if (str_contains($dataLine, 'row_id,USERID,TIMESTAMP,_id,name,difficulty')) {
                        $currentLine++;

                        continue;
                    }

                    $routineDays[] = $dataLine;
                    $currentLine++;
                }
            }

            // Look for tier 3 headers (exercises in routine)
            if (str_contains($line, 'belongSys,superset,_id,exercise_id')) {
                $tier3Headers = str_getcsv($line);
                $currentLine++;

                // Collect tier 3 data rows
                while ($currentLine <= $endLine) {
                    $dataLine = trim($this->sourceLines[$currentLine]);

                    if (empty($dataLine) || str_starts_with($dataLine, '###')) {
                        break;
                    }

                    // Check if we hit another section (tier 1 or tier 2)
                    if (str_contains($dataLine, 'package,_id,name,day') ||
                        str_contains($dataLine, 'row_id,USERID,TIMESTAMP,_id,name,difficulty')) {
                        break;
                    }

                    $routineExercises[] = $dataLine;
                    $currentLine++;
                }
            }

            $currentLine++;
        }

        // Write tier 2 (routine days)
        if (! empty($routineDays)) {
            $results['ROUTINE_DAYS'] = $this->writeCsv(
                'routine-days.csv',
                str_getcsv('row_id,USERID,TIMESTAMP,package,_id,name,day,dayIndex,interval_mode,rest_day,week,sort_order'),
                $routineDays
            );
        }

        // Write tier 3 (routine exercises)
        if (! empty($routineExercises)) {
            $results['ROUTINE_EXERCISES'] = $this->writeCsv(
                'routine-exercises.csv',
                str_getcsv('row_id,USERID,TIMESTAMP,belongSys,superset,_id,exercise_id,belongplan,exercisename,setcount,timer,logs,bodypart,mysort,targetrep,setdone,setdonetime,interval_time,interval_unit,rest_time_enabled,interval_time_enabled'),
                $routineExercises
            );
        }

        return $results;
    }

    /**
     * Find headers within a section
     */
    private function findSectionHeaders(int $startLine, int $endLine): array
    {
        // Skip the section marker line and blank lines
        for ($i = $startLine + 1; $i <= $endLine; $i++) {
            $line = trim($this->sourceLines[$i]);

            // Skip blank lines and separator lines
            if (empty($line) || str_starts_with($line, '###') || str_starts_with($line, '##')) {
                continue;
            }

            // First non-blank line should be headers
            if (str_contains($line, ',')) {
                return [
                    'line' => $i,
                    'columns' => str_getcsv($line),
                ];
            }
        }

        return [];
    }

    /**
     * Write CSV data to output file
     */
    private function writeCsv(string $filename, array $headers, array $rows): string
    {
        $outputPath = $this->outputDirectory.'/'.$filename;
        $handle = fopen($outputPath, 'w');

        if (! $handle) {
            throw new Exception("Unable to write to: {$outputPath}");
        }

        // Write headers
        fputcsv($handle, $headers);

        // Write data rows
        foreach ($rows as $row) {
            $parsed = str_getcsv($row);
            fputcsv($handle, $parsed);
        }

        fclose($handle);

        return $outputPath;
    }
}
