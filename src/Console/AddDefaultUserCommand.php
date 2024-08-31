<?php

declare(strict_types=1);

namespace Obelaw\Permit\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Obelaw\Permit\Models\PermitRule;
use Obelaw\Permit\Models\PermitUser;

final class AddDefaultUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permit:add-default-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add default user to permit';

    /**
     * Summary of handle
     * @return void
     */
    public function handle(): void
    {

        if (!PermitUser::first()) {
            $email = 'admin@obelaw.test';
            $password = '123456';

            $rule = PermitRule::create([
                'name' => 'Super Admin',
                'permissions' => ['*'],
            ]);

            PermitUser::create([
                'rule_id' => $rule->id,
                'name' => 'Super Admin',
                'email' => $email,
                'password' => Hash::make($password),
            ]);

            $this->line('<fg=white;bg=blue> Permit USER CREATED </>');
            $this->line('Email: ' . $email);
            $this->line('Password: ' . $password);
        } else {
            $this->line('<fg=black;bg=yellow> Permit HAS USER </>');
        }
    }
}
