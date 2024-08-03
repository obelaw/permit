<?php

namespace Obelaw\Permit\Models;

use Obelaw\Framework\Base\ModelBase;

class Rule extends ModelBase
{
    protected $table = 'admin_rules';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'has_all_permissions',
        'permissions',
    ];

    protected $casts = [
        'has_all_permissions' => 'boolean',
        'permissions' => 'array',
    ];
}
