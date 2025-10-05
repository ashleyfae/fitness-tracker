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
3. **User** starts a **WorkoutSession** (typically by loading a **Routine**)
   - Creates **WorkoutExercise** records for each exercise in the session
   - Each **WorkoutExercise** has multiple **WorkoutSet** records
4. As the user completes each set, they log the `weight_kg` and `number_reps` in **WorkoutSet**
5. **WorkoutSession** tracks aggregated data: `duration_seconds`, `total_exercises`, `total_kg_lifted`

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

- **Action Classes**: Business logic is extracted into dedicated action classes in `app/Actions/` (e.g., `StoreExercise`, `ListExercises`, `UpdateRoutine`). Controllers delegate to these actions.
- **Form Requests**: All form validation uses dedicated request classes in `app/Http/Requests/`
- **Policies**: Authorization logic is in dedicated policy classes (ExercisePolicy, RoutinePolicy, WorkoutSessionPolicy)
- **View Components**: Reusable UI components in `app/View/Components/` (e.g., Modal, SearchExercises)

### Frontend

- Uses Laravel Blade templates in `resources/views/`
- Asset compilation via Laravel Mix (webpack.mix.js)
- Sass stylesheets in `resources/sass/`
- JavaScript in `resources/js/`
- Ziggy package for route generation in JavaScript

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
- **Exercise.name**: Can be empty in rare cases (76 historical records imported with empty names). New exercises should require a name.
