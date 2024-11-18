<?php

namespace Obelaw\Permit\Services;

use Illuminate\Support\Facades\Hash;
use Obelaw\Permit\Models\PermitRule;
use Obelaw\Permit\Models\PermitUser;

class UserService
{
    public function createUser(): mixed
    {
        if (!PermitUser::first()) {
            $email = 'admin@obelaw.test';
            $password = '123456';

            $rule = PermitRule::create([
                'name' => 'Super Admin',
                'has_all_permissions' => true,
            ]);

            return PermitUser::create([
                'rule_id' => $rule->id,
                'name' => 'Super Admin',
                'email' => $email,
                'password' => Hash::make($password),
            ]);
        }

        return false;
    }

}
