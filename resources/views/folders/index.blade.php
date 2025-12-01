@extends('layouts.app')

@section('title', 'Folders')

@section('content')
<div>
    <div class="flex justify-between items-center mb-3">
        <h1>Folders</h1>
        <a href="{{ route('folders.create') }}" class="btn btn-accent">+ Create Folder</a>
    </div>

    <!-- Level Filter -->
    <div class="card">
        <div class="card-header">
            <h3>Filter by Level</h3>
        </div>
        <div class="card-body" style="padding: var(--spacing-md);">
            <div class="flex gap-2" style="flex-wrap: wrap;">
                <a href="{{ route('folders.index') }}" class="btn btn-sm {{ !request('level') ? 'btn-accent' : 'btn-primary' }}">
                    All Levels
                </a>
                @foreach($levels as $level)
                <a href="{{ route('folders.index', ['level' => $level->id]) }}" class="btn btn-sm {{ request('level') == $level->id ? 'btn-accent' : 'btn-primary' }}">
                    {{ $level->name }}
                </a>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Breadcrumb -->
    @if($currentParent)
    <div class="breadcrumb">
        <span class="breadcrumb-item"><a href="{{ route('folders.index') }}">Root</a></span>
        @foreach($currentParent->getBreadcrumb() as $crumb)
        <span class="breadcrumb-item">
            <a href="{{ route('folders.show', $crumb) }}">{{ $crumb->name }}</a>
        </span>
        @endforeach
    </div>
    @endif

    <!-- Folders Grid -->
    @if($folders->count() > 0)
    <div class="card">
        <div class="card-header">
            <h3>{{ $currentLevel ? $currentLevel->name : 'All Folders' }}</h3>
        </div>
        <div class="card-body" style="padding: var(--spacing-md);">
            <div class="grid">
                @foreach($folders as $folder)
                <a href="{{ route('folders.show', $folder) }}" class="grid-item">
                    <div class="grid-item-icon" style="background: var(--color-primary); color: white;">F</div>
                    <div class="grid-item-title">{{ $folder->name }}</div>
                    <div class="grid-item-meta">
                        {{ $folder->level->name }}<br>
                        {{ $folder->children->count() }} subfolders • {{ $folder->files->count() }} files<br>
                        Owner: {{ $folder->owner->name }}
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div style="margin-top: var(--spacing-md);">
        {{ $folders->links() }}
    </div>
    @else
    <div class="card">
        <div class="card-body" style="padding: var(--spacing-md); text-align: center;">
            <h3>No folders found</h3>
            <p>Create your first folder to get started!</p>
            <a href="{{ route('folders.create') }}" class="btn btn-accent mt-2">+ Create Folder</a>
        </div>
    </div>
    @endif
</div>
@endsection
