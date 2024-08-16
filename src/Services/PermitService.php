<?php

namespace Obelaw\Permit\Services;

class PermitService
{
    private bool $hasAllPermissions = false;
    private ?array $permissions = [];

    public function __construct()
    {
        $admin = auth()->guard('permit')->user();

        $this->hasAllPermissions = $admin->rule->has_all_permissions;
        $this->permissions = $admin->rule->permissions;
    }

    public function can($permission)
    {
        if ($this->hasAllPermissions)
            return true;

        return in_array($permission, $this->permissions);
    }
}
