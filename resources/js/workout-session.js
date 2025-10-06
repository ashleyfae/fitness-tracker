import { RestTimer } from './rest-timer.js';

/**
 * Workout Session AJAX Handler
 */
class WorkoutSession {
    constructor() {
        this.sessionId = document.getElementById('workout-session').dataset.sessionId;
        this.csrfToken = document.getElementById('workout-session').dataset.csrf;
        this.restTimer = new RestTimer();
        this.lastCompletedSetDiv = null; // Track last completed set for "next" calculation
        this.init();
    }

    init() {
        this.attachEventListeners();
        this.checkRestTimer();
        this.updateSetClasses(); // Initialize set classes on page load
    }

    checkRestTimer() {
        const timerData = localStorage.getItem('restTimer');
        if (timerData) {
            const { seconds, exerciseName } = JSON.parse(timerData);
            localStorage.removeItem('restTimer');
            this.restTimer.start(seconds, exerciseName);
        }
    }

    shouldStartTimer(exerciseDiv, setDiv) {
        const expectedSets = parseInt(exerciseDiv.dataset.expectedSets);
        const currentSetIndex = parseInt(setDiv.dataset.setIndex);
        const exerciseSort = parseInt(exerciseDiv.dataset.sort);
        const totalExercises = parseInt(document.getElementById('workout-session').dataset.totalExercises);

        // Don't start timer if this is the last set of the last exercise
        const isLastSet = currentSetIndex >= expectedSets;
        const isLastExercise = exerciseSort >= totalExercises;

        return !(isLastSet && isLastExercise);
    }

    attachEventListeners() {
        // Use event delegation on the workout-session container
        const container = document.getElementById('workout-session');

        container.addEventListener('click', (e) => {
            if (e.target.classList.contains('add-set')) {
                this.handleAddSet(e);
            } else if (e.target.classList.contains('save-set')) {
                this.handleSaveSet(e);
            } else if (e.target.classList.contains('delete-set')) {
                this.handleDeleteSet(e);
            } else if (e.target.classList.contains('add-extra-set')) {
                this.handleAddExtraSet(e);
            }
        });

        // Focus handling for inputs in incomplete sets
        container.addEventListener('focusin', (e) => {
            if (e.target.matches('.set--incomplete input')) {
                const setDiv = e.target.closest('.set');
                // Temporarily mark this as the "next" set
                document.querySelectorAll('.set--next').forEach(s => s.classList.remove('set--next'));
                setDiv.classList.add('set--next');
            }
        });

        container.addEventListener('focusout', (e) => {
            if (e.target.matches('.set--incomplete input')) {
                // Restore logical "next" set after a short delay
                // (delay allows clicking between inputs in same set without flicker)
                setTimeout(() => {
                    if (!container.contains(document.activeElement) ||
                        !document.activeElement.matches('.set--incomplete input')) {
                        this.updateSetClasses();
                    }
                }, 50);
            }
        });

        // Complete workout button
        document.getElementById('complete-workout').addEventListener('click', () => {
            this.handleCompleteWorkout();
        });

        // Skip rest button
        document.getElementById('skip-rest').addEventListener('click', () => {
            this.restTimer.stop();
        });
    }

    async handleAddSet(e) {
        const button = e.target;

        // Prevent double-clicks
        if (button.disabled) return;
        button.disabled = true;

        const setDiv = e.target.closest('.set');
        const exerciseDiv = e.target.closest('.exercise');
        const weightInput = setDiv.querySelector('.set-weight');
        const repsInput = setDiv.querySelector('.set-reps');

        const weight = parseFloat(weightInput.value);
        const reps = parseInt(repsInput.value);

        if (weightInput.value === '' || !reps) {
            alert('Please enter both weight and reps');
            button.disabled = false;
            return;
        }

        const exerciseId = exerciseDiv.dataset.exerciseId;
        const workoutExerciseId = exerciseDiv.dataset.workoutExerciseId;
        const restSeconds = exerciseDiv.dataset.restSeconds;
        const sort = exerciseDiv.dataset.sort;

        try {
            let response;
            // Determine if this is the first set or additional set
            if (!workoutExerciseId) {
                // First set - create workout exercise and set
                response = await this.createWorkoutExercise(exerciseId, weight, reps, restSeconds, sort);
                // Update exercise div with new workout_exercise_id
                exerciseDiv.dataset.workoutExerciseId = response.workout_exercise_id;
            } else {
                // Additional set - just add the set
                response = await this.addSetToExercise(workoutExerciseId, weight, reps);
            }

            const newSet = response.set;

            // Transform the set div from "empty" to "completed" state
            this.convertSetToCompleted(setDiv, newSet);

            // Track this as the last completed set
            this.lastCompletedSetDiv = setDiv;

            // Update CSS classes for all sets on the page
            this.updateSetClasses();

            // Start rest timer if not last set of last exercise
            if (this.shouldStartTimer(exerciseDiv, setDiv)) {
                const exerciseName = exerciseDiv.querySelector('h2').textContent;
                this.restTimer.start(parseInt(restSeconds), exerciseName);
            }
        } catch (error) {
            alert('Failed to add set: ' + error.message);
            button.disabled = false;
        }
    }

