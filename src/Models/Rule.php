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
        'permissions',
    ];

    protected $casts = [
        'permissions' => 'array'
    ];
}
