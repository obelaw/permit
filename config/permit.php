<?php

return [
    /**
     * The authentication guard used by the package.
     * This guard is used to authenticate users for the permit system.
     */
    'guard' => 'web',

    'user' => [
        'can_create' => true,
        'can_select' => false,
    ]
];
