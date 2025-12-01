@extends('layouts.app')

@section('title', 'Shared Files')

@section('content')
<div>
    <h1>Shared Files</h1>
    <p>Access files and folders shared with all users</p>

    <!-- Statistics -->
    <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: var(--spacing-md);">
        <div class="stat-card">
            <div class="stat-value">{{ $stats['total_folders'] }}</div>
            <div class="stat-label">Shared Folders</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['total_files'] }}</div>
            <div class="stat-label">Shared Files</div>
        </div>
    </div>

    <!-- Level Filter -->
    <div class="card">
        <div class="card-header">
            <h3>Filter by Level</h3>
        </div>
        <div class="card-body" style="padding: var(--spacing-md);">
            <div class="flex gap-2" style="flex-wrap: wrap;">
                <a href="{{ route('shared.index') }}" class="btn btn-sm {{ !request('level') ? 'btn-accent' : 'btn-primary' }}">
                    All Levels
                </a>
                @foreach($levels as $level)
                <a href="{{ route('shared.index', ['level' => $level->id]) }}" class="btn btn-sm {{ request('level') == $level->id ? 'btn-accent' : 'btn-primary' }}">
                    {{ $level->name }}
                </a>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Shared Folders -->
    @if($publicFolders->count() > 0)
    <div class="card">
        <div class="card-header">
            <h3>Shared Folders</h3>
        </div>
        <div class="card-body" style="padding: var(--spacing-md);">
            <div class="grid">
                @foreach($publicFolders as $folder)
                <a href="{{ route('shared.show', $folder) }}" class="grid-item">
                    <div class="grid-item-icon" style="background: var(--color-accent); color: var(--color-text);">F</div>
                    <div class="grid-item-title">{{ $folder->name }}</div>
                    <div class="grid-item-meta">
                        {{ $folder->level->name }}<br>
                        {{ $folder->files->count() }} files • {{ $folder->children->count() }} subfolders<br>
                        Owner: {{ $folder->owner->name }}
                    </div>
                    <div style="margin-top: var(--spacing-xs);">
                        <span class="user-badge" style="font-size: 0.75rem; padding: 4px 8px; background: #06A77D; color: white;">
                            PUBLIC
                        </span>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Shared Files -->
    @if($publicFiles->count() > 0)
    <div class="card">
        <div class="card-header">
            <h3>Shared Files</h3>
        </div>
        <div class="card-body" style="padding: var(--spacing-md);">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Filename</th>
                            <th>Level</th>
                            <th>Owner</th>
                            <th>Size</th>
                            <th>Uploaded</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($publicFiles as $file)
                        <tr>
                            <td><strong>{{ $file->original_filename }}</strong></td>
                            <td><span class="user-badge" style="font-size: 0.75rem; padding: 4px 8px;">{{ $file->level->name }}</span></td>
                            <td>{{ $file->owner->name }}</td>
                            <td>{{ $file->formatted_size }}</td>
                            <td>{{ $file->created_at->diffForHumans() }}</td>
                            <td>
                                <a href="{{ route('files.download', $file) }}" class="btn btn-sm btn-accent">Download</a>
                                @if($file->canPreview())
                                <a href="{{ route('files.preview', $file) }}" class="btn btn-sm btn-primary">Preview</a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    @if($publicFolders->count() === 0 && $publicFiles->count() === 0)
    <div class="card">
        <div class="card-body" style="padding: var(--spacing-md); text-align: center;">
            <h3>No shared content yet</h3>
            <p>Administrators can mark folders as public to share them with all users.</p>
            <a href="{{ route('dashboard') }}" class="btn btn-primary mt-2">Back to Dashboard</a>
        </div>
    </div>
    @endif

    <div class="card" style="background: var(--color-accent);">
        <div class="card-body" style="padding: var(--spacing-md);">
            <h4 style="margin: 0 0 var(--spacing-xs) 0;">About Shared Files</h4>
            <p style="margin: 0;">
                <strong>All authenticated users can:</strong> View and download files from shared folders by default.
            </p>
            <p style="margin: var(--spacing-xs) 0 0 0;">
                <strong>Admins can grant additional permissions</strong> for specific users to upload, edit, or delete files in shared folders.
            </p>
        </div>
    </div>
</div>
@endsection
