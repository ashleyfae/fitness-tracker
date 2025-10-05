# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 11 fitness tracking application that allows users to log workouts, track exercises, and manage workout routines. The application uses PHP 8.2+, Laravel Sail for Docker-based development, Laravel Mix for frontend asset compilation, and follows standard Laravel conventions.

## Key Commands

### Development
- `sail up` - Start Docker containers
- `sail up -d` - Start Docker containers in detached mode
- `sail down` - Stop Docker containers
- `npm run dev` - Compile assets for development
- `npm run watch` - Watch and recompile assets on change
- `npm run hot` - Hot module replacement for development

### Testing
- `sail artisan test` - Run all tests
- `sail artisan test --filter=TestName` - Run specific test
- `sail artisan test tests/Feature/ExampleTest.php` - Run specific test file

### Code Quality
- `sail bin pint` - Format code using Laravel Pint

### Database
- `sail artisan migrate` - Run database migrations
- `sail artisan migrate:fresh --seed` - Reset database and run seeders
- `sail artisan db:seed` - Run database seeders

### Useful Artisan Commands
- `sail artisan make:model ModelName -mfc` - Create model with migration, factory, and controller
- `sail artisan make:test TestName` - Create feature test
- `sail artisan make:test TestName --unit` - Create unit test
- `sail artisan route:list` - List all registered routes
- `sail artisan user:create` - Create a new user (custom command)

## Architecture

### Data Model Hierarchy

The application follows a specific workout logging flow:

1. **User** creates **Exercises** (e.g., "Bench Press", "Squat")
2. **User** creates **Routines** composed of multiple **Exercises** via the `exercise_routine` pivot table
   - Pivot stores: `number_sets`, `rest_seconds`, `sort` (order)
3. **User** starts a **WorkoutSession** (typically by selecting a **Routine**)
   - Session is created with `started_at` timestamp and `ended_at = null`
   - Session references the routine but does NOT pre-populate exercises
4. During the workout, user adds sets in real-time via AJAX:
   - First set for an exercise creates both **WorkoutExercise** and **WorkoutSet** atomically
   - Additional sets create new **WorkoutSet** records
   - `WorkoutExercise.number_sets` is automatically maintained by observer
5. User can:
   - Skip exercises from the routine (no WorkoutExercise created)
   - Do more/fewer sets than the routine specifies
   - Add extra exercises not in the routine
6. When complete, user clicks "Complete Workout":
   - Sets `ended_at` timestamp
   - Observer calculates `total_exercises` and `total_kg_lifted`
   - `duration_seconds` is auto-calculated (generated column)

### Model Relationships

```
User
├── hasMany Exercise
├── hasMany Routine
└── hasMany WorkoutSession

Routine
└── belongsToMany Exercise (via ExerciseRoutine pivot)

WorkoutSession
├── belongsTo Routine
├── belongsTo User
└── hasMany WorkoutExercise

WorkoutExercise
├── belongsTo WorkoutSession
├── belongsTo Exercise
└── hasMany WorkoutSet

WorkoutSet
└── belongsTo WorkoutExercise
```

### Key Model Traits

- **BelongsToUser** (app/Models/Traits/BelongsToUser.php): Applied to models that belong to a user (Exercise, Routine, WorkoutSession). Provides the `user()` relationship and `user_id` property.

### Architecture Patterns

- **Action Classes**: Business logic is extracted into dedicated action classes in `app/Actions/` (e.g., `StoreExercise`, `ListExercises`, `UpdateRoutine`, `PrepareWorkoutSessionData`). Controllers delegate to these actions.
- **DTOs (Data Transfer Objects)**: Used to pass structured data between layers (e.g., `WorkoutExerciseData` merges expected routine data with actual workout data). Located in `app/DataTransferObjects/`.
- **Form Requests**: All form validation uses dedicated request classes in `app/Http/Requests/`
- **Policies**: Authorization logic is in dedicated policy classes (ExercisePolicy, RoutinePolicy, WorkoutSessionPolicy)
- **Observers**: Model lifecycle logic handled by observers in `app/Observers/`:
  - `WorkoutSessionObserver` - Calculates totals when workout is completed
  - `WorkoutSetObserver` - Updates parent WorkoutExercise.number_sets and deletes exercise if no sets remain
- **View Components**: Reusable UI components in `app/View/Components/` (e.g., Modal, SearchExercises)

### Frontend

- Uses Laravel Blade templates in `resources/views/`
- Asset compilation via Laravel Mix (webpack.mix.js)
- Sass stylesheets in `resources/sass/`
- JavaScript in `resources/js/`
  - `app.js` - Main application JavaScript
  - `workout-session.js` - Real-time workout tracking with AJAX (vanilla JS, no framework)
- Ziggy package for route generation in JavaScript
- AJAX interactions use content negotiation (`expectsJson()`) - same routes support both JSON and HTML responses

