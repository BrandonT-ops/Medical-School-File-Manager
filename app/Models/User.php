<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'moderated_levels',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'moderated_levels' => 'array',
    ];

    /**
     * Get folders owned by this user
     */
    public function folders()
    {
        return $this->hasMany(Folder::class, 'owner_id');
    }

    /**
     * Get files owned by this user
     */
    public function files()
    {
        return $this->hasMany(FileItem::class, 'owner_id');
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is moderator
     */
    public function isModerator(): bool
    {
        return $this->role === 'moderator';
    }

    /**
     * Check if user can moderate a specific level
     */
    public function canModerateLevel($levelId): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if (!$this->isModerator()) {
            return false;
        }

        return in_array($levelId, $this->moderated_levels ?? []);
    }

    /**
     * Check if user can access a folder
     */
    public function canAccessFolder(Folder $folder): bool
    {
        // Admin can access everything
        if ($this->isAdmin()) {
            return true;
        }

        // Owner can access
        if ($folder->owner_id === $this->id) {
            return true;
        }

        // Moderator for this level
        if ($this->canModerateLevel($folder->level_id)) {
            return true;
        }

        // Check permissions
        $permissions = $folder->permissions ?? [];
        return isset($permissions[$this->id]);
    }
}
