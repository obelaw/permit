<?php

namespace Obelaw\Permit;

use Illuminate\Support\ServiceProvider;
use Obelaw\Permit\Console\AddDefaultUserCommand;
use Obelaw\Permit\Services\PermitService;

class PermitServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        config([
            'auth.guards.permit' => array_merge([
                'driver' => 'session',
                'provider' => 'permit',
            ], config('auth.guards.store', [])),
        ]);

        config([
            'auth.providers.permit' => array_merge([
                'driver' => 'eloquent',
                'model' => \Obelaw\Permit\Models\PermitUser::class,
            ], config('auth.providers.permit', [])),
        ]);

        $this->app->singleton('obelaw.permit', PermitService::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources', 'obelaw-permit');

        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

            $this->commands([
                AddDefaultUserCommand::class,
            ]);
        }
    }
}
