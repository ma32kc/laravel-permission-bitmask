<?php

declare(strict_types=1);

namespace Ma32kc\PermissionBitmask\Facades;

use Illuminate\Support\Facades\Facade;
use Ma32kc\PermissionBitmask\Contracts\PermissionServiceInterface;

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
