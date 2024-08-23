<?php

namespace Obelaw\Permit\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Support\Facades\App;

class Login extends BaseLogin
{
    public function mount(): void
    {
        parent::mount();

        if (App::environment() == 'local')
            $this->form->fill([
                'email' => 'admin@obelaw.test',
                'password' => '123456',
                'remember' => true,
            ]);
    }
}
