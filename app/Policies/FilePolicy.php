<?php

namespace App\Policies;

use App\Models\FileItem;
use App\Models\User;

class FilePolicy
{
    /**
     * Determine if the user can view any files.
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view files
    }

    /**
     * Determine if the user can view the file.
     */
    public function view(User $user, FileItem $file): bool
    {
        return $file->userCanAccess($user);
    }

    /**
     * Determine if the user can create files.
     */
    public function create(User $user): bool
    {
        return true; // All authenticated users can upload files
    }

    /**
     * Determine if the user can update the file.
     */
    public function update(User $user, FileItem $file): bool
    {
        // Admin can update anything
        if ($user->isAdmin()) {
            return true;
        }

        // Owner can update
        if ($file->owner_id === $user->id) {
            return true;
        }

        // Moderator for this level can update
        if ($user->canModerateLevel($file->level_id)) {
            return true;
        }

        // Check folder permissions if file is in a folder
        if ($file->folder) {
            return $file->folder->userHasPermission($user, 'write');
        }

        return false;
    }

    /**
     * Determine if the user can delete the file.
     */
    public function delete(User $user, FileItem $file): bool
    {
        // Admin can delete anything
        if ($user->isAdmin()) {
            return true;
        }

        // Owner can delete (soft delete only)
        if ($file->owner_id === $user->id) {
            return true;
        }

        // Moderator for this level can delete
        if ($user->canModerateLevel($file->level_id)) {
            return true;
        }

        // Check folder permissions if file is in a folder
        if ($file->folder) {
            return $file->folder->userHasPermission($user, 'delete');
        }

        return false;
    }

    /**
     * Determine if the user can restore the file.
     */
    public function restore(User $user, FileItem $file): bool
    {
        return $user->isAdmin() || $file->owner_id === $user->id || $user->canModerateLevel($file->level_id);
    }

    /**
     * Determine if the user can permanently delete the file.
     */
    public function forceDelete(User $user, FileItem $file): bool
    {
        return $user->isAdmin(); // Only admins can permanently delete
    }

    /**
     * Determine if the user can download the file.
     */
    public function download(User $user, FileItem $file): bool
    {
        return $file->userCanAccess($user);
    }
}
