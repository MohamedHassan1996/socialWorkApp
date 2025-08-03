<?php

namespace App\Providers;

use App\Repositories\Authorization\PermissionRepository;
use App\Repositories\Authorization\RoleRepository;
use App\Services\User\AuthorizationService;
use App\Services\User\PermissionService;
use Illuminate\Support\ServiceProvider;

class RolePermissionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
         // Register repositories
        $this->app->bind(RoleRepository::class, function ($app) {
            return new RoleRepository();
        });

        $this->app->bind(PermissionRepository::class, function ($app) {
            return new PermissionRepository();
        });

        // Register services
        $this->app->bind(PermissionService::class, function ($app) {
            return new PermissionService(
                $app->make(RoleRepository::class),
                $app->make(PermissionRepository::class)
            );
        });

        $this->app->bind(AuthorizationService::class, function ($app) {
            return new AuthorizationService(
                $app->make(PermissionService::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
