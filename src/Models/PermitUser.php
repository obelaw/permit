<?php

namespace Obelaw\Permit\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Obelaw\Permit\Models\PermitRule;
use Obelaw\Twist\Facades\Twist;

class PermitUser extends Authenticatable
{
    /**
     * Create a new instance of the Model.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if ($connection = Twist::getConnection()) {
            $this->setConnection($connection);
        }

        $this->setTable(config('obelaw.database.table_prefix', Twist::getPrefixTable()) . $this->getTable());
    }

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
