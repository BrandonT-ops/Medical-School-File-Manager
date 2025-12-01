@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div>
    <h1>Settings</h1>
    <p>Configure your school branding and system settings</p>

    <div class="card">
        <div class="card-header">
            <h3>School Branding</h3>
        </div>
        <div class="card-body" style="padding: var(--spacing-md);">
            <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label for="school_name" class="form-label required">School Name</label>
                    <input
                        type="text"
                        id="school_name"
                        name="school_name"
                        class="form-control"
                        value="{{ old('school_name', $settings['school_name']) }}"
                        placeholder="Medical School"
                        required
                    >
                    <span class="form-help">The name of your school or institution</span>
                </div>

                <div class="form-group">
                    <label for="school_tagline" class="form-label">School Tagline</label>
                    <input
                        type="text"
                        id="school_tagline"
                        name="school_tagline"
                        class="form-control"
                        value="{{ old('school_tagline', $settings['school_tagline']) }}"
                        placeholder="File Management System"
                    >
                    <span class="form-help">Optional subtitle or tagline shown in the header</span>
                </div>

                <div class="form-group">
                    <label for="school_logo" class="form-label">School Logo</label>

                    @if(!empty($settings['school_logo']))
                    <div style="margin-bottom: var(--spacing-sm);">
                        <img
                            src="{{ asset('storage/' . $settings['school_logo']) }}"
                            alt="Current Logo"
                            style="max-height: 100px; border: var(--border-width) solid var(--color-border);"
                        >
                        <p style="margin-top: var(--spacing-xs); font-size: 0.875rem;">Current logo</p>
                    </div>
                    @endif

                    <input
                        type="file"
                        id="school_logo"
                        name="school_logo"
                        class="form-control"
                        accept="image/png,image/jpeg,image/jpg,image/svg+xml"
                    >
                    <span class="form-help">Upload PNG, JPG, or SVG. Max 2MB. Recommended size: 200x200px</span>
                </div>

                <div class="form-group">
                    <label for="school_footer" class="form-label">Footer Text</label>
                    <textarea
                        id="school_footer"
                        name="school_footer"
                        class="form-control"
                        rows="2"
                        placeholder="© 2024 Medical School. All rights reserved."
                    >{{ old('school_footer', $settings['school_footer']) }}</textarea>
                    <span class="form-help">Copyright text displayed in the footer</span>
                </div>

                <button type="submit" class="btn btn-accent">Save Branding Settings</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>System Information</h3>
        </div>
        <div class="card-body" style="padding: var(--spacing-md);">
            <div class="table-wrapper">
                <table>
                    <tbody>
                        <tr>
                            <td><strong>Storage Provider</strong></td>
                            <td><span class="user-badge">{{ strtoupper($storageInfo['provider']) }}</span></td>
                        </tr>
                        <tr>
                            <td><strong>Max File Size</strong></td>
                            <td>{{ number_format($storageInfo['max_file_size'] / 1048576, 0) }} MB</td>
                        </tr>
                        <tr>
                            <td><strong>Allowed File Types</strong></td>
                            <td>{{ implode(', ', $storageInfo['allowed_extensions']) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="alert alert-info mt-3">
                <strong>Note:</strong> Storage provider and file limits are configured in your .env file.
                Change STORAGE_PROVIDER to switch between 'local', 'gdrive', or other providers.
            </div>
        </div>
    </div>
</div>
@endsection
