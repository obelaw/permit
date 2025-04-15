<?php

namespace Obelaw\Permit\Models;

use Obelaw\Twist\Base\BaseModel;

class PermitGiverRule extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'rule_id',
    ];

    public function user()
    {
        return $this->belongsTo(PermitUser::class, 'user_id', 'id');
    }

    public function rule()
    {
        return $this->belongsTo(PermitRule::class, 'rule_id', 'id');
    }
}
