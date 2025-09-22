<?php

declare(strict_types=1);

namespace Vendor\PermissionBitmask\Tests\Feature;

use Illuminate\Support\Facades\Route;
use Vendor\PermissionBitmask\Tests\Support\DummyController;
use Vendor\PermissionBitmask\Tests\Support\TestUser;
use Vendor\PermissionBitmask\Tests\TestCase;
use Vendor\PermissionBitmask\Models\PermissionPolicy;

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
