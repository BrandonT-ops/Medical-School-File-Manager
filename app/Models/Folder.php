<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Folder extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'parent_id',
        'level_id',
        'owner_id',
        'permissions',
        'description',
        'is_public',
        'public_permissions',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'permissions' => 'array',
        'deleted_at' => 'datetime',
        'is_public' => 'boolean',
        'public_permissions' => 'array',
    ];

    /**
     * Get the parent folder
     */
    public function parent()
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    /**
     * Get child folders
     */
    public function children()
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    /**
     * Get all descendants (recursive)
     */
    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get the level this folder belongs to
     */
    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    /**
     * Get the owner of this folder
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get files in this folder
     */
    public function files()
    {
        return $this->hasMany(FileItem::class);
    }

    /**
     * Get full path breadcrumb
     */
    public function getBreadcrumb()
    {
        $breadcrumb = [$this];
        $current = $this;

        while ($current->parent) {
            $current = $current->parent;
            array_unshift($breadcrumb, $current);
        }

        return $breadcrumb;
    }

    /**
     * Check if user has permission
     */
    public function userHasPermission(User $user, string $permission = 'read'): bool
    {
        // Admin has all permissions
        if ($user->isAdmin()) {
            return true;
        }

        // Owner has all permissions
        if ($this->owner_id === $user->id) {
            return true;
        }

        // Moderator for this level
        if ($user->canModerateLevel($this->level_id)) {
            return true;
        }

        // Public folder - check public permissions
        if ($this->is_public) {
            $publicPerms = $this->public_permissions ?? ['read']; // Default: read-only
            if (in_array($permission, $publicPerms)) {
                return true;
            }
        }

        // Check explicit permissions
        $permissions = $this->permissions ?? [];
        if (isset($permissions[$user->id])) {
            return in_array($permission, $permissions[$user->id]);
        }

        return false;
    }

    /**
     * Check if folder is publicly accessible
     */
    public function isPublic(): bool
    {
        return $this->is_public;
    }

    /**
     * Get public permissions
     */
    public function getPublicPermissions(): array
    {
        return $this->public_permissions ?? ['read'];
    }

    /**
     * Set public permissions
     */
    public function setPublicPermissions(array $permissions)
    {
        $this->public_permissions = $permissions;
        $this->save();
    }

    /**
     * Grant permission to user
     */
    public function grantPermission(User $user, array $permissions)
    {
        $currentPermissions = $this->permissions ?? [];
        $currentPermissions[$user->id] = $permissions;
        $this->permissions = $currentPermissions;
        $this->save();
    }

    /**
     * Revoke permission from user
     */
    public function revokePermission(User $user)
    {
        $currentPermissions = $this->permissions ?? [];
        unset($currentPermissions[$user->id]);
        $this->permissions = $currentPermissions;
        $this->save();
    }
}
