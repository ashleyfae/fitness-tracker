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

        try {
            // Determine if this is the first set or additional set
            if (!workoutExerciseId) {
                // First set - create workout exercise and set
                await this.createWorkoutExercise(exerciseId, weight, reps, restSeconds, sort);
            } else {
                // Additional set - just add the set
                await this.addSetToExercise(workoutExerciseId, weight, reps);
            }

            // Reload page to refresh UI
            window.location.reload();
        } catch (error) {
            alert('Failed to add set: ' + error.message);
        }
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

        try {
            await this.updateSet(workoutExerciseId, setId, weight, reps);
            alert('Set updated!');
        } catch (error) {
            alert('Failed to update set: ' + error.message);
        }
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
                <div class="set--fields">
                    <input type="number" class="set-weight" step="0.5" placeholder="Weight (kg)">
                    <input type="number" class="set-reps" placeholder="Reps">
                    <button class="add-set">Save Set</button>
                </div>
            </div>
        `;

        // Insert before the "Add Another Set" button
        e.target.insertAdjacentHTML('beforebegin', setHtml);

        // Re-attach event listeners
        this.attachEventListeners();
    }

    async handleCompleteWorkout() {
        if (!confirm('Complete this workout?')) return;

        try {
            const response = await this.apiRequest('POST', `/workouts/${this.sessionId}/complete`);

            if (response.success) {
                window.location.href = response.redirect;
            }
        } catch (error) {
            alert('Failed to complete workout: ' + error.message);
        }
    }

    // API methods
    async createWorkoutExercise(exerciseId, weightKg, numberReps, restSeconds, sort) {
        return await this.apiRequest('POST', `/workouts/${this.sessionId}/exercises`, {
            exercise_id: exerciseId,
            weight_kg: weightKg,
            number_reps: numberReps,
            rest_seconds: restSeconds,
            sort: sort,
        });
    }

    async addSetToExercise(workoutExerciseId, weightKg, numberReps) {
        return await this.apiRequest('POST',
            `/workouts/${this.sessionId}/exercises/${workoutExerciseId}/sets`, {
            weight_kg: weightKg,
            number_reps: numberReps,
        });
    }

    async updateSet(workoutExerciseId, setId, weightKg, numberReps) {
        return await this.apiRequest('PATCH',
            `/workouts/${this.sessionId}/exercises/${workoutExerciseId}/sets/${setId}`, {
            weight_kg: weightKg,
            number_reps: numberReps,
        });
    }

    async deleteSet(workoutExerciseId, setId) {
        return await this.apiRequest('DELETE',
            `/workouts/${this.sessionId}/exercises/${workoutExerciseId}/sets/${setId}`);
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
