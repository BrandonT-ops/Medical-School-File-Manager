<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Level;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Filter by role
        if ($request->has('role') && $request->role != '') {
            $query->where('role', $request->role);
        }

        // Search
        if ($request->has('search') && $request->search != '') {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $levels = Level::orderBy('order')->get();

        return view('admin.users.create', compact('levels'));
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,moderator,user',
            'moderated_levels' => 'nullable|array',
            'moderated_levels.*' => 'exists:levels,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'moderated_levels' => $request->role === 'moderator' ? $request->moderated_levels : null,
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User created successfully!');
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        $user->load(['folders', 'files']);

        $stats = [
            'total_folders' => $user->folders()->count(),
            'total_files' => $user->files()->count(),
            'total_storage' => $user->files()->sum('size'),
        ];

        return view('admin.users.show', compact('user', 'stats'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        $levels = Level::orderBy('order')->get();

        return view('admin.users.edit', compact('user', 'levels'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:admin,moderator,user',
            'moderated_levels' => 'nullable|array',
            'moderated_levels.*' => 'exists:levels,id',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'moderated_levels' => $request->role === 'moderator' ? $request->moderated_levels : null,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User updated successfully!');
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'You cannot delete your own account!');
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User deleted successfully!');
    }

    /**
     * Assign moderator to levels
     */
    public function assignModerator(Request $request, User $user)
    {
        $request->validate([
            'levels' => 'required|array',
            'levels.*' => 'exists:levels,id',
        ]);

        $user->update([
            'role' => 'moderator',
            'moderated_levels' => $request->levels,
        ]);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'Moderator assigned to levels successfully!');
    }
}
