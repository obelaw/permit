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
        'rule_id',
        'name',
        'email',
        'password',
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
        'is_active' => 'boolean',
    ];

    public function rule()
    {
        return $this->hasOne(PermitRule::class, 'id', 'rule_id');
    }
}
