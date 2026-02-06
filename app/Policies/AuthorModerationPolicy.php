<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\AuthorModeration;
use Illuminate\Auth\Access\HandlesAuthorization;

class AuthorModerationPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AuthorModeration');
    }

    public function view(AuthUser $authUser, AuthorModeration $authorModeration): bool
    {
        return $authUser->can('View:AuthorModeration');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AuthorModeration');
    }

    public function update(AuthUser $authUser, AuthorModeration $authorModeration): bool
    {
        return $authUser->can('Update:AuthorModeration');
    }

    public function delete(AuthUser $authUser, AuthorModeration $authorModeration): bool
    {
        return $authUser->can('Delete:AuthorModeration');
    }

    public function restore(AuthUser $authUser, AuthorModeration $authorModeration): bool
    {
        return $authUser->can('Restore:AuthorModeration');
    }

    public function forceDelete(AuthUser $authUser, AuthorModeration $authorModeration): bool
    {
        return $authUser->can('ForceDelete:AuthorModeration');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AuthorModeration');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AuthorModeration');
    }

    public function replicate(AuthUser $authUser, AuthorModeration $authorModeration): bool
    {
        return $authUser->can('Replicate:AuthorModeration');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AuthorModeration');
    }

}