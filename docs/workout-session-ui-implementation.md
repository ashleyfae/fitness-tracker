# Workout Session UI Implementation Plan

## Overview

This document outlines the implementation plan for the workout session edit UI. This is the primary interface used during an active workout to log exercises, sets, reps, and weight in real-time.

## Core Concept

**Data Model: Expected vs Actual**

- **Expected Data**: Comes from the routine's `exercise_routine` pivot table (which exercises, how many sets, rest time)
- **Actual Data**: Stored in `workout_exercises` and `workout_sets` tables (what was actually completed)
- **UI Strategy**: Merge expected and actual data to show a unified view where users can see what's planned vs what's done

### Why Not Pre-fill the Database?

We considered pre-filling `workout_exercises` and `workout_sets` with all expected data, then updating/pruning as the user progresses. However, this approach was rejected because:

- Requires complex pruning logic at workout completion
- Creates "ghost" records that need special handling
- Harder to distinguish "not started" from "skipped" from "in progress"
- Risk of orphaned data if workout is abandoned
- Messier audit trail

Instead, we only persist what was actually completed, and compute the expected data on-the-fly from the routine.

## Architecture

### Backend Components

#### 1. Data Transfer Object

**File**: `app/DataTransferObjects/WorkoutExerciseData.php`

```php
namespace App\DataTransferObjects;

use App\Models\Exercise;
use Illuminate\Support\Collection;

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
```

#### 2. Action Class: Prepare Workout Data

**File**: `app/Actions/WorkoutSessions/PrepareWorkoutSessionData.php`

```php
namespace App\Actions\WorkoutSessions;

use App\DataTransferObjects\WorkoutExerciseData;
use App\Models\WorkoutSession;
use Illuminate\Support\Collection;

class PrepareWorkoutSessionData
{
    /**
     * Prepare workout session data by merging expected (from routine)
     * and actual (from workout_exercises) data.
     *
     * @return Collection<WorkoutExerciseData>
     */
    public function execute(WorkoutSession $session): Collection
    {
        $session->load([
            'routine.exercises.exercise',
            'exercises.exercise',
            'exercises.sets',
        ]);

        $exercises = collect();

        // Add exercises from routine
        foreach ($session->routine->exercises as $routineExercise) {
            $workoutExercise = $session->exercises
                ->firstWhere('exercise_id', $routineExercise->id);

            $exercises->push(new WorkoutExerciseData(
                exercise: $routineExercise,
                expectedSets: $routineExercise->pivot->number_sets,
                restSeconds: $routineExercise->pivot->rest_seconds,
                sort: $workoutExercise?->sort ?? $routineExercise->pivot->sort,
                actualSets: $workoutExercise?->sets ?? collect(),
                workoutExerciseId: $workoutExercise?->id,
                fromRoutine: true,
            ));
        }

        // Add exercises not in routine (manually added during workout)
        $routineExerciseIds = $session->routine->exercises->pluck('id');
        foreach ($session->exercises as $workoutExercise) {
            if (!$routineExerciseIds->contains($workoutExercise->exercise_id)) {
                $exercises->push(new WorkoutExerciseData(
                    exercise: $workoutExercise->exercise,
                    expectedSets: 1, // we can only expect at least 1
                    restSeconds: $workoutExercise->rest_seconds,
                    sort: $workoutExercise->sort,
                    actualSets: $workoutExercise->sets,
                    workoutExerciseId: $workoutExercise->id,
                    fromRoutine: false,
                ));
            }
        }

        return $exercises->sortBy('sort')->values();
    }
}
```

#### 3. Observer

**File**: `app/Observers/WorkoutSessionObserver.php` (new)

