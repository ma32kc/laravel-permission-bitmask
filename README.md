# Laravel Permission Package

A lightweight and flexible permission management system for Laravel applications.  
It provides bitmask-based permissions, policies, middleware, and helper services for managing user access and entity-level restrictions.

## Features

- Bitmask-based permissions for efficient storage and checks  
- Permission policies for granular access control  
- Facade (`Permission`) and service layer abstraction  
- Middleware for route protection  
- Configurable via `config/permission.php`  
- Database migrations for `permissions` and `permission_policies`  
- Fully tested with PHPUnit

## Installation

Install via Composer:

```bash
composer require your-vendor/permission-package
```

Publish configuration and migrations:

```bash
php artisan vendor:publish --provider="YourVendor\PermissionServiceProvider" --tag=config
php artisan vendor:publish --provider="YourVendor\PermissionServiceProvider" --tag=migrations
php artisan migrate
```

## Usage

### Checking permissions in code

```php
use App\Facades\Permission;

// Check if a user has a permission
if (Permission::has($user, 'task.update')) {
    // allowed
}
```

### Middleware usage

Add to `app/Http/Kernel.php`:

```php
'permission.policy' => \App\Middleware\CheckPermissionPolicy::class,
```

Protect a route:

```php
Route::get('/admin', [AdminController::class, 'index'])
    ->middleware('permission.policy:admin.access');
```

### Publishing config

```bash
php artisan vendor:publish --tag=permission-config
```

Configuration file: `config/permission.php`

## Testing

Run PHPUnit tests:

```bash
composer test
```

or

```bash
vendor/bin/phpunit
```

## Contributing

Contributions are welcome. Please submit a Pull Request with tests.

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).
