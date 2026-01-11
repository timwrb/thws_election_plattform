<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Awpf;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class AwpfPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Awpf');
    }

    public function view(AuthUser $authUser, Awpf $awpf): bool
    {
        return $authUser->can('View:Awpf');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Awpf');
    }

    public function update(AuthUser $authUser, Awpf $awpf): bool
    {
        return $authUser->can('Update:Awpf');
    }

    public function delete(AuthUser $authUser, Awpf $awpf): bool
    {
        return $authUser->can('Delete:Awpf');
    }

    public function restore(AuthUser $authUser, Awpf $awpf): bool
    {
        return $authUser->can('Restore:Awpf');
    }

    public function forceDelete(AuthUser $authUser, Awpf $awpf): bool
    {
        return $authUser->can('ForceDelete:Awpf');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Awpf');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Awpf');
    }

    public function replicate(AuthUser $authUser, Awpf $awpf): bool
    {
        return $authUser->can('Replicate:Awpf');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Awpf');
    }
}
