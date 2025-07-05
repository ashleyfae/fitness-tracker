document.addEventListener('DOMContentLoaded', () => {
    const wrapper = document.getElementById('exercise-list');

    if (wrapper) {
        loadExercises(wrapper);
    }
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
        })
        .catch(error => {
            console.log('Error getting exercises', error);
        })
}

function formatExercise(exercise) {
    return `<div class="routine--exercise">
<h2>${exercise.name}</h2>
<input type="hidden" name="exercises[]" value="${exercise.id}">
</div>`;
}
