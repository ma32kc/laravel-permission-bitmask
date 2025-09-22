<?php

declare(strict_types=1);

namespace Ma32kc\PermissionBitmask\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Ma32kc\PermissionBitmask\Models\Permission;

interface PermissionRepositoryInterface
{
    public function findById(int $id): ?Permission;
    public function findByCode(string $code): ?Permission;

    /** @return Collection<int, Permission> */
    public function getAllPermissions(): Collection;

    /** @return Collection<int, Permission> */
    public function getPermissionsByCodes(array $codes): Collection;

    /** @return Collection<int, mixed> */
    public function getModelsWithPermission(string $code, string $modelClass): Collection;

    public function create(array $data): Permission;
    public function update(Permission $permission, array $data): bool;
    public function delete(Permission $permission): bool;
}
