<?php

declare(strict_types=1);

namespace Vendor\PermissionBitmask;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
use Vendor\PermissionBitmask\Contracts\PermissionRepositoryInterface;
use Vendor\PermissionBitmask\Contracts\PermissionServiceInterface;
use Vendor\PermissionBitmask\Models\Permission;
use Vendor\PermissionBitmask\Models\PermissionPolicy;

/**
 * Service for managing permissions with bitmasks.
 *
 * Encapsulates all business logic: checks, transitions,
 * create/update/delete, synchronization, and policy validation.
 */
final class PermissionService implements PermissionServiceInterface
{
    private PermissionRepositoryInterface $permissionRepository;

    public function __construct(PermissionRepositoryInterface $permissionRepository)
    {
        $this->permissionRepository = $permissionRepository;
    }

    /**
     * Get all permissions for a given model.
     *
     * @return Collection<int, Permission>
     */
    public function getPermissionsForModel(string $modelClass): Collection
    {
        return $this->permissionRepository
            ->getAllPermissions()
            ->where('permissible_type', $modelClass)
            ->values();
    }

    public function hasBitmaskByCode(object $model, string $code): bool
    {
        $permission = $this->permissionRepository->findByCode($code);
        return $permission ? (($permission->bitmask & $model->permissions) !== 0) : false;
    }

    public function addBitmask(object $model, int $bitmask): bool
    {
        $permission = $this->permissionRepository
            ->getAllPermissions()
            ->firstWhere('bitmask', $bitmask);

        if (! $permission) {
            throw ValidationException::withMessages([
                'permissions' => "Permission with bitmask {$bitmask} not found.",
            ]);
        }

        return $this->applyTransition($model, $permission);
    }

    public function addBitmaskById(object $model, int $permissionId): bool
    {
        $permission = $this->permissionRepository->findById($permissionId);

        if (! $permission) {
            throw ValidationException::withMessages([
                'permissions' => "Permission with ID {$permissionId} not found.",
            ]);
        }

        return $this->applyTransition($model, $permission);
    }

    public function addBitmaskByCode(object $model, string $code): bool
    {
        $permission = $this->permissionRepository->findByCode($code);

        if (! $permission) {
            throw ValidationException::withMessages([
                'permissions' => "Permission with code {$code} not found.",
            ]);
        }

        return $this->applyTransition($model, $permission);
    }

    protected function applyTransition(object $model, Permission $permission): bool
    {
        $bitmask = (int) ($model->permissions ?? 0);

        $currentPermissions = collect($this->getModelPermissions($model))
            ->filter(fn(Permission $perm) => $perm->permissible_type !== null)
            ->values();

        foreach ($currentPermissions as $current) {
            if ($current->bitmask_disabled && ($current->bitmask_disabled & $permission->bitmask)) {
                throw ValidationException::withMessages([
                    'permissions' => "Transition to {$permission->code} is forbidden.",
                ]);
            }
        }

        if ($currentPermissions->isNotEmpty()) {
            $allowed = false;
            foreach ($currentPermissions as $current) {
                if ($current->bitmask_next && ($current->bitmask_next & $permission->bitmask)) {
                    $allowed = true;
                    break;
                }
            }
            if (! $allowed) {
                throw ValidationException::withMessages([
                    'permissions' => "Transition to {$permission->code} not allowed from current state.",
                ]);
            }
        }

        foreach ($currentPermissions as $current) {
            $flushMask = (int) ($current->bitmask_flush ?? 0);
            if ($flushMask > 0) {
                $bitmask &= ~$flushMask;
            }
        }

        $newFlush = (int) ($permission->bitmask_flush ?? 0);
        if ($newFlush > 0) {
            $bitmask &= ~$newFlush;
        }

        if (($bitmask & $permission->bitmask) !== $permission->bitmask) {
            $bitmask |= $permission->bitmask;
            $model->permissions = $bitmask;
            return $model->save();
        }

        return false;
    }

    public function forceAddBitmask(object $model, int $bitmask): bool
    {
        if (! ($model->permissions & $bitmask)) {
            $model->permissions |= $bitmask;
            return $model->save();
        }
        return false;
    }

    public function forceAddBitmaskById(object $model, int $permissionId): bool
    {
        $permission = $this->permissionRepository->findById($permissionId);
        return $permission ? $this->forceAddBitmask($model, $permission->bitmask) : false;
    }

    public function forceAddBitmaskByCode(object $model, string $code): bool
    {
        $permission = $this->permissionRepository->findByCode($code);
        return $permission ? $this->forceAddBitmask($model, $permission->bitmask) : false;
    }

    public function removeBitmask(object $model, int $bitmask): bool
    {
        if ($model->permissions & $bitmask) {
            $model->permissions &= ~$bitmask;
            return $model->save();
        }
        return false;
    }

    public function removeBitmaskByCode(object $model, string $code): bool
    {
        $permission = $this->permissionRepository->findByCode($code);
        if ($permission && ($model->permissions & $permission->bitmask)) {
            $model->permissions &= ~$permission->bitmask;
            return $model->save();
        }
        return false;
    }

