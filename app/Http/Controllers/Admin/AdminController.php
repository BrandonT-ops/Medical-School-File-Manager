<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\FileItem;
use App\Models\Folder;
use App\Models\Level;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard
     */
    public function index()
    {
        // Statistics
        $stats = [
            'total_users' => User::count(),
            'admin_count' => User::where('role', 'admin')->count(),
            'moderator_count' => User::where('role', 'moderator')->count(),
            'user_count' => User::where('role', 'user')->count(),
            'total_files' => FileItem::whereNull('deleted_at')->count(),
            'total_folders' => Folder::whereNull('deleted_at')->count(),
            'archived_files' => FileItem::where('is_archived', true)->count(),
            'total_storage' => FileItem::whereNull('deleted_at')->sum('size'),
        ];

        // Recent users
        $recentUsers = User::orderBy('created_at', 'desc')->limit(5)->get();

        // Recent files
        $recentFiles = FileItem::with(['owner', 'level', 'folder'])
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Levels with counts
        $levels = Level::withCount(['files', 'folders'])
            ->orderBy('order')
            ->get();

        return view('admin.index', compact('stats', 'recentUsers', 'recentFiles', 'levels'));
    }
}
