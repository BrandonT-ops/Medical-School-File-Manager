<?php

namespace App\Http\Controllers;

use App\Models\Level;
use App\Models\Folder;
use App\Models\FileItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the dashboard
     */
    public function index()
    {
        $user = Auth::user();

        // Get all levels with their folder and file counts
        $levels = Level::orderBy('order')
            ->withCount(['folders', 'files'])
            ->get();

        // Get recent files for the user
        $recentFiles = FileItem::where('owner_id', $user->id)
            ->orWhereHas('folder', function ($query) use ($user) {
                $query->where('owner_id', $user->id)
                    ->orWhereRaw('JSON_CONTAINS(permissions, ?)', [json_encode([$user->id])]);
            })
            ->where('is_archived', false)
            ->whereNull('deleted_at')
            ->with(['folder', 'level', 'owner'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get user's folders
        $myFolders = Folder::where('owner_id', $user->id)
            ->whereNull('parent_id')
            ->whereNull('deleted_at')
            ->with(['level', 'children'])
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        // Get storage info
        $totalFiles = FileItem::where('owner_id', $user->id)
            ->whereNull('deleted_at')
            ->count();

        $totalStorage = FileItem::where('owner_id', $user->id)
            ->whereNull('deleted_at')
            ->sum('size');

        // Admin statistics
        $adminStats = null;
        if ($user->isAdmin()) {
            $adminStats = [
                'total_users' => \App\Models\User::count(),
                'total_files' => FileItem::whereNull('deleted_at')->count(),
                'total_folders' => Folder::whereNull('deleted_at')->count(),
                'archived_files' => FileItem::where('is_archived', true)->count(),
            ];
        }

        return view('dashboard.index', compact(
            'levels',
            'recentFiles',
            'myFolders',
            'totalFiles',
            'totalStorage',
            'adminStats'
        ));
    }
}
