export function formatExercise(exercise) {
    return `<div class="routine--exercise">
<div class="flex">
    <h2>${exercise.name}</h2>

    <div class="routine--exercise--field">
        <label for="exercise-${exercise.id}-sets">Number sets</label>
        <input type="number" id="exercise-${exercise.id}-sets" name="exercises[${exercise.id}][number_sets]" value="${exercise.number_sets || '3'}" min="1" max="200">
    </div>

    <div class="routine--exercise--field">
        <label for="exercise-${exercise.id}-rest">Rest (seconds)</label>
        <input type="number" id="exercise-${exercise.id}-rest" name="exercises[${exercise.id}][rest_seconds]" value="${exercise.rest_seconds || '60'}" min="1" max="1000">
    </div>

    <div class="routine--exercise--field">
        <label for="exercise-${exercise.id}-sort">Sort position</label>
        <input type="number" id="exercise-${exercise.id}-sort" name="exercises[${exercise.id}][sort]" value="${exercise.sort}" min="0">
    </div>
</div>
</div>`;
}
