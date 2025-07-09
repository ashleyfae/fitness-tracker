document.addEventListener('DOMContentLoaded', () => {
    const deleteForms = document.querySelectorAll('.delete-form');

    if (deleteForms) {
        deleteForms.forEach(form => {
            const deleteMessage = form.getAttribute('data-message') || 'Are you sure you want to delete this item?';

            form.addEventListener('submit', e => {
                if (! confirm(deleteMessage)) {
                    e.preventDefault();
                }
            });
        })
    }
});