    convertSetToCompleted(setDiv, setData) {
        // Add set ID to div
        setDiv.dataset.setId = setData.id;

        // Replace the inner HTML with completed state
        const fieldsDiv = setDiv.querySelector('.set--fields');
        fieldsDiv.innerHTML = this.generateCompletedSetFieldsHtml(setData);

        // Add completed class
        setDiv.classList.remove('set--incomplete', 'set--next');
        setDiv.classList.add('set--complete');
    }

    updateSetClasses(exerciseDiv = null) {
        // Get all sets on the page in DOM order
        const allSets = Array.from(document.querySelectorAll('.set'));

        // Find the next incomplete set based on last completed set
        let nextSetDiv = null;

        if (this.lastCompletedSetDiv && allSets.includes(this.lastCompletedSetDiv)) {
            // Find first incomplete set after the last completed one
            const lastCompletedIndex = allSets.indexOf(this.lastCompletedSetDiv);
            for (let i = lastCompletedIndex + 1; i < allSets.length; i++) {
                if (!allSets[i].hasAttribute('data-set-id')) {
                    nextSetDiv = allSets[i];
                    break;
                }
            }
        }

        // Fallback: if no last completed set or no incomplete sets after it,
        // use the first incomplete set on the page
        if (!nextSetDiv) {
            nextSetDiv = allSets.find(set => !set.hasAttribute('data-set-id'));
        }

        // Update all sets
        allSets.forEach((setDiv) => {
            const isCompleted = setDiv.hasAttribute('data-set-id');

            // Remove all state classes first
            setDiv.classList.remove('set--complete', 'set--incomplete', 'set--next');

            if (isCompleted) {
                setDiv.classList.add('set--complete');
            } else {
                setDiv.classList.add('set--incomplete');
                // Mark as next if this is the determined next set
                if (setDiv === nextSetDiv) {
                    setDiv.classList.add('set--next');
                }
            }
        });
    }

    generateCompletedSetFieldsHtml(setData) {
        return `
            <div class="set--field-group">
                <input type="number"
                       class="set-weight"
                       data-set-id="${setData.id}"
                       value="${setData.weight_kg}"
                       step="0.5"
                       placeholder="Weight (kg)">
                <span>kg</span>
            </div>
            <div class="set--field-group">
                <input type="number"
                       class="set-reps"
                       data-set-id="${setData.id}"
                       value="${setData.number_reps}"
                       placeholder="Reps">
                <span>reps</span>
            </div>
            <button class="save-set" data-set-id="${setData.id}" aria-label="Save set">&#10003;</button>
            <button class="delete-set" data-set-id="${setData.id}" aria-label="Delete set">&times;</button>
        `;
    }

    async handleSaveSet(e) {
        const button = e.target;

        // Prevent double-clicks
        if (button.disabled) return;
        button.disabled = true;

        const setDiv = e.target.closest('.set');
        const exerciseDiv = e.target.closest('.exercise');
        const setId = e.target.dataset.setId;
        const weightInput = setDiv.querySelector(`.set-weight[data-set-id="${setId}"]`);
        const repsInput = setDiv.querySelector(`.set-reps[data-set-id="${setId}"]`);

        const weight = parseFloat(weightInput.value);
        const reps = parseInt(repsInput.value);

        if (weightInput.value === '' || !reps) {
            alert('Please enter both weight and reps');
            button.disabled = false;
            return;
        }

        const workoutExerciseId = exerciseDiv.dataset.workoutExerciseId;

        try {
            await this.updateSet(workoutExerciseId, setId, weight, reps);

            // Start rest timer after saving (no reload for saves)
            if (this.shouldStartTimer(exerciseDiv, setDiv)) {
                const exerciseName = exerciseDiv.querySelector('h2').textContent;
                const restSeconds = exerciseDiv.dataset.restSeconds;
                this.restTimer.start(parseInt(restSeconds), exerciseName);
            }

            alert('Set updated!');
        } catch (error) {
            alert('Failed to update set: ' + error.message);
        } finally {
            button.disabled = false;
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

            // Update CSS classes for all sets on the page
            this.updateSetClasses();
        } catch (error) {
            alert('Failed to delete set: ' + error.message);
        }
    }

    async handleAddExtraSet(e) {
        const exerciseDiv = e.target.closest('.exercise');
        const setsContainer = exerciseDiv.querySelector('.sets-container');
        const currentSets = setsContainer.querySelectorAll('.set').length;
        const nextSetIndex = currentSets + 1;

        // Add new empty set to DOM
        const setHtml = `
            <div class="set set--incomplete" data-set-index="${nextSetIndex}">
                <div class="set--number">Set ${nextSetIndex}</div>
                <div class="set--fields">
                    <div class="set--field-group">
                        <input type="number"
                               class="set-weight"
                               step="0.5"
                               placeholder="Weight (kg)">
                        <span>kg</span>
                    </div>
                    <div class="set--field-group">
                        <input type="number"
                               class="set-reps"
                               placeholder="Reps">
                        <span>reps</span>
                    </div>
                    <button class="add-set" aria-label="Add set">&#10003;</button>
                    <button class="dummy-delete-set">&times;</button>
                </div>
            </div>
        `;

        // Insert before the wrapper (not the button)
        const wrapper = setsContainer.querySelector('.add-extra-set-wrap');
        wrapper.insertAdjacentHTML('beforebegin', setHtml);

        // Update CSS classes for all sets on the page
        this.updateSetClasses();
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
