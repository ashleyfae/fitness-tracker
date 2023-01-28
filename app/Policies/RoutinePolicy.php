<?php

namespace App\Policies;

use App\Models\Routine;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RoutinePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Routine  $routine
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Routine $routine)
    {
        return $user->is($routine->user);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Routine  $routine
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Routine $routine)
    {
        return $user->is($routine->user);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Routine  $routine
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Routine $routine)
    {
        return $user->is($routine->user);
    }
}
