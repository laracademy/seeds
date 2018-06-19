<?php

namespace Laracademy\Commands;

use Illuminate\Support\ServiceProvider;

class SeedsServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    public function boot()
    {
        //
    }

    public function register()
    {
        $this->app->singleton('command.laracademy.seeds', function($app) {
            return $app['Laracademy\Commands\SeedsCommand'];
        });

        $this->commands('command.laracademy.seeds');
    }

}