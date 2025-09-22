<?php

declare(strict_types=1);

namespace Vendor\PermissionBitmask\Facades;

use Illuminate\Support\Facades\Facade;
use Vendor\PermissionBitmask\Contracts\PermissionServiceInterface;

/**
 * @method static bool hasBitmaskByCode($model, string $code)
 * @method static void addBitmaskByCode($model, string $code)
 * @method static void removeBitmaskByCode($model, string $code)
 */
final class Permission extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return PermissionServiceInterface::class;
    }
}
