@extends('layouts.app')

@section('title', $folder->name)

@section('content')
<div>
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <span class="breadcrumb-item"><a href="{{ route('folders.index') }}">Root</a></span>
        @foreach($breadcrumb as $crumb)
        <span class="breadcrumb-item">
            @if($crumb->id === $folder->id)
                {{ $crumb->name }}
            @else
                <a href="{{ route('folders.show', $crumb) }}">{{ $crumb->name }}</a>
            @endif
        </span>
        @endforeach
    </div>

    <div class="flex justify-between items-center mb-3">
        <h1>{{ $folder->name }}</h1>
        @can('update', $folder)
        <a href="{{ route('folders.edit', $folder) }}" class="btn btn-primary">Edit Folder</a>
        @endcan
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
                            <td><strong>Created</strong></td>
                            <td>{{ $folder->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                        @if($folder->description)
                        <tr>
                            <td><strong>Description</strong></td>
                            <td>{{ $folder->description }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td><strong>Status</strong></td>
                            <td>
                                @if($folder->isPublic())
                                <span class="user-badge" style="background: #06A77D; color: white;">PUBLIC</span>
                                <span style="margin-left: var(--spacing-xs); font-size: 0.875rem;">
                                    ({{ implode(', ', $folder->getPublicPermissions()) }})
                                </span>
                                @else
                                <span class="user-badge" style="background: var(--color-primary); color: white;">PRIVATE</span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Public Access Controls (Admin Only) -->
    @can('admin')
    <div class="card" style="background: var(--color-accent);">
        <div class="card-header">
            <h3>Public Access Settings (Admin)</h3>
        </div>
        <div class="card-body" style="padding: var(--spacing-md);">
            <form action="{{ route('folders.toggle-public', $folder) }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Public Access</label>
                    <div class="form-check">
                        <input
                            type="checkbox"
                            id="is_public"
                            name="is_public"
                            value="1"
                            class="form-check-input"
                            {{ $folder->isPublic() ? 'checked' : '' }}
                            onchange="document.getElementById('permissionsSection').style.display = this.checked ? 'block' : 'none'"
                        >
                        <label for="is_public">Make this folder publicly accessible to all users</label>
                    </div>
                    <span class="form-help">When enabled, all authenticated users can access this folder</span>
                </div>

                <div id="permissionsSection" style="{{ $folder->isPublic() ? 'display: block;' : 'display: none;' }}">
                    <div class="form-group">
                        <label class="form-label">Public Permissions</label>
                        @php
                            $currentPerms = $folder->getPublicPermissions();
                        @endphp
                        <div class="form-check">
                            <input
                                type="checkbox"
                                id="perm_read"
                                name="public_permissions[]"
                                value="read"
                                class="form-check-input"
                                checked
                                disabled
                            >
                            <label for="perm_read">Read (always enabled for public folders)</label>
                        </div>
                        <div class="form-check">
                            <input
                                type="checkbox"
                                id="perm_write"
                                name="public_permissions[]"
                                value="write"
                                class="form-check-input"
                                {{ in_array('write', $currentPerms) ? 'checked' : '' }}
                            >
                            <label for="perm_write">Write (upload files)</label>
                        </div>
                        <div class="form-check">
                            <input
                                type="checkbox"
                                id="perm_delete"
                                name="public_permissions[]"
                                value="delete"
                                class="form-check-input"
                                {{ in_array('delete', $currentPerms) ? 'checked' : '' }}
                            >
                            <label for="perm_delete">Delete (remove files)</label>
                        </div>
                        <input type="hidden" name="public_permissions[]" value="read">
                    </div>

                    <div class="form-check">
                        <input
                            type="checkbox"
                            id="apply_to_files"
                            name="apply_to_files"
                            value="1"
                            class="form-check-input"
                        >
                        <label for="apply_to_files">Also apply public status to all files in this folder</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary mt-2">Update Public Access</button>
            </form>
        </div>
    </div>
    @endcan

    <!-- Quick Actions -->
    <div class="card">
        <div class="card-header">
            <h3>Quick Actions</h3>
        </div>
        <div class="card-body" style="padding: var(--spacing-md);">
            <div class="flex gap-2" style="flex-wrap: wrap;">
                <a href="{{ route('folders.create', ['parent' => $folder->id]) }}" class="btn btn-accent">+ Create Subfolder</a>
                <a href="{{ route('files.create', ['folder' => $folder->id]) }}" class="btn btn-accent">Upload File</a>
                @can('delete', $folder)
                <form action="{{ route('folders.destroy', $folder) }}" method="POST" style="display: inline;" onsubmit="return confirm('Delete this folder?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Folder</button>
                </form>
                @endcan
            </div>
        </div>
    </div>

    <!-- Subfolders -->
    @if($folder->children->count() > 0)
    <div class="card">
        <div class="card-header">
            <h3>Subfolders ({{ $folder->children->count() }})</h3>
        </div>
        <div class="card-body" style="padding: var(--spacing-md);">
            <div class="grid">
                @foreach($folder->children as $subfolder)
                <a href="{{ route('folders.show', $subfolder) }}" class="grid-item">
                    <div class="grid-item-icon" style="background: var(--color-primary); color: white;">F</div>
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
                            <th>Uploaded</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($folder->files as $file)
                        <tr>
                            <td><strong>{{ $file->original_filename }}</strong></td>
                            <td>{{ $file->formatted_size }}</td>
                            <td>{{ $file->created_at->diffForHumans() }}</td>
                            <td>
                                <a href="{{ route('files.download', $file) }}" class="btn btn-sm btn-primary">Download</a>
                                @can('delete', $file)
                                <form action="{{ route('files.soft-delete', $file) }}" method="POST" style="display: inline;" onsubmit="return confirm('Archive this file?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Archive</button>
                                </form>
                                @endcan
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
            <h3>No files yet</h3>
            <p>Upload your first file to this folder!</p>
            <a href="{{ route('files.create', ['folder' => $folder->id]) }}" class="btn btn-accent mt-2">Upload File</a>
        </div>
    </div>
    @endif
</div>
@endsection
