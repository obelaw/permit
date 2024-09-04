<?php

namespace Obelaw\Permit\Models;

use Illuminate\Database\Eloquent\Model;

class PermitRule extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'has_all_permissions',
        'resource_permissions',
        'page_permissions',
        'widget_permissions',
    ];

    protected $casts = [
        'has_all_permissions' => 'boolean',
        'resource_permissions' => 'array',
        'page_permissions' => 'array',
        'widget_permissions' => 'array',
    ];
}
