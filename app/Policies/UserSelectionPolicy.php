<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserSelection;

class UserSelectionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Users can view their own selections, admins can view all
    }

    /**
     * Users can view their own selections or admins can view any
     */
    public function view(User $user, UserSelection $selection): bool
    {
        return $selection->user_id === $user->id
            || $user->can('View:UserSelection');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('student');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, UserSelection $selection): bool
    {
        return $user->can('Update:UserSelection');
    }

    /**
     * Users can withdraw their own pending selections
     */
    public function withdraw(User $user, UserSelection $selection): bool
    {
        return $selection->user_id === $user->id
            && $selection->isPending();
    }

    /**
     * Only admins can confirm enrollments
     */
    public function confirm(User $user, UserSelection $selection): bool
    {
        return $user->can('Confirm:UserSelection');
    }

    /**
     * Only admins can reject enrollments
     */
    public function reject(User $user, UserSelection $selection): bool
    {
        return $user->can('Reject:UserSelection');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, UserSelection $selection): bool
    {
        return $user->can('Delete:UserSelection');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, UserSelection $selection): bool
    {
        return $user->can('Restore:UserSelection');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, UserSelection $selection): bool
    {
        return $user->can('ForceDelete:UserSelection');
    }
}
