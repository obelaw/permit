<?php

namespace Obelaw\Permit\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Obelaw\Permit\Models\PermitRule;

class PermitUser extends Authenticatable
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'rule_id',
        'name',
        'email',
        'password',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function rule()
    {
        return $this->hasOne(PermitRule::class, 'id', 'rule_id');
    }
}
