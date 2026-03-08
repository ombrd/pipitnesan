<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PersonalTrainer;
use Illuminate\Auth\Access\HandlesAuthorization;

class PersonalTrainerPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_personal::trainer');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PersonalTrainer $personalTrainer): bool
    {
        return $user->can('view_personal::trainer');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_personal::trainer');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PersonalTrainer $personalTrainer): bool
    {
        return $user->can('update_personal::trainer');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PersonalTrainer $personalTrainer): bool
    {
        return $user->can('delete_personal::trainer');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_personal::trainer');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, PersonalTrainer $personalTrainer): bool
    {
        return $user->can('force_delete_personal::trainer');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_personal::trainer');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, PersonalTrainer $personalTrainer): bool
    {
        return $user->can('restore_personal::trainer');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_personal::trainer');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, PersonalTrainer $personalTrainer): bool
    {
        return $user->can('replicate_personal::trainer');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_personal::trainer');
    }
}
