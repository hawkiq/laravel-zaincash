<?php

namespace Hawkiq\LaravelZaincash;

use Illuminate\Support\ServiceProvider;
use Hawkiq\LaravelZaincash\Services\ZainCash;


class ZainCashServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('hawkiq-zaincash', function () {

            return new ZainCash();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        $this->publishes([
            __DIR__ . '/config/zaincash.php' =>  config_path('zaincash.php'),
        ], 'zaincash');
    }
}
