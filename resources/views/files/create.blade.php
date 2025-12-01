@extends('layouts.app')

@section('title', 'Upload File')

@section('content')
<div style="max-width: 700px; margin: 0 auto;">
    <h1>Upload File</h1>

    <div class="card">
        <div class="card-body" style="padding: var(--spacing-md);">
            <form action="{{ route('files.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                @csrf

                @if($folder)
                <div class="alert alert-info mb-3">
                    Uploading to folder: <strong>{{ $folder->name }}</strong>
                </div>
                <input type="hidden" name="folder_id" value="{{ $folderId }}">
                <input type="hidden" name="level_id" value="{{ $levelId }}">
                @else
                <div class="form-group">
                    <label for="folder_id" class="form-label">Folder (Optional)</label>
                    <select id="folder_id" name="folder_id" class="form-control">
                        <option value="">-- Root / No Folder --</option>
                        @foreach($folders as $folderOption)
                        <option value="{{ $folderOption->id }}" {{ old('folder_id', $folderId) == $folderOption->id ? 'selected' : '' }}>
                            {{ $folderOption->name }} ({{ $folderOption->level->name }})
                        </option>
                        @endforeach
                    </select>
                    <span class="form-help">Select a folder or leave empty for root level</span>
                </div>

                <div class="form-group">
                    <label for="level_id" class="form-label required">Academic Level</label>
                    <select id="level_id" name="level_id" class="form-control" required>
                        <option value="">-- Select Level --</option>
                        @foreach($levels as $level)
                        <option value="{{ $level->id }}" {{ old('level_id', $levelId) == $level->id ? 'selected' : '' }}>
                            {{ $level->name }}
                        </option>
                        @endforeach
                    </select>
                    <span class="form-help">Required: Select the academic level for this file</span>
                </div>
                @endif

                <!-- Drag and Drop Zone -->
                <div class="form-group">
                    <label class="form-label required">File</label>
                    <div class="upload-zone" id="uploadZone">
                        <div class="upload-zone-icon">+</div>
                        <div class="upload-zone-text">Drag & Drop File Here</div>
                        <div class="upload-zone-hint">or click to browse</div>
                        <input
                            type="file"
                            id="file"
                            name="file"
                            style="display: none;"
                            required
                        >
                    </div>
                    <div id="fileInfo" style="margin-top: var(--spacing-sm); display: none;">
                        <div class="card" style="padding: var(--spacing-sm); background: var(--color-accent);">
                            <strong>Selected file:</strong> <span id="fileName"></span><br>
                            <strong>Size:</strong> <span id="fileSize"></span>
                        </div>
                    </div>
                    <span class="form-help">
                        Max size: {{ number_format($storageInfo['max_file_size'] / 1048576, 0) }} MB |
                        Allowed: {{ implode(', ', $storageInfo['allowed_extensions']) }}
                    </span>
                </div>

                <div class="flex gap-2 mt-3">
                    <button type="submit" class="btn btn-accent" id="submitBtn">Upload File</button>
                    <a href="{{ $folder ? route('folders.show', $folder) : route('files.index') }}" class="btn btn-primary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const uploadZone = document.getElementById('uploadZone');
    const fileInput = document.getElementById('file');
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const submitBtn = document.getElementById('submitBtn');

    // Click to browse
    uploadZone.addEventListener('click', () => {
        fileInput.click();
    });

    // File selected
    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            displayFileInfo(e.target.files[0]);
        }
    });

    // Drag and drop
    uploadZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadZone.classList.add('drag-over');
    });

    uploadZone.addEventListener('dragleave', () => {
        uploadZone.classList.remove('drag-over');
    });

    uploadZone.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadZone.classList.remove('drag-over');

        if (e.dataTransfer.files.length > 0) {
            fileInput.files = e.dataTransfer.files;
            displayFileInfo(e.dataTransfer.files[0]);
        }
    });

    function displayFileInfo(file) {
        fileName.textContent = file.name;
        fileSize.textContent = formatFileSize(file.size);
        fileInfo.style.display = 'block';
        uploadZone.querySelector('.upload-zone-text').textContent = 'File selected!';
        uploadZone.querySelector('.upload-zone-hint').textContent = 'Click to choose another file';
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    // Prevent double submission
    document.getElementById('uploadForm').addEventListener('submit', () => {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Uploading...';
    });
</script>
@endpush
@endsection
