<?php

namespace Obelaw\Permit\Services;

use Illuminate\Foundation\Auth\User as Authenticatable;

class PermitService
{
    private bool $hasAllPermissions = false;
    private array $permissions = [];
    private ?string $defaultGuard = null;

    public function __construct()
    {
        $this->defaultGuard = config('obelaw.permit.guard');
        $this->initializePermissions();
    }

    /**
     * Initialize permissions for the current authenticated user
     */
    private function initializePermissions(): void
    {
        $user = $this->getAuthenticatedUser();
        
        if (!$user || !$user->authable || !$user->authable->rule) {
            $this->hasAllPermissions = false;
            $this->permissions = [];
            return;
        }

        $rule = $user->authable->rule;
        $this->hasAllPermissions = (bool) $rule->has_all_permissions;
        
        $this->permissions = array_merge(
            $rule->resource_permissions ?? [],
            $rule->page_permissions ?? [],
            $rule->widget_permissions ?? [],
        );
    }

    /**
     * Check if the user has a specific permission
     */
    public function can(string $permission): bool
    {
        if ($this->hasAllPermissions) {
            return true;
        }

        return in_array($permission, $this->permissions);
    }

    /**
     * Check if the user cannot perform a specific action
     */
    public function cannot(string $permission): bool
    {
        return !$this->can($permission);
    }

    /**
     * Check if the user has any of the given permissions
     */
    public function canAny(array $permissions): bool
    {
        if ($this->hasAllPermissions) {
            return true;
        }

        return !empty(array_intersect($permissions, $this->permissions));
    }

    /**
     * Check if the user has all of the given permissions
     */
    public function canAll(array $permissions): bool
    {
        if ($this->hasAllPermissions) {
            return true;
        }

        return empty(array_diff($permissions, $this->permissions));
    }

    /**
     * Get the current authenticated user
     */
    public function user(): ?Authenticatable
    {
        return auth()->guard($this->defaultGuard)->user();
    }

    /**
     * Get the authenticated user (alias for user method)
     */
    private function getAuthenticatedUser(): ?Authenticatable
    {
        return $this->user();
    }

    /**
     * Check if user is authenticated
     */
    public function check(): bool
    {
        return auth()->guard($this->defaultGuard)->check();
    }

    /**
     * Get all permissions for the current user
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * Check if user has all permissions
     */
    public function hasAllPermissions(): bool
    {
        return $this->hasAllPermissions;
    }

    /**
     * Refresh permissions for the current user
     */
    public function refreshPermissions(): void
    {
        $this->initializePermissions();
    }
}