```php
namespace App\Observers;

use App\Models\WorkoutSession;

class WorkoutSessionObserver
{
    /**
     * Handle the WorkoutSession "updated" event.
     * When ended_at is set, calculate total_exercises and total_kg_lifted.
     */
    public function updated(WorkoutSession $workoutSession): void
    {
        // Only recalculate if ended_at was just set
        if ($workoutSession->isDirty('ended_at') && $workoutSession->ended_at !== null) {
            $workoutSession->updateQuietly([
                'total_exercises' => $workoutSession->exercises()->count(),
                'total_kg_lifted' => $workoutSession->exercises()
                    ->with('sets')
                    ->get()
                    ->flatMap->sets
                    ->sum(fn($set) => $set->weight_kg * $set->number_reps),
            ]);
        }
    }
}
```

**File**: `app/Models/WorkoutSession.php` (add attribute)

```php
use App\Observers\WorkoutSessionObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy(WorkoutSessionObserver::class)]
class WorkoutSession extends Model
{
    // ... rest of model
}
```

#### 4. Routes

**File**: `routes/web.php`

```php
// Workout session resources (existing)
Route::resource('workouts', WorkoutSessionController::class)
    ->middleware('auth');

// Routes for AJAX interactions during workout
// These support both JSON (AJAX) and HTML responses via content negotiation
Route::middleware('auth')->prefix('workouts/{workoutSession}')->group(function () {
    // Create workout exercise and first set atomically
    Route::post('exercises', [WorkoutExerciseController::class, 'store'])
        ->name('workouts.exercises.store');

    // Add a set to an existing workout exercise
    Route::post('exercises/{workoutExercise}/sets', [WorkoutSetController::class, 'store'])
        ->name('workouts.exercises.sets.store');

    // Update an existing set
    Route::patch('exercises/{workoutExercise}/sets/{workoutSet}', [WorkoutSetController::class, 'update'])
        ->name('workouts.exercises.sets.update');

    // Delete a set
    Route::delete('exercises/{workoutExercise}/sets/{workoutSet}', [WorkoutSetController::class, 'destroy'])
        ->name('workouts.exercises.sets.destroy');

    // Complete the workout (sets ended_at timestamp)
    Route::post('complete', [WorkoutSessionController::class, 'complete'])
        ->name('workouts.complete');
});
```

#### 5. Controllers and Request Classes

**File**: `app/Http/Controllers/WorkoutSessionController.php` (additions)

```php
// Edit method (existing, updated) - uses dependency injection
public function edit(WorkoutSession $workoutSession, PrepareWorkoutSessionData $prepareData)
{
    $exercises = $prepareData->execute($workoutSession);

    return view('workout-sessions.edit', [
        'workoutSession' => $workoutSession,
        'exercises' => $exercises,
    ]);
}

// Complete workout
// Observer handles total_exercises and total_kg_lifted calculations
public function complete(WorkoutSession $workoutSession)
{
    $workoutSession->update(['ended_at' => now()]);

    if (request()->expectsJson()) {
        return response()->json([
            'success' => true,
            'redirect' => route('workouts.show', $workoutSession),
        ]);
    }

    return redirect()->route('workouts.show', $workoutSession);
}
```

**File**: `app/Http/Requests/StoreWorkoutExerciseRequest.php` (new)

```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkoutExerciseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by route model binding policy
    }

    public function rules(): array
    {
        return [
            'exercise_id' => 'required|exists:exercises,id',
            'weight_kg' => 'required|numeric|min:0',
            'number_reps' => 'required|integer|min:0',
            'rest_seconds' => 'required|integer|min:0',
            'sort' => 'required|integer|min:0',
        ];
    }
}
```

**File**: `app/Http/Controllers/WorkoutExerciseController.php` (new)

