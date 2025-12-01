<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\StorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingsController extends Controller
{
    protected StorageService $storage;

    public function __construct(StorageService $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Display the settings page
     */
    public function index()
    {
        $settings = [
            'school_name' => Setting::get('school_name', 'Medical School'),
            'school_tagline' => Setting::get('school_tagline', 'File Management System'),
            'school_logo' => Setting::get('school_logo', ''),
            'school_footer' => Setting::get('school_footer', '© 2024 Medical School. All rights reserved.'),
        ];

        $storageInfo = $this->storage->getStorageInfo();

        return view('admin.settings.index', compact('settings', 'storageInfo'));
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        $request->validate([
            'school_name' => 'required|string|max:255',
            'school_tagline' => 'nullable|string|max:255',
            'school_logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'school_footer' => 'nullable|string|max:500',
        ]);

        // Update school name
        Setting::set('school_name', $request->school_name, 'string', 'School or institution name');

        // Update school tagline
        Setting::set('school_tagline', $request->school_tagline, 'string', 'School tagline or subtitle');

        // Update school footer
        Setting::set('school_footer', $request->school_footer, 'string', 'Footer copyright text');

        // Handle logo upload
        if ($request->hasFile('school_logo')) {
            $logo = $request->file('school_logo');

            // Delete old logo if exists
            $oldLogo = Setting::get('school_logo');
            if ($oldLogo && \Storage::disk('public')->exists($oldLogo)) {
                \Storage::disk('public')->delete($oldLogo);
            }

            // Store new logo
            $logoPath = $logo->store('logos', 'public');

            Setting::set('school_logo', $logoPath, 'string', 'Path to school logo');
        }

        // Clear all settings cache
        Setting::clearCache();
        Cache::forget('school_settings');

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Settings updated successfully!');
    }
}
