# Data Import Mapping

This document outlines the strategy for importing historical workout data from the old fitness tracking app (JEFit export CSV) into the new Laravel application.

## Overview

- **Source:** Single CSV export file (`NoseGraze_YYYYMMDD.csv`) containing multiple sections
- **Target:** Laravel application models
- **User:** All data belongs to a single user (import will assign to user ID 1 or configurable)
- **Record Count:** ~1,171 workout sessions, ~4,578 exercise logs, ~47 personal records, 7 custom exercises

## Import Strategy

### Phase 1: CSV Splitting (CLI Command)

Create a CLI command to split the monolithic CSV into separate files:

```bash
sail artisan import:split-csv resources/imports/NoseGraze_YYYYMMDD.csv
```

**Output files:**
- `custom-exercises.csv`
- `routine-days.csv` (tier 2 of routine hierarchy)
- `routine-exercises.csv` (tier 3 of routine hierarchy)
- `workout-sessions.csv`
- `exercise-logs.csv`
- `exercise-records.csv`

### Phase 2: Data Import (CLI Command)

Import data in dependency order:

```bash
sail artisan import:data
```

**Import Order:**
1. `custom-exercises.csv` → Exercise model
2. `routine-days.csv` → Routine model
3. `routine-exercises.csv` → exercise_routine pivot
4. `workout-sessions.csv` → WorkoutSession model
5. `exercise-logs.csv` → WorkoutExercise + WorkoutSet models
6. `exercise-records.csv` → ExerciseRecord model

## Routine Hierarchy (3-Tier Structure)

The old app has a 3-tier routine structure. We only care about tiers 2 and 3:

### Tier 1: Top-Level Routine (❌ SKIP)
```csv
row_id,USERID,TIMESTAMP,_id,name,difficulty,focus,dayaweek,description,daytype,tags,rdb_id,bannerCode
9158878,3773197,"2019-08-26 10:29:39",1,PPL,1,1,5,,1," ",22459,
```
**Action:** Ignore - we don't need this grouping level

### Tier 2: Routine Day (✅ IMPORT as Routine)
```csv
row_id,USERID,TIMESTAMP,package,_id,name,day,dayIndex,interval_mode,rest_day,week,sort_order
46743798,3773197,"2025-01-29 10:33:43",1,21,"PPL - Pull ",8,2,0,0,0,0
```
**Action:** Import as separate `Routine` records (e.g., "PPL - Pull", "PPL - Push", "PPL - Leg")

### Tier 3: Exercises in Day (✅ IMPORT as exercise_routine pivot)
```csv
row_id,USERID,TIMESTAMP,belongSys,superset,_id,exercise_id,belongplan,exercisename,setcount,timer,logs,bodypart,mysort,targetrep,setdone,setdonetime,interval_time,interval_unit,rest_time_enabled,interval_time_enabled
865331847,3773197,"2025-01-29 10:33:43",1,0,184,93,21,"Barbell Deadlift",4,120,"30x9,30x10,30x10",1,0,8,0,1738059610,0,0,1,0
```
**Action:** Import as `exercise_routine` pivot records linking Routine to Exercise

## Field Mappings

### 1. custom-exercises.csv → Exercise Model

| Old CSV Field | New Model Field | Notes |
|--------------|-----------------|-------|
| `name` | `name` | Direct mapping |
| `description` | `description` | Direct mapping |
| `image1`, `image2` | `image_path` | Skip for now (not critical) |
| `bodypart`, `bodypart2`, `bodypart3` | - | Skip (could add as tags later) |
| `equip1`, `equip2` | - | Skip |
| `rating` | - | Skip |
| - | `user_id` | Set to import user ID |

**Important:**
- Use `updateOrCreate(['name' => ...])` to handle exercises that may have been auto-created
- No ID mapping needed - exercises are looked up by name going forward!

---

### 2. routine-days.csv → Routine Model

| Old CSV Field | New Model Field | Notes |
|--------------|-----------------|-------|
| `_id` | - | Store in mapping table for lookups |
| `name` | `name` | Direct mapping (e.g., "PPL - Pull") |
| `package` | - | Skip (links to tier 1 which we ignore) |
| `day`, `dayIndex` | - | Skip |
| `interval_mode`, `rest_day`, `week`, `sort_order` | - | Skip |
| - | `user_id` | Set to import user ID |