```php
namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkoutExerciseRequest;
use App\Models\WorkoutSession;

class WorkoutExerciseController extends Controller
{
    /**
     * Create a workout exercise and its first set atomically.
     */
    public function store(StoreWorkoutExerciseRequest $request, WorkoutSession $workoutSession)
    {
        $validated = $request->validated();

        // Create workout exercise
        $workoutExercise = $workoutSession->exercises()->create([
            'exercise_id' => $validated['exercise_id'],
            'number_sets' => 1, // Will be updated as more sets are added
            'rest_seconds' => $validated['rest_seconds'],
            'sort' => $validated['sort'],
        ]);

        // Create first set
        $set = $workoutExercise->sets()->create([
            'weight_kg' => $validated['weight_kg'],
            'number_reps' => $validated['number_reps'],
            'completed_at' => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'workout_exercise_id' => $workoutExercise->id,
                'set' => $set,
            ]);
        }

        return redirect()->route('workouts.edit', $workoutSession);
    }
}
```

**File**: `app/Http/Requests/StoreWorkoutSetRequest.php` (new)

```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkoutSetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by route model binding policy
    }

    public function rules(): array
    {
        return [
            'weight_kg' => 'required|numeric|min:0',
            'number_reps' => 'required|integer|min:0',
        ];
    }
}
```

**File**: `app/Http/Requests/UpdateWorkoutSetRequest.php` (new)

```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkoutSetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by route model binding policy
    }

    public function rules(): array
    {
        return [
            'weight_kg' => 'required|numeric|min:0',
            'number_reps' => 'required|integer|min:0',
        ];
    }
}
```

**File**: `app/Http/Controllers/WorkoutSetController.php` (new)

```php
namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkoutSetRequest;
use App\Http\Requests\UpdateWorkoutSetRequest;
use App\Models\WorkoutExercise;
use App\Models\WorkoutSession;
use App\Models\WorkoutSet;

class WorkoutSetController extends Controller
{
    /**
     * Add a new set to an existing workout exercise.
     */
    public function store(StoreWorkoutSetRequest $request, WorkoutSession $workoutSession, WorkoutExercise $workoutExercise)
    {
        $validated = $request->validated();

        $set = $workoutExercise->sets()->create([
            'weight_kg' => $validated['weight_kg'],
            'number_reps' => $validated['number_reps'],
            'completed_at' => now(),
        ]);

        // Update number_sets count
        $workoutExercise->update([
            'number_sets' => $workoutExercise->sets()->count(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'set' => $set,
            ]);
        }

        return redirect()->route('workouts.edit', $workoutSession);
    }

    /**
     * Update an existing set.
     */
    public function update(UpdateWorkoutSetRequest $request, WorkoutSession $workoutSession, WorkoutExercise $workoutExercise, WorkoutSet $workoutSet)
    {
        $workoutSet->update($request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'set' => $workoutSet,
            ]);
        }

        return redirect()->route('workouts.edit', $workoutSession);
    }

    /**
     * Delete a set.
     */
    public function destroy(WorkoutSession $workoutSession, WorkoutExercise $workoutExercise, WorkoutSet $workoutSet)
    {
        $workoutSet->delete();

        // Update number_sets count
        $setCount = $workoutExercise->sets()->count();
        $workoutExercise->update([
            'number_sets' => $setCount,
        ]);

        // If no sets remain, delete the workout exercise
        if ($setCount === 0) {
            $workoutExercise->delete();
        }

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
            ]);
        }

        return redirect()->route('workouts.edit', $workoutSession);
    }
}
```

### Frontend Components

#### 1. Blade View Structure

**File**: `resources/views/workout-sessions/edit.blade.php`

