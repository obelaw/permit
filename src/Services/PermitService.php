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
        $this->permissions = array_merge(
            $admin->rule->resource_permissions ?? [],
            $admin->rule->page_permissions ?? [],
            $admin->rule->widget_permissions ?? [],
        );
    }

    public function can($permission)
    {
        if ($this->hasAllPermissions)
            return true;

        return in_array($permission, $this->permissions);
    }

    public function user()
    {
        return auth()->guard('permit')->user();
    }
}
