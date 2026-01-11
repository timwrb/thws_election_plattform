<?php

namespace App\Policies;

use App\Models\ResearchProject;
use App\Models\User;

class ResearchProjectPolicy
{
    /**
     * Students and admins can view all research projects
     */
    public function viewAny(User $user): bool
    {
        if ($user->hasRole('student')) {
            return true;
        }

        return $user->can('ViewAny:ResearchProject');
    }

    /**
     * Students and admins can view individual projects
     */
    public function view(User $user, ResearchProject $researchProject): bool
    {
        if ($user->hasRole('student')) {
            return true;
        }

        return $user->can('View:ResearchProject');
    }

    /**
     * Students and admins can create research projects
     */
    public function create(User $user): bool
    {
        if ($user->hasRole('student')) {
            return true;
        }

        return $user->can('Create:ResearchProject');
    }

    /**
     * Users can update projects they created, admins can update any
     */
    public function update(User $user, ResearchProject $researchProject): bool
    {
        if ($researchProject->isCreatedBy($user)) {
            return true;
        }

        return $user->can('Update:ResearchProject');
    }

    /**
     * Users can delete projects they created (if no enrollments), admins can delete any
     */
    public function delete(User $user, ResearchProject $researchProject): bool
    {
        if ($user->can('Delete:ResearchProject')) {
            return true;
        }

        // Students can only delete their own projects with no enrollments
        return $researchProject->isCreatedBy($user)
            && $researchProject->enrollments()->whereIn('status', ['pending', 'confirmed'])->count() === 0;
    }

    /**
     * Only admins can restore
     */
    public function restore(User $user, ResearchProject $researchProject): bool
    {
        return $user->can('Restore:ResearchProject');
    }

    /**
     * Only admins can force delete
     */
    public function forceDelete(User $user, ResearchProject $researchProject): bool
    {
        return $user->can('ForceDelete:ResearchProject');
    }

    /**
     * Students can enroll in projects with available capacity
     */
    public function enroll(User $user, ResearchProject $researchProject): bool
    {
        if (! $user->hasRole('student')) {
            return false;
        }

        // Get current semester (you may need to implement this logic based on your needs)
        $currentSemester = $researchProject->semester;

        if (! $currentSemester) {
            return false;
        }

        return $researchProject->hasCapacity($currentSemester)
            && ! $researchProject->isUserEnrolled($user, $currentSemester);
    }
}
