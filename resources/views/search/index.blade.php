@extends('layouts.app')

@section('title', 'Search')

@section('content')
<div>
    <h1>Search Files & Folders</h1>

    <!-- Search Form -->
    <div class="card">
        <div class="card-body" style="padding: var(--spacing-md);">
            <form action="{{ route('search') }}" method="GET">
                <div class="form-group">
                    <label for="q" class="form-label required">Search Query</label>
                    <input
                        type="text"
                        id="q"
                        name="q"
                        class="form-control"
                        value="{{ $query ?? '' }}"
                        placeholder="Enter filename, folder name, or keywords..."
                        required
                        autofocus
                    >
                    <span class="form-help">Minimum 2 characters required</span>
                </div>

                <div class="flex gap-2" style="flex-wrap: wrap;">
                    <!-- Type Filter -->
                    <div class="form-group" style="flex: 1; min-width: 150px;">
                        <label for="type" class="form-label">Type</label>
                        <select id="type" name="type" class="form-control">
                            <option value="all" {{ ($type ?? 'all') === 'all' ? 'selected' : '' }}>All</option>
                            <option value="files" {{ ($type ?? '') === 'files' ? 'selected' : '' }}>Files Only</option>
                            <option value="folders" {{ ($type ?? '') === 'folders' ? 'selected' : '' }}>Folders Only</option>
                        </select>
                    </div>

                    <!-- Level Filter -->
                    <div class="form-group" style="flex: 1; min-width: 150px;">
                        <label for="level" class="form-label">Level</label>
                        <select id="level" name="level" class="form-control">
                            <option value="">All Levels</option>
                            @foreach($levels as $level)
                            <option value="{{ $level->id }}" {{ ($levelId ?? '') == $level->id ? 'selected' : '' }}>
                                {{ $level->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Scope Filter -->
                    <div class="form-group" style="flex: 1; min-width: 150px;">
                        <label for="scope" class="form-label">Scope</label>
                        <select id="scope" name="scope" class="form-control">
                            <option value="all" {{ ($scope ?? 'all') === 'all' ? 'selected' : '' }}>All Accessible</option>
                            <option value="my" {{ ($scope ?? '') === 'my' ? 'selected' : '' }}>My Files Only</option>
                            <option value="shared" {{ ($scope ?? '') === 'shared' ? 'selected' : '' }}>Shared Only</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-accent mt-2">Search</button>
                @if($query)
                <a href="{{ route('search') }}" class="btn btn-primary mt-2">Clear Search</a>
                @endif
            </form>
        </div>
    </div>

    @if($query)
    <!-- Search Results -->
    <div class="card">
        <div class="card-header">
            <h3>Search Results for "{{ $query }}"</h3>
        </div>
        <div class="card-body" style="padding: var(--spacing-md);">
            <!-- Statistics -->
            <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); margin-bottom: var(--spacing-md);">
                <div class="stat-card">
                    <div class="stat-value">{{ $stats['folders'] }}</div>
                    <div class="stat-label">Folders Found</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ $stats['files'] }}</div>
                    <div class="stat-label">Files Found</div>
                </div>
                <div class="stat-card" style="background: var(--color-accent);">
                    <div class="stat-value">{{ $stats['folders'] + $stats['files'] }}</div>
                    <div class="stat-label">Total Results</div>
                </div>
            </div>

            @if($stats['folders'] + $stats['files'] === 0)
            <div style="text-align: center; padding: var(--spacing-lg);">
                <h3>No results found</h3>
                <p>Try different keywords or adjust your filters.</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Folders Results -->
    @if($folders->count() > 0)
    <div class="card">
        <div class="card-header">
            <h3>Folders ({{ $folders->count() }})</h3>
        </div>
        <div class="card-body" style="padding: var(--spacing-md);">
            <div class="grid">
                @foreach($folders as $folder)
                <a href="{{ route('folders.show', $folder) }}" class="grid-item">
                    <div class="grid-item-icon" style="background: {{ $folder->isPublic() ? 'var(--color-accent)' : 'var(--color-primary)' }}; color: {{ $folder->isPublic() ? 'var(--color-text)' : 'white' }};">
                        F
                    </div>
                    <div class="grid-item-title">{{ $folder->name }}</div>
                    <div class="grid-item-meta">
                        {{ $folder->level->name }}<br>
                        Owner: {{ $folder->owner->name }}<br>
                        {{ $folder->files->count() }} files
                    </div>
                    @if($folder->isPublic())
                    <div style="margin-top: var(--spacing-xs);">
                        <span class="user-badge" style="font-size: 0.75rem; padding: 4px 8px; background: #06A77D; color: white;">
                            PUBLIC
                        </span>
                    </div>
                    @endif
                </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Files Results -->
    @if($files->count() > 0)
    <div class="card">
        <div class="card-header">
            <h3>Files ({{ $files->count() }})</h3>
        </div>
        <div class="card-body" style="padding: var(--spacing-md);">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Filename</th>
                            <th>Folder</th>
                            <th>Level</th>
                            <th>Owner</th>
                            <th>Size</th>
                            <th>Modified</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($files as $file)
                        <tr>
                            <td>
                                <strong>{{ $file->original_filename }}</strong>
                                @if($file->isPublic())
                                <span class="user-badge" style="font-size: 0.7rem; padding: 2px 6px; background: #06A77D; color: white; margin-left: 4px;">PUBLIC</span>
                                @endif
                            </td>
                            <td>{{ $file->folder->name ?? 'Root' }}</td>
                            <td><span class="user-badge" style="font-size: 0.75rem; padding: 4px 8px;">{{ $file->level->name }}</span></td>
                            <td>{{ $file->owner->name }}</td>
                            <td>{{ $file->formatted_size }}</td>
                            <td>{{ $file->updated_at->diffForHumans() }}</td>
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
    @endif

    <!-- Search Tips -->
    <div class="card" style="background: var(--color-bg);">
        <div class="card-header">
            <h3>Search Tips</h3>
        </div>
        <div class="card-body" style="padding: var(--spacing-md);">
            <ul style="margin: 0; padding-left: var(--spacing-md);">
                <li><strong>Type Filter:</strong> Choose "Files Only" or "Folders Only" to narrow results</li>
                <li><strong>Level Filter:</strong> Filter by academic level (Level 1-7 or General)</li>
                <li><strong>Scope Filter:</strong>
                    <ul style="margin-top: var(--spacing-xs); padding-left: var(--spacing-md);">
                        <li><strong>All Accessible:</strong> Search everything you have access to</li>
                        <li><strong>My Files Only:</strong> Search only files and folders you own</li>
                        <li><strong>Shared Only:</strong> Search only public/shared content</li>
                    </ul>
                </li>
                <li><strong>Search Query:</strong> Searches in filenames, folder names, and descriptions</li>
            </ul>
        </div>
    </div>
</div>
@endsection
