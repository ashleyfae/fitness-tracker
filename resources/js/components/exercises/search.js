import {formatExercise} from "../routines/formatting";

document.addEventListener('DOMContentLoaded', () => {
    const wrapper = document.getElementById('search-exercises');

    if (wrapper) {
        loadExercises(wrapper, null);
    }

    const searchInput = document.getElementById('exercise-search-input');
    if (searchInput) {
        searchInput.addEventListener('keydown', e => {
            loadExercises(wrapper, searchInput.value);
        })
    }
});

/**
 * Loads exercises
 *
 * @param {HTMLElement} wrapper
 * @param {String|null} search
 */
function loadExercises(wrapper, search) {
    const url = new URL(wrapper.getAttribute('data-route'));

    if (search) {
        url.searchParams.set('search', search);
    }

    axios.get(url.toString())
        .then(res => {
            displayExercises(res.data);
        })
        .catch(error => {
            console.log('Error loading exercises', error);
        });
}

function displayExercises(data) {
    const wrapper = document.getElementById('search-exercises-list');

    if (! wrapper) {
        return;
    }

    let html = '';

    if (data.data.length > 0) {
        data.data.forEach(exercise => {
            html += formatExerciseForSearch(exercise);
        })
    } else {
        html = '<div class="notification">No exercises found.</div>';
    }

    wrapper.innerHTML = html;

    // Add click handlers to all exercise buttons
    wrapper.querySelectorAll('.exercise--search-result button').forEach(button => {
        button.addEventListener('click', handleExerciseClick);
    });
}

function formatExerciseForSearch(exercise) {
    return `<div class="exercise--search-result mb-2">
<button type="button" data-id="${exercise.id}" data-name="${exercise.name}">${exercise.name}</button>
</div>`;
}

function handleExerciseClick(event) {
    const button = event.currentTarget;
    const exerciseId = button.getAttribute('data-id');
    const exerciseName = button.getAttribute('data-name');

    // Create exercise object to match the format expected by formatExercise
    const exercise = {
        id: exerciseId,
        name: exerciseName,
        number_sets: 3,
        rest_seconds: 60,
        sort: 0,
    };

    // Get the exercise list wrapper
    const exerciseList = document.getElementById('exercise-list');

    // Only proceed if we found the exercise list
    if (exerciseList) {
        // Create a temporary div to hold the new exercise HTML
        const temp = document.createElement('div');
        temp.innerHTML = formatExercise(exercise);

        // If there's a "No exercises yet" message, remove it first
        const noExercisesMsg = exerciseList.querySelector('.notification');
        if (noExercisesMsg) {
            exerciseList.innerHTML = '';
        }

        // Append the new exercise to the list
        exerciseList.appendChild(temp.firstElementChild);

        // Update the exercise count
        const currentCount = parseInt(exerciseList.getAttribute('data-exercise-count') || '0');
        exerciseList.setAttribute('data-exercise-count', (currentCount + 1).toString());

        // Close the modal
        const modal = document.getElementById('add-exercise-modal');
        if (modal) {
            modal.classList.remove('is-active');
        }
    }
}
