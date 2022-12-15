<?php

namespace Vtlabs\Ecommerce;

use Illuminate\Support\ServiceProvider;

class EcommerceServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/vtlabs_ecommerce.php' => config_path('vtlabs_ecommerce.php'),
        ], 'config');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');

        // translations
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'vtlabs_ecommerce');
        $this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/vtlabs_ecommerce'),
        ], 'translations');

        // assets
        $this->publishes([
            __DIR__.'/../assets' => public_path('assets/vendor/product/'),
        ], 'public');
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
    }
}