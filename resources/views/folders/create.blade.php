@extends('layouts.app')

@section('title', 'Create Folder')

@section('content')
<div style="max-width: 700px; margin: 0 auto;">
    <h1>Create New Folder</h1>

    <div class="card">
        <div class="card-body" style="padding: var(--spacing-md);">
            <form action="{{ route('folders.store') }}" method="POST">
                @csrf

                <input type="hidden" name="parent_id" value="{{ $parentId }}">

                @if($parent)
                <div class="alert alert-info mb-3">
                    Creating subfolder in: <strong>{{ $parent->name }}</strong>
                </div>
                @endif

                <div class="form-group">
                    <label for="name" class="form-label required">Folder Name</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="form-control"
                        value="{{ old('name') }}"
                        placeholder="e.g., Lecture Notes"
                        required
                        autofocus
                    >
                    <span class="form-help">Choose a descriptive name for your folder</span>
                </div>

                <div class="form-group">
                    <label for="level_id" class="form-label required">Academic Level</label>
                    <select id="level_id" name="level_id" class="form-control" required {{ $parent ? 'disabled' : '' }}>
                        <option value="">-- Select Level --</option>
                        @foreach($levels as $level)
                        <option value="{{ $level->id }}" {{ old('level_id', $levelId) == $level->id ? 'selected' : '' }}>
                            {{ $level->name }}
                        </option>
                        @endforeach
                    </select>
                    @if($parent)
                    <input type="hidden" name="level_id" value="{{ $levelId }}">
                    <span class="form-help">Level inherited from parent folder</span>
                    @else
                    <span class="form-help">Select the academic level for this folder</span>
                    @endif
                </div>

                <div class="form-group">
                    <label for="description" class="form-label">Description</label>
                    <textarea
                        id="description"
                        name="description"
                        class="form-control"
                        placeholder="Optional description..."
                    >{{ old('description') }}</textarea>
                    <span class="form-help">Optional: Add a description for this folder</span>
                </div>

                <div class="flex gap-2 mt-3">
                    <button type="submit" class="btn btn-accent">Create Folder</button>
                    <a href="{{ route('folders.index') }}" class="btn btn-primary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