**ID Mapping:**
- Build lookup: `old_routine_day_id => new_routine_id`
- Used by: routine-exercises, workout-sessions

---

### 3. routine-exercises.csv → exercise_routine Pivot

| Old CSV Field | New Pivot Field | Notes |
|--------------|-----------------|-------|
| `belongplan` | `routine_id` | Lookup old routine day ID → new routine ID |
| `exercise_id` | - | Store in IdMapper for later: `mapper->mapExerciseName(exercise_id, exercisename)` |
| `exercisename` | - | Use to `firstOrCreate()` Exercise, then get ID |
| `setcount` | `number_sets` | Direct mapping |
| `timer` | `rest_seconds` | Direct mapping |
| `mysort` | `sort` | Direct mapping |
| `logs` | - | Skip (just last logged data, not template) |
| `superset` | - | Skip (we don't support supersets) |
| `targetrep` | - | Skip (not in current schema) |

**Exercise Handling:**
```php
// First, store the mapping for later use
$mapper->mapExerciseName($row['exercise_id'], $row['exercisename']);

// Then get or create the exercise
$exercise = $user->exercises()->firstOrCreate(['name' => $row['exercisename']]);
// Use $exercise->id for pivot
```

---

### 4. workout-sessions.csv → WorkoutSession Model

| Old CSV Field | New Model Field | Notes |
|--------------|-----------------|-------|
| `_id` | - | Store in mapping table for lookups |
| `day_id` | `routine_id` | Lookup old routine day ID → new routine ID |
| `starttime` | `started_at` | Convert Unix timestamp to Carbon datetime |
| `endtime` | `ended_at` | Convert Unix timestamp to Carbon datetime |
| `total_time` | `duration_seconds` | Direct mapping |
| `total_exercise` | `total_exercises` | Direct mapping |
| `total_weight` | `total_kg_lifted` | Direct mapping |
| `workout_time`, `rest_time`, `wasted_time` | - | Skip (not in current schema) |
| `recordbreak` | - | Skip |
| `calories` | - | Skip |
| - | `user_id` | Set to import user ID |

**ID Mapping:**
- Build lookup: `old_session_id => new_workout_session_id`
- Used by: exercise-logs

---

### 5. exercise-logs.csv → WorkoutExercise + WorkoutSet Models

#### WorkoutExercise

| Old CSV Field | New Model Field | Notes |
|--------------|-----------------|-------|
| `belongsession` | `workout_session_id` | Lookup old session ID → new workout_session_id |
| `eid` | - | Store in IdMapper (if not already): `mapper->mapExerciseName(eid, ename)` |
| `ename` | - | Use to `firstOrCreate()` Exercise, then get ID |
| `logs` | - | Parse to count number of sets → `number_sets` |
| - | `rest_seconds` | Default to 60 or extract from routine if available |
| `day_item_id` | `sort` | Use as sort order within session |

**Exercise Handling:**
```php
// Store the mapping if not already present (may have been set by RoutineExerciseImporter)
if (!$mapper->hasExerciseName($row['eid'])) {
    $mapper->mapExerciseName($row['eid'], $row['ename']);
}

// Get or create the exercise
$exercise = $user->exercises()->firstOrCreate(['name' => $row['ename']]);
// Use $exercise->id
```

**Special Handling:**
- Parse `logs` field (e.g., `"22.5x5,22.5x8,22.5x8"`) to determine `number_sets`

#### WorkoutSet (Created from `logs` field)

Each exercise log creates multiple WorkoutSet records.

**Parsing Logic:**
- Input: `"22.5x5,22.5x8,22.5x8"`
- Split by comma → 3 sets
- For each set:
  - Split by `x`
  - Left side = `weight_kg` (22.5, 22.5, 22.5)
  - Right side = `number_reps` (5, 8, 8)
  - `completed_at` = `logTime` (convert Unix timestamp)

**Special Cases:**
- Bodyweight exercises: `0.5` or `0` for weight
- Time-based exercises (plank): Format `0x5x30` means 5 minutes 30 seconds (needs special parsing)

| Old CSV Field | New Model Field | Notes |
|--------------|-----------------|-------|
| `logs` (parsed) | `weight_kg` | Left side of `x` in each set |
| `logs` (parsed) | `number_reps` | Right side of `x` in each set |
| `logTime` | `completed_at` | Convert Unix timestamp, same for all sets in log |
| - | `workout_exercise_id` | Link to created WorkoutExercise |

---

### 6. exercise-records.csv → ExerciseRecord Model

| Old CSV Field | New Model Field | Notes |
|--------------|-----------------|-------|
| `eid` | - | Use to lookup exercise name via IdMapper |
| `record` | `best_weight_kg` | Direct mapping |
| `recordReachTime` | `achieved_at` | Convert Unix timestamp to Carbon datetime |
| `target1RM` | - | Skip (not in current schema) |
| `goalDate` | - | Skip |
| - | `user_id` | Set to import user ID |

**Exercise Handling:**
```php
// Lookup exercise name from IdMapper (populated by earlier importers)
$exerciseName = $mapper->getExerciseName($row['eid']);

// Find or create exercise by name
$exercise = $user->exercises()->firstOrCreate(['name' => $exerciseName]);

// Use $exercise->id for exercise_id
```

**How it works:**
- By the time this importer runs, RoutineExerciseImporter and ExerciseLogImporter have already populated the IdMapper with `old_exercise_id => exercise_name` mappings
- We simply lookup the name and use it to find/create the Exercise record

---

## Key Implementation Details

### ID Mapping Tables

Build in-memory arrays during import to map old IDs to new IDs:

```php
$exerciseIdMap = []; // old_exercise_id => new_exercise_id
$routineIdMap = [];  // old_routine_day_id => new_routine_id
$sessionIdMap = [];  // old_session_id => new_workout_session_id
```

### Unix Timestamp Conversion

Old CSV uses Unix timestamps. Convert using Carbon:

```php
use Illuminate\Support\Carbon;

$datetime = Carbon::createFromTimestamp($unixTimestamp);
```

### Logs Field Parsing

Complex field requiring custom parsing:

```php
// Example: "22.5x5,22.5x8,22.5x8"
$sets = explode(',', $logs);
foreach ($sets as $set) {
    [$weight, $reps] = explode('x', $set);
    // Create WorkoutSet with weight_kg and number_reps
}
```

**Edge Case - Time-based exercises:**
- Format: `0x5x30` = 5 minutes 30 seconds
- May need to skip or handle specially

### Default Values

When old data doesn't have a field we need:

- `WorkoutExercise.rest_seconds` → Default to 60
- `WorkoutExercise.sort` → Use `day_item_id` from old data or auto-increment

### User Assignment

All imported records should be assigned to a single user:
- Configurable via command option: `--user=1`
- Default to user ID 1

## CLI Command Structure

### Splitting Command

```bash
sail artisan import:split-csv {filename}

# Example:
sail artisan import:split-csv resources/imports/NoseGraze_20250129.csv
```

**Functionality:**
- Reads monolithic CSV
- Identifies section headers (`### ROUTINES ###`, etc.)
- Writes separate CSV files to `resources/imports/`
- Validates row counts

### Import Command

```bash
sail artisan import:data {--user=1} {--dry-run}

# Examples:
sail artisan import:data --user=1
sail artisan import:data --dry-run  # Validate without importing
```

**Functionality:**
- Prompts for confirmation
- Imports in order with progress bars
- Builds ID mappings
- Reports success/errors for each section
- Dry-run mode to validate data without persisting

## Validation & Error Handling

### Pre-Import Validation
- Check all CSV files exist
- Verify user exists
- Check for required columns in each CSV
- Validate foreign key references will resolve

### During Import
- Wrap in database transaction (rollback on error)
- Log errors to `storage/logs/import-errors.log`
- Continue on row error vs. abort entire import (configurable)

### Post-Import Validation
- Count records imported vs. expected
- Verify foreign key integrity
- Report orphaned records

## Testing Strategy

1. Test with small subset of data first
2. Validate ID mappings are correct
3. Spot-check imported records against source CSV
4. Verify relationships (WorkoutSession → WorkoutExercise → WorkoutSet chain)
5. Check personal records match expected values
