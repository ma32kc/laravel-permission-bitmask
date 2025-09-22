<?php

declare(strict_types=1);

namespace Vendor\PermissionBitmask\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Vendor\PermissionBitmask\Models\Permission;

interface PermissionServiceInterface
{
    public function getPermissionsForModel(string $modelClass): Collection;
    public function hasBitmaskByCode(object $model, string $code): bool;
    public function addBitmask(object $model, int $bitmask): bool;
    public function addBitmaskById(object $model, int $permissionId): bool;
    public function addBitmaskByCode(object $model, string $code): bool;
    public function forceAddBitmask(object $model, int $bitmask): bool;
    public function forceAddBitmaskById(object $model, int $permissionId): bool;
    public function forceAddBitmaskByCode(object $model, string $code): bool;
    public function removeBitmask(object $model, int $bitmask): bool;
    public function removeBitmaskByCode(object $model, string $code): bool;
    public function replaceBitmaskByCode(object $model, string $oldCode, string $newCode): bool;
    public function getModelsWithPermission(string $code, string $modelClass): Collection;
    public function getModelPermissions(object $model): array;
    public function syncPermissionsByCodes(object $model, array $codes): bool;
    public function createPermission(array $data): Permission;
    public function updatePermission(Permission $permission, array $data): bool;
    public function deletePermission(Permission $permission): bool;
    public function hasBitmask(mixed $model, int $bitmask): bool;
    public function getBitmaskByCode(string $code): int;
    public function getPermissionsByBitmaskAndClass(int $bitmask, ?string $modelClass = null): Collection;
    public function checkPolicy(object|string|null $model, ?string $method, ?int $id = null): bool;
}
