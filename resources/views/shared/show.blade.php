@extends('layouts.app')

@section('title', $folder->name . ' - Shared')

@section('content')
<div>
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <span class="breadcrumb-item"><a href="{{ route('shared.index') }}">Shared Files</a></span>
        @foreach($breadcrumb as $crumb)
        <span class="breadcrumb-item">
            @if($crumb->id === $folder->id)
                {{ $crumb->name }}
            @else
                <a href="{{ route('shared.show', $crumb) }}">{{ $crumb->name }}</a>
            @endif
        </span>
        @endforeach
    </div>

    <div class="flex justify-between items-center mb-3">
        <h1>{{ $folder->name }}</h1>
        <span class="user-badge" style="background: #06A77D; color: white;">PUBLIC FOLDER</span>
    </div>

    <!-- Folder Info -->
    <div class="card">
        <div class="card-header">
            <h3>Folder Information</h3>
        </div>
        <div class="card-body" style="padding: var(--spacing-md);">
            <div class="table-wrapper">
                <table>
                    <tbody>
                        <tr>
                            <td><strong>Level</strong></td>
                            <td><span class="user-badge">{{ $folder->level->name }}</span></td>
                        </tr>
                        <tr>
                            <td><strong>Owner</strong></td>
                            <td>{{ $folder->owner->name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Permissions</strong></td>
                            <td>
                                @foreach($folder->getPublicPermissions() as $perm)
                                    <span class="user-badge" style="font-size: 0.75rem; padding: 4px 8px; margin-right: 4px;">{{ strtoupper($perm) }}</span>
                                @endforeach
                            </td>
                        </tr>
                        @if($folder->description)
                        <tr>
                            <td><strong>Description</strong></td>
                            <td>{{ $folder->description }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Subfolders -->
    @if($folder->children->where('is_public', true)->count() > 0)
    <div class="card">
        <div class="card-header">
            <h3>Shared Subfolders</h3>
        </div>
        <div class="card-body" style="padding: var(--spacing-md);">
            <div class="grid">
                @foreach($folder->children->where('is_public', true) as $subfolder)
                <a href="{{ route('shared.show', $subfolder) }}" class="grid-item">
                    <div class="grid-item-icon" style="background: var(--color-accent); color: var(--color-text);">F</div>
                    <div class="grid-item-title">{{ $subfolder->name }}</div>
                    <div class="grid-item-meta">
                        {{ $subfolder->files->count() }} files
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Files -->
    @if($folder->files->count() > 0)
    <div class="card">
        <div class="card-header">
            <h3>Files ({{ $folder->files->count() }})</h3>
        </div>
        <div class="card-body" style="padding: var(--spacing-md);">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Filename</th>
                            <th>Size</th>
                            <th>Uploaded By</th>
                            <th>Uploaded</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($folder->files as $file)
                        <tr>
                            <td><strong>{{ $file->original_filename }}</strong></td>
                            <td>{{ $file->formatted_size }}</td>
                            <td>{{ $file->owner->name }}</td>
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
    @else
    <div class="card">
        <div class="card-body" style="padding: var(--spacing-md); text-align: center;">
            <h3>No files in this folder yet</h3>
            <p>This shared folder is currently empty.</p>
        </div>
    </div>
    @endif

    <div class="card" style="background: var(--color-bg);">
        <div class="card-body" style="padding: var(--spacing-md);">
            <p style="margin: 0;">
                <strong>Your permissions:</strong>
                @if(in_array('write', $folder->getPublicPermissions()))
                    You can view, download, and upload files.
                @elseif(in_array('delete', $folder->getPublicPermissions()))
                    You can view, download, upload, and delete files.
                @else
                    You can view and download files.
                @endif
            </p>
        </div>
    </div>
</div>
@endsection
