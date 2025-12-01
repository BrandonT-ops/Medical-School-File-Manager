<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\FileItem;
use App\Models\Level;
use Illuminate\Http\Request;

class SharedFilesController extends Controller
{
    /**
     * Display shared/public files and folders
     */
    public function index(Request $request)
    {
        $levelId = $request->get('level');

        // Get all public folders
        $query = Folder::where('is_public', true)
            ->with(['level', 'owner', 'children', 'files'])
            ->whereNull('deleted_at');

        if ($levelId) {
            $query->where('level_id', $levelId);
        } else {
            $query->whereNull('parent_id'); // Root folders only
        }

        $publicFolders = $query->orderBy('name')->get();

        // Get all public files (not in folders or in root)
        $fileQuery = FileItem::where('is_public', true)
            ->with(['level', 'owner'])
            ->where('is_archived', false)
            ->whereNull('deleted_at');

        if ($levelId) {
            $fileQuery->where('level_id', $levelId);
        }

        // Only show files that are either public themselves or have no folder
        $fileQuery->where(function($q) {
            $q->whereNull('folder_id')
              ->orWhereHas('folder', function($fq) {
                  $fq->where('is_public', false); // Show public files even if not in public folder
              });
        });

        $publicFiles = $fileQuery->orderBy('created_at', 'desc')->get();

        $levels = Level::orderBy('order')->get();
        $currentLevel = $levelId ? Level::find($levelId) : null;

        // Statistics
        $stats = [
            'total_folders' => Folder::where('is_public', true)->count(),
            'total_files' => FileItem::where('is_public', true)->count(),
        ];

        return view('shared.index', compact('publicFolders', 'publicFiles', 'levels', 'currentLevel', 'stats'));
    }

    /**
     * Show a specific public folder
     */
    public function show(Folder $folder)
    {
        // Only show if folder is public
        if (!$folder->isPublic()) {
            abort(403, 'This folder is not publicly accessible.');
        }

        $folder->load(['level', 'owner', 'parent', 'children', 'files.owner']);

        $breadcrumb = $folder->getBreadcrumb();

        return view('shared.show', compact('folder', 'breadcrumb'));
    }
}
