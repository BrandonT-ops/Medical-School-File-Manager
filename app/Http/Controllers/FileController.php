<?php

namespace App\Http\Controllers;

use App\Models\FileItem;
use App\Models\Folder;
use App\Models\Level;
use App\Services\StorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class FileController extends Controller
{
    protected StorageService $storage;

    public function __construct(StorageService $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Display a listing of files
     */
    public function index(Request $request)
    {
        $folderId = $request->get('folder');
        $levelId = $request->get('level');

        $query = FileItem::with(['folder', 'level', 'owner'])
            ->where('is_archived', false)
            ->whereNull('deleted_at');

        if ($folderId) {
            $query->where('folder_id', $folderId);
        }

        if ($levelId) {
            $query->where('level_id', $levelId);
        }

        $files = $query->orderBy('created_at', 'desc')->paginate(20);
        $folder = $folderId ? Folder::find($folderId) : null;
        $level = $levelId ? Level::find($levelId) : null;

        return view('files.index', compact('files', 'folder', 'level'));
    }

    /**
     * Show the form for creating a new file
     */
    public function create(Request $request)
    {
        $folderId = $request->get('folder');
        $levelId = $request->get('level');

        $folders = Folder::whereNull('deleted_at')->orderBy('name')->get();
        $levels = Level::orderBy('order')->get();

        $folder = $folderId ? Folder::find($folderId) : null;

        // If folder exists, inherit its level
        if ($folder) {
            $levelId = $folder->level_id;
        }

        $storageInfo = $this->storage->getStorageInfo();

        return view('files.create', compact('folders', 'levels', 'folderId', 'levelId', 'folder', 'storageInfo'));
    }

    /**
     * Store a newly uploaded file
     */
    public function store(Request $request)
    {
        $allowedExtensions = $this->storage->getStorageInfo()['allowed_extensions'];
        $maxSize = $this->storage->getStorageInfo()['max_file_size'];

        $request->validate([
            'file' => [
                'required',
                'file',
                'max:' . ($maxSize / 1024), // Convert bytes to KB
                function ($attribute, $value, $fail) use ($allowedExtensions) {
                    $extension = strtolower($value->getClientOriginalExtension());
                    if (!in_array($extension, $allowedExtensions)) {
                        $fail("The file must be one of: " . implode(', ', $allowedExtensions));
                    }
                },
            ],
            'folder_id' => 'nullable|exists:folders,id',
            'level_id' => 'required|exists:levels,id',
        ]);

        $uploadedFile = $request->file('file');

        // Store the file using StorageService
        $fileData = $this->storage->store($uploadedFile, 'files');

        // Create file record in database
        $file = FileItem::create([
            'folder_id' => $request->folder_id,
            'level_id' => $request->level_id,
            'filename' => $fileData['filename'],
            'original_filename' => $uploadedFile->getClientOriginalName(),
            'filepath' => $fileData['filepath'],
            'extension' => $fileData['extension'],
            'mime_type' => $fileData['mime_type'],
            'size' => $fileData['size'],
            'owner_id' => Auth::id(),
            'storage_provider' => $fileData['provider'],
            'external_id' => $fileData['external_id'],
        ]);

        $redirectRoute = $request->folder_id
            ? route('folders.show', $request->folder_id)
            : route('files.index', ['level' => $request->level_id]);

        return redirect($redirectRoute)
            ->with('success', 'File uploaded successfully!');
    }

    /**
     * Display the specified file
     */
    public function show(FileItem $file)
    {
        Gate::authorize('view', $file);

        $file->load(['folder', 'level', 'owner']);

        return view('files.show', compact('file'));
    }

    /**
     * Show the form for editing the specified file
     */
    public function edit(FileItem $file)
    {
        Gate::authorize('update', $file);

        $folders = Folder::whereNull('deleted_at')->orderBy('name')->get();
        $levels = Level::orderBy('order')->get();

        return view('files.edit', compact('file', 'folders', 'levels'));
    }

    /**
     * Update the specified file
     */
    public function update(Request $request, FileItem $file)
    {
        Gate::authorize('update', $file);

        $request->validate([
            'folder_id' => 'nullable|exists:folders,id',
            'level_id' => 'required|exists:levels,id',
        ]);

        $file->update([
            'folder_id' => $request->folder_id,
            'level_id' => $request->level_id,
        ]);

        return redirect()
            ->route('files.show', $file)
            ->with('success', 'File updated successfully!');
    }

    /**
     * Soft delete the file (move to archive)
     */
    public function softDelete(FileItem $file)
    {
        Gate::authorize('delete', $file);

        $file->archive();

        return redirect()
            ->back()
            ->with('success', 'File moved to archive.');
    }

    /**
     * Download the file
     */
    public function download(FileItem $file)
    {
        Gate::authorize('download', $file);

        try {
            $filePath = $this->storage->download($file->filepath, $file->external_id);

            return response()->download($filePath, $file->original_filename);
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'File not found or error downloading: ' . $e->getMessage());
        }
    }

    /**
     * Preview the file
     */
    public function preview(FileItem $file)
    {
        Gate::authorize('view', $file);

        if (!$file->canPreview()) {
            return redirect()
                ->back()
                ->with('error', 'This file type cannot be previewed.');
        }

        $file->load(['folder', 'level', 'owner']);

        return view('files.preview', compact('file'));
    }
}
