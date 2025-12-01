<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\Level;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class FolderController extends Controller
{
    /**
     * Display a listing of folders
     */
    public function index(Request $request)
    {
        $levelId = $request->get('level');
        $parentId = $request->get('parent');

        $query = Folder::with(['level', 'owner', 'children', 'files'])
            ->whereNull('deleted_at');

        if ($levelId) {
            $query->where('level_id', $levelId);
        }

        if ($parentId) {
            $query->where('parent_id', $parentId);
        } else {
            $query->whereNull('parent_id'); // Root folders only
        }

        $folders = $query->orderBy('name')->paginate(20);
        $levels = Level::orderBy('order')->get();
        $currentLevel = $levelId ? Level::find($levelId) : null;
        $currentParent = $parentId ? Folder::find($parentId) : null;

        return view('folders.index', compact('folders', 'levels', 'currentLevel', 'currentParent'));
    }

    /**
     * Show the form for creating a new folder
     */
    public function create(Request $request)
    {
        $levels = Level::orderBy('order')->get();
        $parentId = $request->get('parent');
        $levelId = $request->get('level');

        $parent = $parentId ? Folder::find($parentId) : null;

        // If parent exists, inherit its level
        if ($parent) {
            $levelId = $parent->level_id;
        }

        return view('folders.create', compact('levels', 'parentId', 'levelId', 'parent'));
    }

    /**
     * Store a newly created folder
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'level_id' => 'required|exists:levels,id',
            'parent_id' => 'nullable|exists:folders,id',
            'description' => 'nullable|string|max:1000',
        ]);

        $folder = Folder::create([
            'name' => $request->name,
            'level_id' => $request->level_id,
            'parent_id' => $request->parent_id,
            'owner_id' => Auth::id(),
            'description' => $request->description,
            'permissions' => [], // Empty permissions initially
        ]);

        return redirect()
            ->route('folders.show', $folder)
            ->with('success', 'Folder created successfully!');
    }

    /**
     * Display the specified folder
     */
    public function show(Folder $folder)
    {
        Gate::authorize('view', $folder);

        $folder->load(['level', 'owner', 'parent', 'children', 'files.owner']);

        $breadcrumb = $folder->getBreadcrumb();

        return view('folders.show', compact('folder', 'breadcrumb'));
    }

    /**
     * Show the form for editing the specified folder
     */
    public function edit(Folder $folder)
    {
        Gate::authorize('update', $folder);

        $levels = Level::orderBy('order')->get();
        $folders = Folder::where('id', '!=', $folder->id)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        return view('folders.edit', compact('folder', 'levels', 'folders'));
    }

    /**
     * Update the specified folder
     */
    public function update(Request $request, Folder $folder)
    {
        Gate::authorize('update', $folder);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $folder->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()
            ->route('folders.show', $folder)
            ->with('success', 'Folder updated successfully!');
    }

    /**
     * Remove the specified folder (soft delete)
     */
    public function destroy(Folder $folder)
    {
        Gate::authorize('delete', $folder);

        $folder->delete(); // Soft delete

        return redirect()
            ->route('folders.index')
            ->with('success', 'Folder moved to trash.');
    }

    /**
     * Update folder permissions
     */
    public function updatePermissions(Request $request, Folder $folder)
    {
        Gate::authorize('managePermissions', $folder);

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'permissions' => 'required|array',
            'permissions.*' => 'in:read,write,delete',
        ]);

        $user = User::findOrFail($request->user_id);
        $folder->grantPermission($user, $request->permissions);

        return redirect()
            ->route('folders.show', $folder)
            ->with('success', 'Permissions updated successfully!');
    }

    /**
     * Toggle folder public/private status (Admin only)
     */
    public function togglePublic(Request $request, Folder $folder)
    {
        // Only admins can make folders public
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Only administrators can manage public folders.');
        }

        $request->validate([
            'is_public' => 'required|boolean',
            'public_permissions' => 'nullable|array',
            'public_permissions.*' => 'in:read,write,delete',
        ]);

        $folder->is_public = $request->is_public;
        $folder->public_permissions = $request->public_permissions ?? ['read'];
        $folder->save();

        // Optionally apply to all files in folder
        if ($request->apply_to_files) {
            $folder->files()->update(['is_public' => $request->is_public]);
        }

        $message = $folder->is_public
            ? 'Folder is now publicly accessible!'
            : 'Folder is now private.';

        return redirect()
            ->route('folders.show', $folder)
            ->with('success', $message);
    }
}
