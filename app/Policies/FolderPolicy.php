<?php

namespace App\Policies;

use App\Models\Folder;
use App\Models\User;

class FolderPolicy
{
    /**
     * Determine if the user can view any folders.
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view folders
    }

    /**
     * Determine if the user can view the folder.
     */
    public function view(User $user, Folder $folder): bool
    {
        return $folder->userHasPermission($user, 'read');
    }

    /**
     * Determine if the user can create folders.
     */
    public function create(User $user): bool
    {
        return true; // All authenticated users can create folders
    }

    /**
     * Determine if the user can update the folder.
     */
    public function update(User $user, Folder $folder): bool
    {
        // Admin can update anything
        if ($user->isAdmin()) {
            return true;
        }

        // Owner can update
        if ($folder->owner_id === $user->id) {
            return true;
        }

        // Moderator for this level can update
        if ($user->canModerateLevel($folder->level_id)) {
            return true;
        }

        // Check write permission
        return $folder->userHasPermission($user, 'write');
    }

    /**
     * Determine if the user can delete the folder.
     */
    public function delete(User $user, Folder $folder): bool
    {
        // Admin can delete anything
        if ($user->isAdmin()) {
            return true;
        }

        // Owner can delete (soft delete only)
        if ($folder->owner_id === $user->id) {
            return true;
        }

        // Moderator for this level can delete
        if ($user->canModerateLevel($folder->level_id)) {
            return true;
        }

        // Check delete permission
        return $folder->userHasPermission($user, 'delete');
    }

    /**
     * Determine if the user can restore the folder.
     */
    public function restore(User $user, Folder $folder): bool
    {
        return $user->isAdmin() || $folder->owner_id === $user->id || $user->canModerateLevel($folder->level_id);
    }

    /**
     * Determine if the user can permanently delete the folder.
     */
    public function forceDelete(User $user, Folder $folder): bool
    {
        return $user->isAdmin(); // Only admins can permanently delete
    }

    /**
     * Determine if the user can manage permissions for the folder.
     */
    public function managePermissions(User $user, Folder $folder): bool
    {
        // Admin can manage permissions
        if ($user->isAdmin()) {
            return true;
        }

        // Owner can manage permissions
        if ($folder->owner_id === $user->id) {
            return true;
        }

        // Moderator for this level can manage permissions
        return $user->canModerateLevel($folder->level_id);
    }
}
