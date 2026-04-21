<?php

namespace Obelaw\Permit\Models;

use Obelaw\Twist\Base\BaseModel;

class PermitRule extends BaseModel
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

    public function giverRules()
    {
        return $this->hasMany(PermitGiverRule::class, 'rule_id', 'id');
    }
}
