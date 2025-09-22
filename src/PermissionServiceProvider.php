<?php

declare(strict_types=1);

namespace Ma32kc\PermissionBitmask;

use Illuminate\Support\ServiceProvider;
use Ma32kc\PermissionBitmask\Contracts\PermissionRepositoryInterface;
use Ma32kc\PermissionBitmask\Contracts\PermissionServiceInterface;
use Ma32kc\PermissionBitmask\Repositories\PermissionRepository;
use Ma32kc\PermissionBitmask\PermissionService;
use Ma32kc\PermissionBitmask\Middleware\CheckPermissionPolicy;

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