```blade
@extends('layouts.page')

@section('title', 'Workout Session')

@section('header')
    <h1>Workout: {{ $workoutSession->routine?->name ?? 'Unknown Workout' }}</h1>
    <p>Started: {{ $workoutSession->started_at->format('g:i A') }}</p>
@endsection

@section('content')
    <div id="workout-session"
         data-session-id="{{ $workoutSession->id }}"
         data-csrf="{{ csrf_token() }}">

        @foreach($exercises as $exerciseData)
            <div class="exercise"
                 data-exercise-id="{{ $exerciseData->exercise->id }}"
                 data-workout-exercise-id="{{ $exerciseData->workoutExerciseId }}"
                 data-expected-sets="{{ $exerciseData->expectedSets }}"
                 data-rest-seconds="{{ $exerciseData->restSeconds }}"
                 data-sort="{{ $exerciseData->sort }}">

                <div class="exercise-header">
                    <h2>{{ $exerciseData->exercise->name }}</h2>
                    @if($exerciseData->fromRoutine)
                        <span class="badge">From routine</span>
                    @else
                        <span class="badge">Added manually</span>
                    @endif
                </div>

                <div class="sets-container">
                    @for($i = 1; $i <= $exerciseData->maxSets(); $i++)
                        @php
                            $set = $exerciseData->actualSets->get($i - 1);
                        @endphp

                        <div class="set" data-set-index="{{ $i }}" @if($set) data-set-id="{{ $set->id }}" @endif>
                            <label>Set {{ $i }}</label>

                            @if($set)
                                {{-- Existing set (editable) --}}
                                <input type="number"
                                       class="set-weight"
                                       data-set-id="{{ $set->id }}"
                                       value="{{ $set->weight_kg }}"
                                       step="0.5"
                                       placeholder="Weight (kg)">
                                <input type="number"
                                       class="set-reps"
                                       data-set-id="{{ $set->id }}"
                                       value="{{ $set->number_reps }}"
                                       placeholder="Reps">
                                <button class="save-set" data-set-id="{{ $set->id }}">Save</button>
                                <button class="delete-set" data-set-id="{{ $set->id }}">Delete</button>
                            @else
                                {{-- Empty set (ready to add) --}}
                                <input type="number"
                                       class="set-weight"
                                       step="0.5"
                                       placeholder="Weight (kg)">
                                <input type="number"
                                       class="set-reps"
                                       placeholder="Reps">
                                <button class="add-set">Add Set</button>
                            @endif
                        </div>
                    @endfor

                    {{-- Button to add additional sets beyond expected --}}
                    <button class="add-extra-set">+ Add Another Set</button>
                </div>
            </div>
        @endforeach

        <div class="workout-actions">
            <button id="complete-workout">Complete Workout</button>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ mix('js/workout-session.js') }}"></script>
@endsection
```

#### 2. JavaScript Implementation

**File**: `resources/js/workout-session.js`

