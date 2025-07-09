document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('create-workout');
    if (! form) {
        return;
    }

    const routines = form.querySelectorAll('.workout-routine');
    if (! routines) {
        return;
    }

    /**
     * Handle selecting a routine
     * @param selectedRoutine
     */
    const handleRoutineSelection = (selectedRoutine) => {
        // Remove the "selected" class from all routines
        routines.forEach(routine => {
            routine.classList.remove('selected');
            // Uncheck radio input
            const radio = routine.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = false;
            }
        });

        // Add the "selected" class to the clicked routine
        selectedRoutine.classList.add('selected');
        // Check the radio input inside selected routine
        const selectedRadio = selectedRoutine.querySelector('input[type="radio"]');
        if (selectedRadio) {
            selectedRadio.checked = true;
        }
    };

    const maybeShowSubmitButton = () => {
        const submitButtonWrapper = document.getElementById('create-workout-submit');
        if (submitButtonWrapper) {
            submitButtonWrapper.classList.remove('hidden');
        }
    }

    routines.forEach(routine => {
        routine.addEventListener('click', e => {
            handleRoutineSelection(routine);
            maybeShowSubmitButton();
        })
    })
})
