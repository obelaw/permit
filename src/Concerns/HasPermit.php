<?php

namespace Obelaw\Permit\Concerns;

use Filament\Panel;
use Obelaw\Permit\Models\PermitUser;

trait HasPermit
{
    /**
     * Determine if the user can access the Filament admin panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active;
    }

    /**
     * Get the polymorphic relationship to the PermitUser model.
     * 
     * This method establishes a one-to-one polymorphic relationship
     * between the current model and the PermitUser model.
     * 
     * @deprecated v1.0.0 Use permit() method instead for better naming consistency
     * @see permit()
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function authable()
    {
        return $this->morphOne(PermitUser::class, 'authable');
    }

    /**
     * Get the relationship for the authable attributes.
     */
    public function permit()
    {
        return $this->morphOne(PermitUser::class, 'authable');
    }

    /**
     * Determine if the user is active.
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->permit ? $this->permit->is_active : false;
    }

    /**
     * Determine if the user can create other users.
     */
    public function getRuleNameAttribute()
    {
        return $this->permit ? $this->permit->rule->name : null;
    }
}