```javascript
/**
 * Workout Session AJAX Handler
 */
class WorkoutSession {
    constructor() {
        this.sessionId = document.getElementById('workout-session').dataset.sessionId;
        this.csrfToken = document.getElementById('workout-session').dataset.csrf;
        this.init();
    }

    init() {
        this.attachEventListeners();
    }

    attachEventListeners() {
        // Add set buttons
        document.querySelectorAll('.add-set').forEach(btn => {
            btn.addEventListener('click', (e) => this.handleAddSet(e));
        });

        // Save set buttons
        document.querySelectorAll('.save-set').forEach(btn => {
            btn.addEventListener('click', (e) => this.handleSaveSet(e));
        });

        // Delete set buttons
        document.querySelectorAll('.delete-set').forEach(btn => {
            btn.addEventListener('click', (e) => this.handleDeleteSet(e));
        });

        // Add extra set buttons
        document.querySelectorAll('.add-extra-set').forEach(btn => {
            btn.addEventListener('click', (e) => this.handleAddExtraSet(e));
        });

        // Complete workout button
        document.getElementById('complete-workout').addEventListener('click', () => {
            this.handleCompleteWorkout();
        });
    }

    async handleAddSet(e) {
        const setDiv = e.target.closest('.set');
        const exerciseDiv = e.target.closest('.exercise');
        const weightInput = setDiv.querySelector('.set-weight');
        const repsInput = setDiv.querySelector('.set-reps');

        const weight = parseFloat(weightInput.value);
        const reps = parseInt(repsInput.value);

        if (!weight || !reps) {
            alert('Please enter both weight and reps');
            return;
        }

        const exerciseId = exerciseDiv.dataset.exerciseId;
        const workoutExerciseId = exerciseDiv.dataset.workoutExerciseId;
        const restSeconds = exerciseDiv.dataset.restSeconds;
        const sort = exerciseDiv.dataset.sort;

        // Determine if this is the first set or additional set
        if (!workoutExerciseId) {
            // First set - create workout exercise and set
            await this.createWorkoutExercise(exerciseId, weight, reps, restSeconds, sort);
        } else {
            // Additional set - just add the set
            await this.addSetToExercise(workoutExerciseId, weight, reps);
        }

        // Reload page to refresh UI
        // TODO: Update DOM dynamically instead of reloading
        window.location.reload();
    }

    async handleSaveSet(e) {
        const setDiv = e.target.closest('.set');
        const exerciseDiv = e.target.closest('.exercise');
        const setId = e.target.dataset.setId;
        const weightInput = setDiv.querySelector(`.set-weight[data-set-id="${setId}"]`);
        const repsInput = setDiv.querySelector(`.set-reps[data-set-id="${setId}"]`);

        const weight = parseFloat(weightInput.value);
        const reps = parseInt(repsInput.value);

        if (!weight || !reps) {
            alert('Please enter both weight and reps');
            return;
        }

        const workoutExerciseId = exerciseDiv.dataset.workoutExerciseId;

        await this.updateSet(workoutExerciseId, setId, weight, reps);

        alert('Set updated!');
    }

    async handleDeleteSet(e) {
        if (!confirm('Delete this set?')) return;

        const setDiv = e.target.closest('.set');
        const exerciseDiv = e.target.closest('.exercise');
        const setId = e.target.dataset.setId;
        const workoutExerciseId = exerciseDiv.dataset.workoutExerciseId;

        try {
            await this.deleteSet(workoutExerciseId, setId);

            // Remove the set from DOM
            setDiv.remove();

            // Check if this was the last set for this exercise
            const remainingSets = exerciseDiv.querySelectorAll('.set[data-set-id]');
            if (remainingSets.length === 0) {
                // Reset exercise to "not started" state by removing workout-exercise-id
                exerciseDiv.removeAttribute('data-workout-exercise-id');
            }

            alert('Set deleted!');
        } catch (error) {
            alert('Failed to delete set: ' + error.message);
        }
    }

    async handleAddExtraSet(e) {
        const exerciseDiv = e.target.closest('.exercise');
        const setsContainer = exerciseDiv.querySelector('.sets-container');
        const expectedSets = parseInt(exerciseDiv.dataset.expectedSets);
        const currentSets = setsContainer.querySelectorAll('.set').length;
        const nextSetIndex = currentSets + 1;

        // Add new empty set to DOM
        const setHtml = `
            <div class="set" data-set-index="${nextSetIndex}">
                <label>Set ${nextSetIndex}</label>
                <input type="number" class="set-weight" step="0.5" placeholder="Weight (kg)">
                <input type="number" class="set-reps" placeholder="Reps">
                <button class="add-set">Add Set</button>
            </div>
        `;

        // Insert before the "Add Another Set" button
        e.target.insertAdjacentHTML('beforebegin', setHtml);

        // Re-attach event listeners
        this.attachEventListeners();
    }

    async handleCompleteWorkout() {
        if (!confirm('Complete this workout?')) return;

        const response = await this.apiRequest('POST', `/api/workouts/${this.sessionId}/complete`);

        if (response.success) {
            window.location.href = response.redirect;
        }
    }

    // API methods
    async createWorkoutExercise(exerciseId, weightKg, numberReps, restSeconds, sort) {
        return await this.apiRequest('POST', `/api/workouts/${this.sessionId}/exercises`, {
            exercise_id: exerciseId,
            weight_kg: weightKg,
            number_reps: numberReps,
            rest_seconds: restSeconds,
            sort: sort,
        });
    }

    async addSetToExercise(workoutExerciseId, weightKg, numberReps) {
        return await this.apiRequest('POST',
            `/api/workouts/${this.sessionId}/exercises/${workoutExerciseId}/sets`, {
            weight_kg: weightKg,
            number_reps: numberReps,
        });
    }

    async updateSet(workoutExerciseId, setId, weightKg, numberReps) {
        return await this.apiRequest('PATCH',
            `/api/workouts/${this.sessionId}/exercises/${workoutExerciseId}/sets/${setId}`, {
            weight_kg: weightKg,
            number_reps: numberReps,
        });
    }

    async deleteSet(workoutExerciseId, setId) {
        return await this.apiRequest('DELETE',
            `/api/workouts/${this.sessionId}/exercises/${workoutExerciseId}/sets/${setId}`);
    }

    async apiRequest(method, url, data = null) {
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken,
                'Accept': 'application/json',
            },
        };

        if (data) {
            options.body = JSON.stringify(data);
        }

        const response = await fetch(url, options);

        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.message || 'Request failed');
        }

        return await response.json();
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new WorkoutSession();
});
```

