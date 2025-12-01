<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\FileItem;
use App\Models\Level;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    /**
     * Search for files and folders
     */
    public function index(Request $request)
    {
        $query = $request->get('q');
        $type = $request->get('type', 'all'); // all, files, folders
        $levelId = $request->get('level');
        $scope = $request->get('scope', 'all'); // all, my, shared

        if (!$query || strlen($query) < 2) {
            return view('search.index', [
                'query' => $query,
                'folders' => collect(),
                'files' => collect(),
                'levels' => Level::orderBy('order')->get(),
                'stats' => ['folders' => 0, 'files' => 0],
            ]);
        }

        $user = Auth::user();
        $folders = collect();
        $files = collect();

        // Search Folders
        if ($type === 'all' || $type === 'folders') {
            $folderQuery = Folder::where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->whereNull('deleted_at')
            ->with(['level', 'owner']);

            // Apply level filter
            if ($levelId) {
                $folderQuery->where('level_id', $levelId);
            }

            // Apply scope filter
            if ($scope === 'my') {
                // Only user's own folders
                $folderQuery->where('owner_id', $user->id);
            } elseif ($scope === 'shared') {
                // Only public folders
                $folderQuery->where('is_public', true);
            } else {
                // All accessible folders
                if (!$user->isAdmin()) {
                    $folderQuery->where(function($q) use ($user) {
                        $q->where('owner_id', $user->id)
                          ->orWhere('is_public', true)
                          ->orWhereRaw('JSON_CONTAINS(permissions, ?)', [json_encode([$user->id])]);
                    });
                }
            }

            $folders = $folderQuery->orderBy('name')->get();

            // Filter by user permission
            if (!$user->isAdmin()) {
                $folders = $folders->filter(function ($folder) use ($user) {
                    return $folder->userHasPermission($user, 'read');
                });
            }
        }

        // Search Files
        if ($type === 'all' || $type === 'files') {
            $fileQuery = FileItem::where(function($q) use ($query) {
                $q->where('original_filename', 'like', "%{$query}%")
                  ->orWhere('filename', 'like', "%{$query}%");
            })
            ->where('is_archived', false)
            ->whereNull('deleted_at')
            ->with(['folder', 'level', 'owner']);

            // Apply level filter
            if ($levelId) {
                $fileQuery->where('level_id', $levelId);
            }

            // Apply scope filter
            if ($scope === 'my') {
                // Only user's own files
                $fileQuery->where('owner_id', $user->id);
            } elseif ($scope === 'shared') {
                // Only public files or files in public folders
                $fileQuery->where(function($q) {
                    $q->where('is_public', true)
                      ->orWhereHas('folder', function($fq) {
                          $fq->where('is_public', true);
                      });
                });
            } else {
                // All accessible files
                if (!$user->isAdmin()) {
                    $fileQuery->where(function($q) use ($user) {
                        $q->where('owner_id', $user->id)
                          ->orWhere('is_public', true)
                          ->orWhereHas('folder', function($fq) use ($user) {
                              $fq->where('owner_id', $user->id)
                                 ->orWhere('is_public', true)
                                 ->orWhereRaw('JSON_CONTAINS(permissions, ?)', [json_encode([$user->id])]);
                          });
                    });
                }
            }

            $files = $fileQuery->orderBy('original_filename')->get();

            // Filter by user permission
            if (!$user->isAdmin()) {
                $files = $files->filter(function ($file) use ($user) {
                    return $file->userCanAccess($user);
                });
            }
        }

        $stats = [
            'folders' => $folders->count(),
            'files' => $files->count(),
        ];

        return view('search.index', [
            'query' => $query,
            'folders' => $folders,
            'files' => $files,
            'levels' => Level::orderBy('order')->get(),
            'stats' => $stats,
            'type' => $type,
            'levelId' => $levelId,
            'scope' => $scope,
        ]);
    }
}
