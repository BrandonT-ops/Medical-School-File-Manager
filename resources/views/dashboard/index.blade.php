@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div>
    <h1>Dashboard</h1>
    <p style="font-size: 1.125rem; margin-bottom: var(--spacing-lg);">
        Welcome back, <strong>{{ auth()->user()->name }}</strong>!
    </p>

    <!-- User Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">{{ $totalFiles }}</div>
            <div class="stat-label">My Files</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $myFolders->count() }}</div>
            <div class="stat-label">My Folders</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ number_format($totalStorage / 1048576, 2) }} MB</div>
            <div class="stat-label">Storage Used</div>
        </div>
        @if($adminStats)
        <div class="stat-card" style="background: var(--color-accent);">
            <div class="stat-value">{{ $adminStats['total_users'] }}</div>
            <div class="stat-label">Total Users</div>
        </div>
        @endif
    </div>

    <!-- Admin Stats (if admin) -->
    @if($adminStats)
    <div class="card">
        <div class="card-header">
            <h3>System Statistics</h3>
        </div>
        <div class="card-body" style="padding: var(--spacing-md);">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value">{{ $adminStats['total_files'] }}</div>
                    <div class="stat-label">Total Files</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ $adminStats['total_folders'] }}</div>
                    <div class="stat-label">Total Folders</div>
                </div>
                <div class="stat-card" style="background: #E63946; color: white;">
                    <div class="stat-value" style="color: white;">{{ $adminStats['archived_files'] }}</div>
                    <div class="stat-label" style="color: white;">Archived Files</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Levels Overview -->
    <div class="card">
        <div class="card-header">
            <h3>Academic Levels</h3>
        </div>
        <div class="card-body" style="padding: var(--spacing-md);">
            <div class="grid">
                @foreach($levels as $level)
                <a href="{{ route('folders.index', ['level' => $level->id]) }}" class="grid-item">
                    <div class="grid-item-icon">F</div>
                    <div class="grid-item-title">{{ $level->name }}</div>
                    <div class="grid-item-meta">
                        {{ $level->folders_count }} folders • {{ $level->files_count }} files
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </div>

    <!-- My Folders -->
    @if($myFolders->count() > 0)
    <div class="card">
        <div class="card-header">
            <h3>My Recent Folders</h3>
        </div>
        <div class="card-body" style="padding: var(--spacing-md);">
            <div class="grid">
                @foreach($myFolders as $folder)
                <a href="{{ route('folders.show', $folder) }}" class="grid-item">
                    <div class="grid-item-icon" style="background: var(--color-primary); color: white;">F</div>
                    <div class="grid-item-title">{{ $folder->name }}</div>
                    <div class="grid-item-meta">
                        {{ $folder->level->name }} • {{ $folder->files->count() }} files
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('folders.index') }}" class="btn btn-primary btn-sm">View All Folders →</a>
        </div>
    </div>
    @endif

    <!-- Recent Files -->
    @if($recentFiles->count() > 0)
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
                            <th>Folder</th>
                            <th>Level</th>
                            <th>Size</th>
                            <th>Uploaded</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentFiles as $file)
                        <tr>
                            <td><strong>{{ $file->original_filename }}</strong></td>
                            <td>{{ $file->folder->name ?? 'Root' }}</td>
                            <td><span class="user-badge" style="font-size: 0.75rem; padding: 4px 8px;">{{ $file->level->name }}</span></td>
                            <td>{{ $file->formatted_size }}</td>
                            <td>{{ $file->created_at->diffForHumans() }}</td>
                            <td>
                                <a href="{{ route('files.download', $file) }}" class="btn btn-sm btn-primary">Download</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('files.index') }}" class="btn btn-primary btn-sm">View All Files →</a>
        </div>
    </div>
    @endif

    <!-- Quick Actions -->
    <div class="card">
        <div class="card-header">
            <h3>Quick Actions</h3>
        </div>
        <div class="card-body" style="padding: var(--spacing-md);">
            <div class="flex gap-2" style="flex-wrap: wrap;">
                <a href="{{ route('folders.create') }}" class="btn btn-primary">+ Create Folder</a>
                <a href="{{ route('files.create') }}" class="btn btn-accent">↑ Upload File</a>
                <a href="{{ route('folders.index') }}" class="btn btn-primary">Browse Folders</a>
            </div>
        </div>
    </div>
</div>
@endsection
