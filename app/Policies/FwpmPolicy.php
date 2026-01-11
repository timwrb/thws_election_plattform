<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Fwpm;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class FwpmPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Fwpm');
    }

    public function view(AuthUser $authUser, Fwpm $fwpm): bool
    {
        return $authUser->can('View:Fwpm');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Fwpm');
    }

    public function update(AuthUser $authUser, Fwpm $fwpm): bool
    {
        return $authUser->can('Update:Fwpm');
    }

    public function delete(AuthUser $authUser, Fwpm $fwpm): bool
    {
        return $authUser->can('Delete:Fwpm');
    }

    public function restore(AuthUser $authUser, Fwpm $fwpm): bool
    {
        return $authUser->can('Restore:Fwpm');
    }

    public function forceDelete(AuthUser $authUser, Fwpm $fwpm): bool
    {
        return $authUser->can('ForceDelete:Fwpm');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Fwpm');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Fwpm');
    }

    public function replicate(AuthUser $authUser, Fwpm $fwpm): bool
    {
        return $authUser->can('Replicate:Fwpm');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Fwpm');
    }
}
