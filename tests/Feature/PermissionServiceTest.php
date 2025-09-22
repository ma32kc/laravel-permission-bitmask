<?php

declare(strict_types=1);

namespace Ma32kc\PermissionBitmask\Tests\Feature;

use Ma32kc\PermissionBitmask\Facades\Permission;
use Ma32kc\PermissionBitmask\Models\Permission as PermissionModel;
use Ma32kc\PermissionBitmask\Tests\TestCase;
use Ma32kc\PermissionBitmask\Tests\Support\TestUser;

final class PermissionServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        PermissionModel::create([
            'code'             => 'task.status.in_progress',
            'bitmask'          => 1,
            'permissible_type' => TestUser::class,
        ]);
    }

    public function test_it_can_add_and_check_bitmask(): void
    {
        $user = TestUser::create(['name' => 'Tester', 'permissions' => 0]);

        Permission::addBitmaskByCode($user, 'task.status.in_progress');

        $this->assertTrue(
            Permission::hasBitmaskByCode($user, 'task.status.in_progress')
        );
    }
}
