<?php

declare(strict_types=1);

namespace Vendor\PermissionBitmask\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int         $id
 * @property string|null $permissible_type
 * @property int|null    $permissible_id
 * @property string      $crud_method
 * @property int         $bitmask
 * @property string|null $description
 *
 * @property-read Model|null      $permissible
 * @property-read Permission|null $permission
 */
class PermissionPolicy extends Model
{
    protected $table = 'permission_policies';

    protected $fillable = [
        'permissible_type',
        'permissible_id',
        'crud_method',
        'bitmask',
        'description',
    ];

    protected $casts = [
        'permissible_id' => 'integer',
        'bitmask'        => 'integer',
    ];

    public $timestamps = false;

    /**
     * Polymorphic relation to the permissible model.
     */
    public function permissible(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Optional relation back to the Permission entity.
     * (if you want to resolve the bitmask into a Permission model)
     */
    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class, 'bitmask', 'bitmask');
    }
}
