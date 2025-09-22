<?php

declare(strict_types=1);

namespace Ma32kc\PermissionBitmask\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int         $id
 * @property string      $code
 * @property string|null $description
 * @property int         $bitmask
 * @property string|null $permissible_type
 * @property int|null    $bitmask_next
 * @property int|null    $bitmask_disabled
 * @property int|null    $bitmask_flush
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PermissionPolicy> $policies
 */
class Permission extends Model
{
    /**
     * Table name.
     *
     * @var string
     */
    protected $table = 'permissions';

    /**
     * Mass assignable attributes.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'description',
        'bitmask',
        'permissible_type',
        'bitmask_next',
        'bitmask_disabled',
        'bitmask_flush',
    ];

    /**
     * Attribute casting.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'bitmask'          => 'integer',
        'bitmask_next'     => 'integer',
        'bitmask_disabled' => 'integer',
        'bitmask_flush'    => 'integer',
    ];

    /**
     * Relation: policies that use this permission.
     */
    public function policies(): HasMany
    {
        return $this->hasMany(PermissionPolicy::class, 'bitmask', 'bitmask');
    }
}
