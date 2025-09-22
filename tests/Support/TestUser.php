<?php

declare(strict_types=1);

namespace Ma32kc\PermissionBitmask\Tests\Support;

use Illuminate\Foundation\Auth\User as Authenticatable;

class TestUser extends Authenticatable
{
    protected $table = 'users';
    protected $fillable = ['name', 'permissions'];
}
