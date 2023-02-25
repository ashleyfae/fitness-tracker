document.addEventListener('DOMContentLoaded', () => {
    const deleteForms = document.querySelectorAll('.delete-exercise');

    if (deleteForms) {
        deleteForms.forEach(form => {
            form.addEventListener('submit', e => {
                if (! confirm('Are you sure you want to delete this exercise?')) {
                    e.preventDefault();
                }
            });
        })
    }
});
