<?php

declare(strict_types=1);

namespace Vendor\PermissionBitmask\Tests\Feature;

use Vendor\PermissionBitmask\Facades\Permission;
use Vendor\PermissionBitmask\Models\Permission as PermissionModel;
use Vendor\PermissionBitmask\Tests\TestCase;
use Vendor\PermissionBitmask\Tests\Support\TestUser;

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
