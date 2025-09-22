<?php

declare(strict_types=1);

namespace Vendor\PermissionBitmask\Tests\Unit;

use PHPUnit\Framework\TestCase as FrameworkTestCase;
use Vendor\PermissionBitmask\Contracts\PermissionRepositoryInterface;
use Vendor\PermissionBitmask\Models\Permission;
use Vendor\PermissionBitmask\PermissionService;

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
