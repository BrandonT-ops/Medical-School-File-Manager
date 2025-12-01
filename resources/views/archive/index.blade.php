@extends('layouts.app')

@section('title', 'Archive')

@section('content')
<div>
    <h1>Archive</h1>
    <p>Manage archived files (Admin only)</p>

    @if($archivedFiles->count() > 0)
    <div class="card">
        <div class="card-header">
            <h3>Archived Files</h3>
        </div>
        <div class="card-body" style="padding: var(--spacing-md);">
            <div class="alert alert-warning mb-3">
                <strong>Note:</strong> Files in the archive can be restored or permanently deleted.
                Permanent deletion cannot be undone!
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Filename</th>
                            <th>Folder</th>
                            <th>Level</th>
                            <th>Owner</th>
                            <th>Size</th>
                            <th>Archived On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($archivedFiles as $file)
                        <tr>
                            <td><strong>{{ $file->original_filename }}</strong></td>
                            <td>{{ $file->folder->name ?? 'Root' }}</td>
                            <td><span class="user-badge" style="font-size: 0.75rem; padding: 4px 8px;">{{ $file->level->name }}</span></td>
                            <td>{{ $file->owner->name }}</td>
                            <td>{{ $file->formatted_size }}</td>
                            <td>{{ $file->deleted_at->format('M d, Y H:i') }}</td>
                            <td>
                                <form action="{{ route('archive.restore', $file) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Restore this file?')">Restore</button>
                                </form>
                                <form action="{{ route('archive.permanent', $file) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('PERMANENTLY delete this file? This cannot be undone!')">Delete Forever</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div style="margin-top: var(--spacing-md);">
        {{ $archivedFiles->links() }}
    </div>
    @else
    <div class="card">
        <div class="card-body" style="padding: var(--spacing-md); text-align: center;">
            <h3>Archive is empty</h3>
            <p>No archived files found.</p>
            <a href="{{ route('dashboard') }}" class="btn btn-primary mt-2">Back to Dashboard</a>
        </div>
    </div>
    @endif
</div>
@endsection
