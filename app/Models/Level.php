<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'order',
    ];

    /**
     * Get folders in this level
     */
    public function folders()
    {
        return $this->hasMany(Folder::class);
    }

    /**
     * Get files in this level
     */
    public function files()
    {
        return $this->hasMany(FileItem::class);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }
}
