@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div>
    <h1>Admin Dashboard</h1>
    <p>System overview and management</p>

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">{{ $stats['total_users'] }}</div>
            <div class="stat-label">Total Users</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['total_files'] }}</div>
            <div class="stat-label">Total Files</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['total_folders'] }}</div>
            <div class="stat-label">Total Folders</div>
        </div>
        <div class="stat-card" style="background: #E63946; color: white;">
            <div class="stat-value" style="color: white;">{{ $stats['archived_files'] }}</div>
            <div class="stat-label" style="color: white;">Archived Files</div>
        </div>
    </div>

    <!-- User Statistics -->
    <div class="card">
        <div class="card-header">
            <h3>User Statistics</h3>
        </div>
        <div class="card-body" style="padding: var(--spacing-md);">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value">{{ $stats['admin_count'] }}</div>
                    <div class="stat-label">Admins</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ $stats['moderator_count'] }}</div>
                    <div class="stat-label">Moderators</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ $stats['user_count'] }}</div>
                    <div class="stat-label">Standard Users</div>
                </div>
                <div class="stat-card" style="background: var(--color-accent);">
                    <div class="stat-value">{{ number_format($stats['total_storage'] / 1048576, 2) }} MB</div>
                    <div class="stat-label">Total Storage</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="card">
        <div class="card-header">
            <h3>Quick Actions</h3>
        </div>
        <div class="card-body" style="padding: var(--spacing-md);">
            <div class="flex gap-2" style="flex-wrap: wrap;">
                <a href="{{ route('admin.users.index') }}" class="btn btn-primary">Manage Users</a>
                <a href="{{ route('admin.users.create') }}" class="btn btn-accent">Create User</a>
                <a href="{{ route('admin.settings.index') }}" class="btn btn-primary">System Settings</a>
                <a href="{{ route('archive.index') }}" class="btn btn-primary">View Archive</a>
            </div>
        </div>
    </div>

    <!-- Recent Users -->
    <div class="card">
        <div class="card-header">
            <h3>Recent Users</h3>
        </div>
        <div class="card-body" style="padding: var(--spacing-md);">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentUsers as $user)
                        <tr>
                            <td><strong>{{ $user->name }}</strong></td>
                            <td>{{ $user->email }}</td>
                            <td><span class="user-badge">{{ strtoupper($user->role) }}</span></td>
                            <td>{{ $user->created_at->diffForHumans() }}</td>
                            <td>
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-primary">Edit</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('admin.users.index') }}" class="btn btn-primary btn-sm">View All Users</a>
        </div>
    </div>

    <!-- Recent Files -->
    <div class="card">
        <div class="card-header">
            <h3>Recent Files</h3>
        </div>
        <div class="card-body" style="padding: var(--spacing-md);">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Filename</th>
                            <th>Owner</th>
                            <th>Level</th>
                            <th>Size</th>
                            <th>Uploaded</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentFiles as $file)
                        <tr>
                            <td><strong>{{ $file->original_filename }}</strong></td>
                            <td>{{ $file->owner->name }}</td>
                            <td><span class="user-badge" style="font-size: 0.75rem; padding: 4px 8px;">{{ $file->level->name }}</span></td>
                            <td>{{ $file->formatted_size }}</td>
                            <td>{{ $file->created_at->diffForHumans() }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Academic Levels Overview -->
    <div class="card">
        <div class="card-header">
            <h3>Academic Levels Overview</h3>
        </div>
        <div class="card-body" style="padding: var(--spacing-md);">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Level</th>
                            <th>Folders</th>
                            <th>Files</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($levels as $level)
                        <tr>
                            <td><strong>{{ $level->name }}</strong></td>
                            <td>{{ $level->folders_count }}</td>
                            <td>{{ $level->files_count }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
