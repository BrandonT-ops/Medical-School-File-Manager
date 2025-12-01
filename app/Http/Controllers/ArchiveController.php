<?php

namespace App\Http\Controllers;

use App\Models\FileItem;
use App\Services\StorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ArchiveController extends Controller
{
    protected StorageService $storage;

    public function __construct(StorageService $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Display archived files (Admin only)
     */
    public function index()
    {
        Gate::authorize('access-archive');

        $archivedFiles = FileItem::onlyTrashed()
            ->where('is_archived', true)
            ->with(['folder', 'level', 'owner'])
            ->orderBy('deleted_at', 'desc')
            ->paginate(20);

        return view('archive.index', compact('archivedFiles'));
    }

    /**
     * Restore a file from archive
     */
    public function restore($id)
    {
        Gate::authorize('access-archive');

        $file = FileItem::onlyTrashed()->findOrFail($id);

        Gate::authorize('restore', $file);

        $file->unarchive();

        return redirect()
            ->route('archive.index')
            ->with('success', 'File restored successfully!');
    }

    /**
     * Permanently delete a file (Admin only)
     */
    public function permanentDelete($id)
    {
        Gate::authorize('permanent-delete');

        $file = FileItem::onlyTrashed()->findOrFail($id);

        Gate::authorize('forceDelete', $file);

        // Delete file from storage
        try {
            $this->storage->delete($file->filepath, $file->external_id);
        } catch (\Exception $e) {
            // Log error but continue with database deletion
            \Log::error('Failed to delete file from storage: ' . $e->getMessage());
        }

        // Permanently delete from database
        $file->forceDelete();

        return redirect()
            ->route('archive.index')
            ->with('success', 'File permanently deleted!');
    }
}
