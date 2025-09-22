<?php

declare(strict_types=1);

namespace Ma32kc\PermissionBitmask\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Ma32kc\PermissionBitmask\Contracts\PermissionRepositoryInterface;
use Ma32kc\PermissionBitmask\Models\Permission;

final class PermissionRepository implements PermissionRepositoryInterface
{
    public function findById(int $id): ?Permission
    {
        return Permission::find($id);
    }

    public function findByCode(string $code): ?Permission
    {
        return Permission::where('code', $code)->first();
    }

    public function getAllPermissions(): Collection
    {
        return Permission::all();
    }

    public function getPermissionsByCodes(array $codes): Collection
    {
        return Permission::whereIn('code', $codes)->get();
    }

    public function getModelsWithPermission(string $code, string $modelClass): Collection
    {
        $permission = $this->findByCode($code);
        if (! $permission) {
            return new Collection;
        }
        return $modelClass::whereRaw('permissions & ? != 0', [$permission->bitmask])->get();
    }

    public function create(array $data): Permission
    {
        return Permission::create($data);
    }

    public function update(Permission $permission, array $data): bool
    {
        return $permission->update($data);
    }

    public function delete(Permission $permission): bool
    {
        return $permission->delete();
    }
}
