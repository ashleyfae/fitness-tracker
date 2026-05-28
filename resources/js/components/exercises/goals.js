import {closeModal} from "../../layout/modals";

document.addEventListener('DOMContentLoaded', () => {
    const wrapper = document.getElementById('exercise-goals');
    if (!wrapper) return;

    const form = document.getElementById('goal-form');

    // Track which goal is being edited (null = create mode)
    let editingGoalId = null;

    document.getElementById('add-goal-btn').addEventListener('click', () => {
        editingGoalId = null;
        document.getElementById('goal-modal-title').textContent = 'Add Goal';
        form.reset();
        openGoalModal();
    });

    wrapper.addEventListener('click', e => {
        const goalEl = e.target.closest('.exercise-goal');
        if (!goalEl) return;

        if (e.target.classList.contains('exercise-goal--edit')) {
            editingGoalId = goalEl.dataset.id;
            document.getElementById('goal-modal-title').textContent = 'Edit Goal';
            document.getElementById('goal-sets').value = goalEl.dataset.sets;
            document.getElementById('goal-weight').value = goalEl.dataset.weight;
            document.getElementById('goal-reps').value = goalEl.dataset.reps;
            openGoalModal();
        }

        if (e.target.classList.contains('exercise-goal--delete')) {
            deleteGoal(goalEl);
        }

        if (e.target.classList.contains('exercise-goal--move-up')) {
            reorderGoal(goalEl, 'up');
        }

        if (e.target.classList.contains('exercise-goal--move-down')) {
            reorderGoal(goalEl, 'down');
        }
    });

    form.addEventListener('submit', e => {
        e.preventDefault();

        const data = {
            target_sets: document.getElementById('goal-sets').value,
            target_weight_kg: document.getElementById('goal-weight').value,
            target_reps: document.getElementById('goal-reps').value,
        };

        if (editingGoalId) {
            updateGoal(editingGoalId, data);
        } else {
            createGoal(data);
        }
    });

    updateReorderButtons();
});

function openGoalModal() {
    const modal = document.getElementById('goal-modal');
    modal.classList.add('is-active');
    document.documentElement.classList.add('is-clipped');
    const first = modal.querySelector('input:not([type="hidden"])');
    if (first) first.focus();
}

function storeUrl() {
    return document.getElementById('exercise-goals').dataset.storeUrl;
}

function goalUrl(goalId) {
    return `${storeUrl()}/${goalId}`;
}

function createGoal(data) {
    axios.post(storeUrl(), data)
        .then(res => {
            appendGoal(res.data);
            closeModal(document.getElementById('goal-modal'));
            updateReorderButtons();
        })
        .catch(err => console.error('Error creating goal', err));
}

function updateGoal(goalId, data) {
    axios.patch(goalUrl(goalId), data)
        .then(res => {
            const goalEl = document.querySelector(`.exercise-goal[data-id="${goalId}"]`);
            if (goalEl) syncGoalElement(goalEl, res.data);
            closeModal(document.getElementById('goal-modal'));
        })
        .catch(err => console.error('Error updating goal', err));
}

function deleteGoal(goalEl) {
    axios.delete(goalUrl(goalEl.dataset.id))
        .then(() => {
            goalEl.remove();
            updateReorderButtons();

            const wrapper = document.getElementById('exercise-goals');
            if (!wrapper.querySelector('.exercise-goal') && !document.getElementById('no-goals-message')) {
                const p = document.createElement('p');
                p.id = 'no-goals-message';
                p.className = 'notification';
                p.textContent = 'No goals yet.';
                wrapper.prepend(p);
            }
        })
        .catch(err => console.error('Error deleting goal', err));
}

function reorderGoal(goalEl, direction) {
    axios.patch(`${goalUrl(goalEl.dataset.id)}/reorder`, {direction})
        .then(() => {
            const wrapper = document.getElementById('exercise-goals');
            if (direction === 'up') {
                const prev = goalEl.previousElementSibling;
                if (prev) wrapper.insertBefore(goalEl, prev);
            } else {
                const next = goalEl.nextElementSibling;
                if (next) wrapper.insertBefore(next, goalEl);
            }
            updateReorderButtons();
        })
        .catch(err => console.error('Error reordering goal', err));
}

function appendGoal(goal) {
    const wrapper = document.getElementById('exercise-goals');

    const noGoals = document.getElementById('no-goals-message');
    if (noGoals) noGoals.remove();

    const el = document.createElement('div');
    el.className = 'exercise-goal';
    el.dataset.id = goal.id;
    el.dataset.sets = goal.target_sets;
    el.dataset.weight = goal.target_weight_kg;
    el.dataset.reps = goal.target_reps;
    el.innerHTML = goalInnerHtml(goal);
    wrapper.appendChild(el);
}

function syncGoalElement(el, goal) {
    el.dataset.sets = goal.target_sets;
    el.dataset.weight = goal.target_weight_kg;
    el.dataset.reps = goal.target_reps;
    el.querySelector('.exercise-goal--summary').textContent =
        `${goal.target_sets} sets × ${goal.target_weight_kg}kg × ${goal.target_reps} reps`;
}

function goalInnerHtml(goal) {
    return `<div class="exercise-goal--summary">${goal.target_sets} sets × ${goal.target_weight_kg}kg × ${goal.target_reps} reps</div>
<div class="exercise-goal--actions">
    <button type="button" class="small exercise-goal--move-up" aria-label="Move up">&#9650;</button>
    <button type="button" class="small exercise-goal--move-down" aria-label="Move down">&#9660;</button>
    <button type="button" class="small exercise-goal--edit">Edit</button>
    <button type="button" class="small danger exercise-goal--delete">Delete</button>
</div>`;
}

function updateReorderButtons() {
    const goals = Array.from(document.querySelectorAll('#exercise-goals .exercise-goal'));
    goals.forEach((goal, index) => {
        const upBtn = goal.querySelector('.exercise-goal--move-up');
        const downBtn = goal.querySelector('.exercise-goal--move-down');
        if (upBtn) upBtn.disabled = index === 0;
        if (downBtn) downBtn.disabled = index === goals.length - 1;
    });
}
