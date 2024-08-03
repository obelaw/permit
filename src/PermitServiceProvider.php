<?php

namespace Obelaw\Permit;

use Illuminate\Support\ServiceProvider;
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
                'model' => \Obelaw\Permit\Models\Admin::class,
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
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources', 'obelaw-permit');
    }
}