### Testing

- Tests extend `Tests\TestCase` which includes:
  - `CreatesApplication` trait
  - `CanGetInaccessibleMembers` trait for accessing private/protected members in tests
- Feature tests in `tests/Feature/`
- Unit tests in `tests/Unit/`
- PHPUnit configuration in `phpunit.xml` with testing environment variables
- Factories available for all main models (User, Exercise, Routine, WorkoutSession)

### Authentication

- Laravel Fortify handles authentication
- Custom user creation actions in `app/Actions/Fortify/`
- Routes protected by `auth` middleware
- Public homepage route at `/`
- Authenticated routes: exercises, routines, workouts (all resourceful)

## Development Guidelines

### When Creating New Models

1. Models that belong to users should use the `BelongsToUser` trait
2. Add PHPDoc blocks with `@property` declarations for all database columns and relationships
3. Add `@mixin Builder` for IDE autocomplete
4. Create corresponding factory, migration, and policy if needed
5. Define `$fillable` or `$guarded` properties
6. Cast datetime columns in `$casts` array

### When Creating Tests

1. Use factories to create test data
2. Feature tests should test full request/response cycle
3. Unit tests for isolated logic (actions, services)
4. Use `CanGetInaccessibleMembers` trait to test private/protected methods when needed
5. If a data provider is needed, it should return a `Generator` and not an array
    - Reference parameter names as array keys in test cases

### Database Conventions

- Use migrations for all schema changes
- Foreign keys should be `model_id` (e.g., `user_id`, `exercise_id`)
- Pivot tables named alphabetically (e.g., `exercise_routine`)
- Timestamps on all tables by default

### Important Schema Notes

- **WorkoutSession.routine_id**: Nullable for backwards compatibility with imported historical data only. All new workout sessions MUST have a routine_id. Views should handle null with "Unknown Workout" fallback.
- **WorkoutSession.duration_seconds**: Generated column, automatically calculated from `started_at` and `ended_at`. Do not set directly.
- **WorkoutSession.total_exercises & total_kg_lifted**: Updated by `WorkoutSessionObserver` when `ended_at` is set. Do not set directly.
- **WorkoutExercise.number_sets**: Updated by `WorkoutSetObserver` when sets are created/deleted. Do not set directly (except initial creation).
- **Exercise.name**: Can be empty in rare cases (76 historical records imported with empty names). New exercises should require a name.

## Workout Session UI Implementation

### Expected vs Actual Data Model

The workout session edit UI (`/workouts/{id}/edit`) uses an "Expected vs Actual" pattern:

- **Expected Data**: Comes from the `Routine` (via `exercise_routine` pivot) - which exercises, how many sets, rest time
- **Actual Data**: Comes from `WorkoutExercise` and `WorkoutSet` tables - what was actually completed
- **Merging**: `PrepareWorkoutSessionData` action merges these into `WorkoutExerciseData` DTOs for the UI

### Why Not Pre-fill?

The database is NOT pre-populated with expected exercises/sets. Only actual completed work is persisted. This approach:
- Keeps data clean (no "ghost" records)
- Makes it trivial to skip exercises (just don't add sets)
- Allows doing more/fewer sets than planned
- Provides clear audit trail of what was actually done

### AJAX Workflow

Routes in `routes/web.php` support both JSON (AJAX) and HTML (form submission) via content negotiation:

1. **Add first set**: `POST /workouts/{session}/exercises` - Creates WorkoutExercise + first WorkoutSet atomically
2. **Add subsequent set**: `POST /workouts/{session}/exercises/{exercise}/sets` - Creates WorkoutSet
3. **Update set**: `PATCH /workouts/{session}/exercises/{exercise}/sets/{set}` - Updates WorkoutSet
4. **Delete set**: `DELETE /workouts/{session}/exercises/{exercise}/sets/{set}` - Deletes WorkoutSet (observer handles cleanup)
5. **Complete workout**: `POST /workouts/{session}/complete` - Sets `ended_at` (observer calculates totals)

### Observer Chain

When a set is created/deleted, this automatic chain occurs:

1. `WorkoutSetObserver.created/deleted` fires
2. Updates parent `WorkoutExercise.number_sets`
3. If `number_sets` reaches 0, deletes the `WorkoutExercise`

When workout is completed:

1. `WorkoutSessionObserver.updated` fires (triggered by `ended_at` change)
2. Calculates `total_exercises` (count of WorkoutExercise records)
3. Calculates `total_kg_lifted` (sum of weight × reps across all sets)

### MVP Trade-offs

Current implementation (MVP):
- ✅ Delete set: Updates DOM dynamically (no page reload)
- ⏳ Add set: Page reload (easier for MVP, will be replaced with dynamic DOM updates)
- ⏳ Alerts for feedback (will be replaced with button loading states)

Future enhancements documented in `docs/workout-session-ui-implementation.md`.
