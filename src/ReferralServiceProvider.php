<?php

namespace ItmoSoft\Referrals;

use Illuminate\Support\ServiceProvider;

class ReferralServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/itmosoft-referrals.php',
            'itmosoft-referrals'
        );

        $this->app->singleton(ReferralManager::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/itmosoft-referrals.php' => config_path('itmosoft-referrals.php'),
        ], 'itmosoft-referrals-config');

        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations'),
        ], 'itmosoft-referrals-migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([]);
        }
    }
}
