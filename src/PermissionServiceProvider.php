<?php

declare(strict_types=1);

namespace Vendor\PermissionBitmask;

use Illuminate\Support\ServiceProvider;
use Vendor\PermissionBitmask\Contracts\PermissionRepositoryInterface;
use Vendor\PermissionBitmask\Contracts\PermissionServiceInterface;
use Vendor\PermissionBitmask\Repositories\PermissionRepository;
use Vendor\PermissionBitmask\PermissionService;
use Vendor\PermissionBitmask\Middleware\CheckPermissionPolicy;

final class PermissionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PermissionRepositoryInterface::class, PermissionRepository::class);
        $this->app->singleton(PermissionServiceInterface::class, PermissionService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->publishes([
            __DIR__ . '/../config/permission.php' => config_path('permission.php'),
        ], 'config');

        $this->app['router']->aliasMiddleware(
            'permission.policy',
            CheckPermissionPolicy::class
        );
    }
}
