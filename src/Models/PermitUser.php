<?php

namespace Obelaw\Permit\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Obelaw\Permit\Models\PermitRule;
use Obelaw\Permit\Observers\PermitUserObserver;
use Obelaw\Twist\Base\BaseModel;

#[ObservedBy([PermitUserObserver::class])]
class PermitUser extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'created_by',
        'rule_id',
        'can_create',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'can_create' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function rule()
    {
        return $this->belongsTo(PermitRule::class);
    }

    public function giverRules()
    {
        return $this->hasMany(PermitGiverRule::class, 'user_id', 'id');
    }

    public function authable()
    {
        return $this->morphTo();
    }
}
