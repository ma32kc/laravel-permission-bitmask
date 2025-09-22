<?php

declare(strict_types=1);

namespace Vendor\PermissionBitmask\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Vendor\PermissionBitmask\Facades\Permission;

/**
 * Middleware to check access policy based on bitmask permissions.
 *
 * Resolves the model and CRUD method from route/controller,
 * then calls Permission::checkPolicy(...) for the current user.
 */
final class CheckPermissionPolicy
{
    /**
     * Handle an incoming request and check policy access.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $route = $request->route();
        $action = $route?->getAction();

        $controllerMethod = $action['controller'] ?? null;

        if ($controllerMethod && Str::contains($controllerMethod, '@')) {
            [$controllerClass, $crudMethod] = explode('@', $controllerMethod);

            $modelInstance = $this->resolveModelFromRoute($route->parameters());

            $modelClass = $modelInstance
                ? get_class($modelInstance)
                : ($this->resolveModelFromController($controllerClass) ?? null);

            $modelId = $modelInstance?->getKey();

            if (! Permission::checkPolicy($modelClass, $crudMethod, $modelId)) {
                abort(403, 'Access denied by permission policy.');
            }
        }

        return $next($request);
    }

    /**
     * Extract first Eloquent model from route parameters.
     *
     * @param  array<string, mixed>  $parameters
     */
    protected function resolveModelFromRoute(array $parameters): ?object
    {
        foreach ($parameters as $param) {
            if (is_object($param) && method_exists($param, 'getKey')) {
                return $param;
            }
        }
        return null;
    }

    /**
     * Determine model class associated with a controller.
     *
     * @param  class-string  $controllerClass
     * @return class-string|null
     */
    protected function resolveModelFromController(string $controllerClass): ?string
    {
        return method_exists($controllerClass, 'permissionModel')
            ? $controllerClass::permissionModel()
            : null;
    }
}
