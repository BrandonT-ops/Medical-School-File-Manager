<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileItem extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'folder_id',
        'level_id',
        'filename',
        'original_filename',
        'filepath',
        'extension',
        'mime_type',
        'size',
        'owner_id',
        'is_archived',
        'storage_provider',
        'external_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_archived' => 'boolean',
        'size' => 'integer',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the folder this file belongs to
     */
    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }

    /**
     * Get the level this file belongs to
     */
    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    /**
     * Get the owner of this file
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get human-readable file size
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Check if file is an image
     */
    public function isImage(): bool
    {
        return in_array($this->extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp']);
    }

    /**
     * Check if file is a PDF
     */
    public function isPdf(): bool
    {
        return $this->extension === 'pdf';
    }

    /**
     * Check if file is a video
     */
    public function isVideo(): bool
    {
        return in_array($this->extension, ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv', 'webm']);
    }

    /**
     * Check if file can be previewed
     */
    public function canPreview(): bool
    {
        return $this->isImage() || $this->isPdf();
    }

    /**
     * Archive this file (soft delete)
     */
    public function archive()
    {
        $this->is_archived = true;
        $this->save();
        $this->delete(); // Soft delete
    }

    /**
     * Restore from archive
     */
    public function unarchive()
    {
        $this->restore(); // Restore soft delete
        $this->is_archived = false;
        $this->save();
    }

    /**
     * Check if user can access this file
     */
    public function userCanAccess(User $user): bool
    {
        // Admin can access everything
        if ($user->isAdmin()) {
            return true;
        }

        // Owner can access
        if ($this->owner_id === $user->id) {
            return true;
        }

        // Moderator for this level
        if ($user->canModerateLevel($this->level_id)) {
            return true;
        }

        // Check folder permissions if file is in a folder
        if ($this->folder) {
            return $this->folder->userHasPermission($user, 'read');
        }

        return false;
    }
}