    public function replaceBitmaskByCode(object $model, string $oldCode, string $newCode): bool
    {
        $removed = $this->removeBitmaskByCode($model, $oldCode);
        $added = $this->addBitmaskByCode($model, $newCode);
        return $removed || $added;
    }

    public function getModelsWithPermission(string $code, string $modelClass): Collection
    {
        return $this->permissionRepository->getModelsWithPermission($code, $modelClass);
    }

    /**
     * @return array<int, Permission>
     */
    public function getModelPermissions(object $model): array
    {
        $permissions = $this->permissionRepository->getAllPermissions();
        $bitmask = (int) ($model->permissions ?? 0);
        $modelClass = get_class($model);

        $specific = $permissions->filter(
            fn(Permission $permission) => $permission->permissible_type === $modelClass &&
                (($bitmask & $permission->bitmask) === $permission->bitmask)
        );

        $global = $permissions->filter(
            fn(Permission $permission) => is_null($permission->permissible_type) &&
                (($bitmask & $permission->bitmask) === $permission->bitmask)
        );

        return $specific->concat($global)->values()->all();
    }

    public function syncPermissionsByCodes(object $model, array $codes): bool
    {
        $permissions = $this->permissionRepository->getPermissionsByCodes($codes);
        $newBitmask = 0;

        foreach ($permissions as $permission) {
            $newBitmask |= $permission->bitmask;
        }

        if ($model->permissions !== $newBitmask) {
            $model->permissions = $newBitmask;
            return $model->save();
        }
        return false;
    }

    public function createPermission(array $data): Permission
    {
        $this->validateBitmask($data);
        return $this->permissionRepository->create($data);
    }

    public function updatePermission(Permission $permission, array $data): bool
    {
        $this->validateBitmask($data, $permission);
        return $this->permissionRepository->update($permission, $data);
    }

    protected function validateBitmask(array $data, ?Permission $existing = null): void
    {
        $bitmask = $data['bitmask'] ?? null;
        $type = $data['permissible_type'] ?? null;

        if ($bitmask === null) {
            throw ValidationException::withMessages(['bitmask' => 'Bitmask is required.']);
        }

        if ($type === null) {
            throw ValidationException::withMessages(['permissible_type' => 'permissible_type is required.']);
        }

        $systemConflict = Permission::query()
            ->whereNull('permissible_type')
            ->where('bitmask', $bitmask);

        if ($existing) {
            $systemConflict->where('id', '!=', $existing->id);
        }

        if ($systemConflict->exists()) {
            throw ValidationException::withMessages([
                'bitmask' => "Bitmask {$bitmask} already used in global permissions.",
            ]);
        }

        $typeConflict = Permission::query()
            ->where('permissible_type', $type)
            ->where('bitmask', $bitmask);

        if ($existing) {
            $typeConflict->where('id', '!=', $existing->id);
        }

        if ($typeConflict->exists()) {
            throw ValidationException::withMessages([
                'bitmask' => "Bitmask {$bitmask} already used for type {$type}.",
            ]);
        }
    }

    public function deletePermission(Permission $permission): bool
    {
        return $this->permissionRepository->delete($permission);
    }

    public function hasBitmask(mixed $model, int $bitmask): bool
    {
        return (($model->permissions ?? 0) & $bitmask) === $bitmask;
    }

    public function getBitmaskByCode(string $code): int
    {
        return $this->permissionRepository->findByCode($code)?->bitmask ?? 0;
    }

    public function getPermissionsByBitmaskAndClass(int $bitmask, ?string $modelClass = null): Collection
    {
        $permissions = $this->permissionRepository->getAllPermissions();

        return $permissions->filter(function (Permission $permission) use ($bitmask, $modelClass) {
            if (($bitmask & $permission->bitmask) !== $permission->bitmask) {
                return false;
            }
            if ($modelClass === null) {
                return is_null($permission->permissible_type);
            }
            return $permission->permissible_type === $modelClass || is_null($permission->permissible_type);
        })->values();
    }

    public function checkPolicy(object|string|null $model, ?string $method, ?int $id = null): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        $class = is_object($model) ? get_class($model) : $model;
        if (! $class && ! $method) {
            return false;
        }

        $queries = [];

        // Specific object + method
        if ($class && $id && $method) {
            $queries[] = PermissionPolicy::where('permissible_type', $class)
                ->where('permissible_id', $id)
                ->where('crud_method', $method);
        }

        // Any object of this class + method
        if ($class && $method) {
            $queries[] = PermissionPolicy::where('permissible_type', $class)
                ->whereNull('permissible_id')
                ->where('crud_method', $method);
        }

        // Global policy for method
        if ($method) {
            $queries[] = PermissionPolicy::whereNull('permissible_type')
                ->whereNull('permissible_id')
                ->where('crud_method', $method);
        }

        foreach ($queries as $query) {
            $policy = $query->first();
            if ($policy && $this->hasBitmask($user, $policy->bitmask)) {
                return true;
            }
        }

        return false;
    }
}
