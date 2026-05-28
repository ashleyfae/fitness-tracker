import {formatExercise} from "./formatting";
import {closeModal} from "../../layout/modals";

document.addEventListener('exercise:selected', (e) => {
    const exerciseList = document.getElementById('exercise-list');
    if (! exerciseList) return;

    const { id, name } = e.detail;

    const exercise = {
        id,
        name,
        pivot: {
            number_sets: 3,
            rest_seconds: 60,
            sort: getMaxSortValue(exerciseList) + 1,
        }
    };

    const temp = document.createElement('div');
    temp.innerHTML = formatExercise(exercise);

    const noExercisesMsg = exerciseList.querySelector('.notification');
    if (noExercisesMsg) {
        exerciseList.innerHTML = '';
    }

    exerciseList.appendChild(temp.firstElementChild);

    const currentCount = parseInt(exerciseList.getAttribute('data-exercise-count') || '0');
    exerciseList.setAttribute('data-exercise-count', (currentCount + 1).toString());

    updateSortValues();
    updateReorderButtons();

    const modal = document.getElementById('add-exercise-modal');
    if (modal) {
        closeModal(modal);
    }
});

function getMaxSortValue(exerciseList) {
    const existingExercises = exerciseList.querySelectorAll('.routine--exercise');
    let maxSort = -1;

    existingExercises.forEach(exerciseDiv => {
        const sortInput = exerciseDiv.querySelector('input[name$="[sort]"]');
        if (sortInput) {
            const sortValue = parseInt(sortInput.value);
            maxSort = Math.max(maxSort, sortValue);
        }
    });

    return maxSort;
}

document.addEventListener('DOMContentLoaded', () => {
    const wrapper = document.getElementById('exercise-list');

    if (wrapper) {
        loadExercises(wrapper);
    }

    document.addEventListener('click', e => {
        if (e.target.classList.contains('routine--exercise--remove')) {
            e.target.closest('.routine--exercise').remove();
            updateReorderButtons();
        }

        if (e.target.classList.contains('routine--exercise--move-up')) {
            const exercise = e.target.closest('.routine--exercise');
            const prev = exercise.previousElementSibling;
            if (prev) {
                exercise.parentNode.insertBefore(exercise, prev);
                updateSortValues();
                updateReorderButtons();
            }
        }

        if (e.target.classList.contains('routine--exercise--move-down')) {
            const exercise = e.target.closest('.routine--exercise');
            const next = exercise.nextElementSibling;
            if (next) {
                exercise.parentNode.insertBefore(next, exercise);
                updateSortValues();
                updateReorderButtons();
            }
        }
    });
});

/**
 * Loads exercises.
 *
 * @param {HTMLElement} wrapper
 */
function loadExercises(wrapper) {
    axios.get(wrapper.getAttribute('data-get'))
        .then(res => {
            let html = '';
            let exerciseCount = 0;
            if (res.data.exercises.length === 0) {
                html = '<div class="notification">No exercises yet</div>';
            } else {
                res.data.exercises.forEach(exercise => {
                    html += formatExercise(exercise);
                });

                exerciseCount = res.data.exercises.length;
            }

            wrapper.innerHTML = html;
            wrapper.setAttribute('data-exercise-count', exerciseCount.toString());
            updateReorderButtons();
        })
        .catch(error => {
            console.log('Error getting exercises', error);
        });
}

export function updateSortValues() {
    const exercises = document.querySelectorAll('#exercise-list .routine--exercise');
    exercises.forEach((exercise, index) => {
        const sortInput = exercise.querySelector('input[name$="[sort]"]');
        if (sortInput) {
            sortInput.value = index;
        }
    });
}

export function updateReorderButtons() {
    const exercises = Array.from(document.querySelectorAll('#exercise-list .routine--exercise'));
    exercises.forEach((exercise, index) => {
        const upBtn = exercise.querySelector('.routine--exercise--move-up');
        const downBtn = exercise.querySelector('.routine--exercise--move-down');
        if (upBtn) {
            upBtn.disabled = index === 0;
        }
        if (downBtn) {
            downBtn.disabled = index === exercises.length - 1;
        }
    });
}
