<?php

namespace Obelaw\Permit\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Obelaw\Permit\Models\Rule;

class Admin extends Authenticatable
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

    /**
     * Create a new instance of the Model.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('obelaw.database.table_prefix', 'obelaw_') . $this->getTable());
    }

    public function rule()
    {
        return $this->hasOne(Rule::class, 'id', 'rule_id');
    }
}
