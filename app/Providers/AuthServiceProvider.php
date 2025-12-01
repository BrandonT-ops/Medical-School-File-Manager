<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Folder;
use App\Models\FileItem;
use App\Policies\FolderPolicy;
use App\Policies\FilePolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Folder::class => FolderPolicy::class,
        FileItem::class => FilePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Define gates for roles
        Gate::define('admin', function ($user) {
            return $user->role === 'admin';
        });

        Gate::define('moderator', function ($user) {
            return in_array($user->role, ['admin', 'moderator']);
        });

        Gate::define('manage-users', function ($user) {
            return $user->role === 'admin';
        });

        Gate::define('access-archive', function ($user) {
            return $user->role === 'admin';
        });

        Gate::define('permanent-delete', function ($user) {
            return $user->role === 'admin';
        });
    }
}
