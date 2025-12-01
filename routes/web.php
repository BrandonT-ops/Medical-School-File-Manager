<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\SharedFilesController;
use App\Http\Controllers\SearchController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect()->route('login');
    });

    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Search
    Route::get('/search', [SearchController::class, 'index'])->name('search');

    // Shared Files - Accessible to all authenticated users
    Route::get('/shared', [SharedFilesController::class, 'index'])->name('shared.index');
    Route::get('/shared/folders/{folder}', [SharedFilesController::class, 'show'])->name('shared.show');

    // Folders
    Route::resource('folders', FolderController::class);
    Route::post('/folders/{folder}/permissions', [FolderController::class, 'updatePermissions'])->name('folders.permissions');
    Route::post('/folders/{folder}/toggle-public', [FolderController::class, 'togglePublic'])->name('folders.toggle-public');

    // Files
    Route::get('/files/{file}/download', [FileController::class, 'download'])->name('files.download');
    Route::get('/files/{file}/preview', [FileController::class, 'preview'])->name('files.preview');
    Route::resource('files', FileController::class);
    Route::delete('/files/{file}/soft-delete', [FileController::class, 'softDelete'])->name('files.soft-delete');

    // Archive (Admin only)
    Route::middleware('admin')->group(function () {
        Route::get('/archive', [ArchiveController::class, 'index'])->name('archive.index');
        Route::post('/archive/{file}/restore', [ArchiveController::class, 'restore'])->name('archive.restore');
        Route::delete('/archive/{file}/permanent', [ArchiveController::class, 'permanentDelete'])->name('archive.permanent');
    });

    // Admin routes
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');

        // User management
        Route::resource('users', UserController::class);
        Route::post('/users/{user}/assign-moderator', [UserController::class, 'assignModerator'])->name('users.assign-moderator');

        // Settings
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
    });
});
