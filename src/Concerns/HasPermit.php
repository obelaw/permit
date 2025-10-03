<?php

namespace Obelaw\Permit\Concerns;

use Obelaw\Permit\Models\PermitUser;

trait HasPermit
{
    /**
     * Get the relationship for the authable attributes.
     */
    public function authable()
    {
        return $this->morphOne(PermitUser::class, 'authable');
    }
}
