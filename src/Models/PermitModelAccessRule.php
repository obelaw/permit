<?php

namespace Obelaw\Permit\Models;

use Obelaw\Twist\Base\BaseModel;

class PermitModelAccessRule extends BaseModel
{
    protected $fillable = [
        'rule_id',
        'model_path',
        'field',
        'operator',
        'value',
        'boolean',
    ];

    public function rule()
    {
        return $this->belongsTo(PermitRule::class, 'rule_id');
    }
}
