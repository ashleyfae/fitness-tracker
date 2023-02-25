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
}

function formatExerciseForSearch(exercise) {
    return `<div class="exercise--search-result mb-2">
<button type="button" data-id="${exercise.id}">${exercise.name}</button>
</div>`;
}
