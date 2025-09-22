<?php

declare(strict_types=1);

namespace Ma32kc\PermissionBitmask\Tests\Unit;

use PHPUnit\Framework\TestCase as FrameworkTestCase;
use Ma32kc\PermissionBitmask\Contracts\PermissionRepositoryInterface;
use Ma32kc\PermissionBitmask\Models\Permission;
use Ma32kc\PermissionBitmask\PermissionService;

final class PermissionServiceUnitTest extends FrameworkTestCase
{
    public function test_has_bitmask_by_code_returns_true(): void
    {
        $repo = $this->createMock(PermissionRepositoryInterface::class);
        $repo->method('findByCode')->willReturn(new Permission([
            'code'    => 'task.status.in_progress',
            'bitmask' => 1,
        ]));

        $service = new PermissionService($repo);

        $model = new class {
            public int $permissions = 1;
        };

        $this->assertTrue(
            $service->hasBitmaskByCode($model, 'task.status.in_progress')
        );
    }

    public function test_has_bitmask_by_code_returns_false(): void
    {
        $repo = $this->createMock(PermissionRepositoryInterface::class);
        $repo->method('findByCode')->willReturn(null);

        $service = new PermissionService($repo);

        $model = new class {
            public int $permissions = 0;
        };

        $this->assertFalse(
            $service->hasBitmaskByCode($model, 'task.status.in_progress')
        );
    }
}