**File**: `webpack.mix.js` (add entry point)

```javascript
mix.js('resources/js/app.js', 'public/js')
   .js('resources/js/workout-session.js', 'public/js')  // Add this
   .sass('resources/sass/app.scss', 'public/css');
```

## Data Flow Examples

### Example 1: Starting a new exercise (first set)

1. User enters weight (100kg) and reps (8) in Set 1 input fields
2. User clicks "Add Set" button
3. JS detects `workoutExerciseId` is null (first set for this exercise)
4. JS calls `POST /api/workouts/{session}/exercises` with:
   ```json
   {
     "exercise_id": 123,
     "weight_kg": 100,
     "number_reps": 8,
     "rest_seconds": 90,
     "sort": 0
   }
   ```
5. Backend creates `WorkoutExercise` and first `WorkoutSet` atomically
6. Page reloads, now showing the completed set with edit buttons

### Example 2: Adding subsequent sets

1. User enters weight (100kg) and reps (10) in Set 2 input fields
2. User clicks "Add Set" button
3. JS detects `workoutExerciseId` exists (exercise already started)
4. JS calls `POST /api/workouts/{session}/exercises/{workoutExercise}/sets` with:
   ```json
   {
     "weight_kg": 100,
     "number_reps": 10
   }
   ```
5. Backend creates new `WorkoutSet` and updates `WorkoutExercise.number_sets`
6. Page reloads with updated UI

### Example 3: Editing a completed set

1. User changes reps from 10 to 8 in Set 2
2. User clicks "Save" button
3. JS calls `PATCH /api/workouts/{session}/exercises/{workoutExercise}/sets/{set}` with:
   ```json
   {
     "weight_kg": 100,
     "number_reps": 8
   }
   ```
4. Backend updates the `WorkoutSet` record
5. Alert shown: "Set updated!"

### Example 4: Completing the workout

1. User clicks "Complete Workout" button
2. Confirmation dialog shown
3. JS calls `POST /api/workouts/{session}/complete`
4. Backend sets `ended_at`, calculates `total_exercises` and `total_kg_lifted`
5. User redirected to workout summary page (show view)

## Edge Cases & Considerations

### Sorting Exercises

- Exercises from routine use `exercise_routine.pivot.sort` by default
- If user has already started the exercise, use `workout_exercises.sort` instead (allows re-ordering during workout)
- Manually added exercises get a sort value provided by the user (UI for this comes later)

### Doing More Sets Than Expected

- UI renders `max(expectedSets, actualSets.count)` set inputs
- "Add Another Set" button always available to go beyond expected
- No validation preventing more sets than expected

### Skipping Exercises

- If user never adds any sets for an exercise, no `workout_exercises` record is created
- Exercise still shows in UI (from routine) but remains empty
- Perfectly valid - user simply didn't do that exercise

### Deleting All Sets

