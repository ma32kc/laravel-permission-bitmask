<?php

declare(strict_types=1);

namespace Ma32kc\PermissionBitmask\Tests\Feature;

use Illuminate\Support\Facades\Route;
use Ma32kc\PermissionBitmask\Tests\Support\DummyController;
use Ma32kc\PermissionBitmask\Tests\Support\TestUser;
use Ma32kc\PermissionBitmask\Tests\TestCase;
use Ma32kc\PermissionBitmask\Models\PermissionPolicy;

final class MiddlewareTest extends TestCase
{
    public function test_it_denies_access_if_policy_missing(): void
    {
        Route::middleware('permission.policy')
            ->get('/test', [DummyController::class, 'index']);

        $this->get('/test')->assertStatus(403);
    }

    public function test_it_allows_access_if_policy_matches(): void
    {
        $user = TestUser::create(['name' => 'Tester', 'permissions' => 1]);
        $this->be($user);

        PermissionPolicy::create([
            'permissible_type' => null,
            'permissible_id'   => null,
            'crud_method'      => 'index',
            'bitmask'          => 1,
        ]);

        Route::middleware('permission.policy')
            ->get('/test', [DummyController::class, 'index']);

        $this->get('/test')->assertOk();
    }
}
