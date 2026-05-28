export function formatExercise(exercise) {
    return `<div class="routine--exercise">
<h2>${exercise.name}</h2>

<div class="routine--exercise--settings">
    <div class="routine--exercise--field">
        <label for="exercise-${exercise.id}-sets">Number sets</label>
        <input type="number" id="exercise-${exercise.id}-sets" name="exercises[${exercise.id}][number_sets]" value="${exercise.pivot?.number_sets || '3'}" min="1" max="200">
    </div>

    <div class="routine--exercise--field">
        <label for="exercise-${exercise.id}-rest">Rest (seconds)</label>
        <input type="number" id="exercise-${exercise.id}-rest" name="exercises[${exercise.id}][rest_seconds]" value="${exercise.pivot?.rest_seconds || '60'}" min="1" max="1000">
    </div>

    <div class="routine--exercise--field">
        <label for="exercise-${exercise.id}-sort">Sort position</label>
        <input type="number" id="exercise-${exercise.id}-sort" name="exercises[${exercise.id}][sort]" value="${exercise.pivot?.sort}" min="0">
    </div>
</div>
<div class="routine--exercise--remove-wrapper text-right mt-1">
    <button type="button" class="small routine--exercise--remove">Remove</button>
</div>
</div>`;
}