- When last set is deleted, the `workout_exercises` record is also deleted
- Exercise returns to "not started" state
- Next time user adds a set, a new `workout_exercises` record is created

### Abandoned Workouts

- If user never clicks "Complete Workout", the session remains with `ended_at = null`
- Can identify abandoned workouts with `WHERE ended_at IS NULL AND started_at < NOW() - INTERVAL '2 hours'`
- Could add a cleanup job or manual completion later

### Authorization

- All routes should verify the workout session belongs to the authenticated user
- Use route model binding with policies
- Policy check: `$user->id === $workoutSession->user_id`

## CSS Requirements

The following CSS classes/elements need styling (to be created by user, not Claude):

- `.exercise` - Container for each exercise
- `.exercise-header` - Header section of exercise (contains name and badge)
- `.badge` - Badge indicator for "From routine" / "Added manually"
- `.sets-container` - Container for all sets within an exercise
- `.set` - Individual set row (contains label, inputs, buttons)
- `.workout-actions` - Container for workout-level actions (Complete Workout button)

**Note:** All `<button>` elements will use existing button styles from `resources/sass/app/components/_buttons.scss`. No additional button styling needed.

## Future Enhancements

### Phase 2: UX Improvements

- Replace page reload for adding sets with dynamic DOM updates (currently only deletes are dynamic)
- Replace alert() dialogs with button loading states (disabled while saving, then re-enabled)
- Add rest timer countdown between sets
- Add auto-save for sets (save on blur instead of button click)
- Add keyboard shortcuts (Enter to save, Tab to next field)
- Show running total of kg lifted and exercise count

### Phase 3: Advanced Features

- Reorder exercises during workout (drag-and-drop or up/down buttons)
- Add exercises not in routine during workout
- Copy previous workout's weights/reps as defaults
- Show personal records as you lift
- Add notes to individual sets or exercises

## Implementation Checklist

### Backend
- [ ] Create `WorkoutExerciseData` DTO (`app/DataTransferObjects/WorkoutExerciseData.php`)
- [ ] Create `PrepareWorkoutSessionData` action (`app/Actions/WorkoutSessions/PrepareWorkoutSessionData.php`)
- [ ] Create `WorkoutSessionObserver` (`app/Observers/WorkoutSessionObserver.php`)
- [ ] Add `#[ObservedBy]` attribute to `WorkoutSession` model
- [ ] Create `StoreWorkoutExerciseRequest` (`app/Http/Requests/StoreWorkoutExerciseRequest.php`)
- [ ] Create `StoreWorkoutSetRequest` (`app/Http/Requests/StoreWorkoutSetRequest.php`)
- [ ] Create `UpdateWorkoutSetRequest` (`app/Http/Requests/UpdateWorkoutSetRequest.php`)
- [ ] Create `WorkoutExerciseController` (`app/Http/Controllers/WorkoutExerciseController.php`)
- [ ] Create `WorkoutSetController` (`app/Http/Controllers/WorkoutSetController.php`)
- [ ] Update `WorkoutSessionController::edit()` method (use DI for action)
- [ ] Add `WorkoutSessionController::complete()` method
- [ ] Add routes to `routes/web.php` (without 'api' prefix, using content negotiation)
- [ ] Add `$fillable` properties to models if needed (`WorkoutSet` likely needs `completed_at`)
- [ ] Add authorization policies for workout exercises and sets

### Frontend
- [ ] Update `workout-sessions/edit.blade.php` view
- [ ] Create `resources/js/workout-session.js`
- [ ] Update `webpack.mix.js` to compile workout-session.js
- [ ] Add any necessary CSS for workout session UI

### Testing
- [ ] Test complete user flow: start workout → add sets → edit sets → delete sets → complete workout
- [ ] Test skipping exercises (not adding any sets)
- [ ] Test adding more sets than expected
- [ ] Test observer correctly calculates totals when workout is completed
- [ ] Test content negotiation (JSON vs HTML responses)
