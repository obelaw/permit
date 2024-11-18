<?php

declare(strict_types=1);

namespace Obelaw\Permit\Console;

use Illuminate\Console\Command;
use Obelaw\Permit\Services\UserService;

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

        if ($user = (new UserService)->createUser()) {
            $this->line('<fg=white;bg=blue> Permit USER CREATED </>');
            $this->line('Email: ' . $user->email);
            $this->line('Password: ' . $user->password);
        } else {
            $this->line('<fg=black;bg=yellow> Permit HAS USER </>');
        }
    }
}
