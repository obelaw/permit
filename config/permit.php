<?php

return [
    /**
     * The authentication guard used by the package.
     * This guard is used to authenticate users for the permit system.
     */
    'guard' => 'web',

    /*
    |--------------------------------------------------------------------------
    | Suspend All Users
    |--------------------------------------------------------------------------
    |
    | When enabled, all users are suspended from accessing the permit panel.
    |
    */
    'suspend_all_users' => false,

    /*
    |--------------------------------------------------------------------------
    | Suspend All Users Exceptions
    |--------------------------------------------------------------------------
    |
    | Users with these email addresses can still access the panel even when
    | suspend_all_users is enabled.
    |
    */
    'suspend_all_users_exceptions' => [],

    /*
    |--------------------------------------------------------------------------
    | Auto-Suspend Inactive Users
    |--------------------------------------------------------------------------
    |
    | When enabled, users whose last_active_at exceeds the configured number
    | of minutes will be automatically suspended on their next request.
    |
    */
    'auto_suspend_inactive' => false,
    'auto_suspend_inactive_after_minutes' => 60,

    'user' => [
        'can_create' => true,
        'can_select' => false,
        'prevent_self_deactivation' => true,
        'prevent_self_delete' => true,
    ]
];
