<?php

namespace Indigoram89\Laravel\Epay;

use Illuminate\Support\ServiceProvider;
use Indigoram89\Laravel\Epay\Contracts\Epay as EpayContract;

class EpayServiceProvider extends ServiceProvider
{
	/**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

	/**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/epay.php' => config_path('epay.php'),
        ]);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('epay', function ($app) {
            return new Epay($app);
        });

        $this->app->alias('epay', EpayContract::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['epay'];
    }
}
