<?php

namespace Simple\Adminer;

use Route;
use Illuminate\Support\ServiceProvider;

class AdminerServiceProvider extends ServiceProvider
{
    protected $namespace = 'Simple\Adminer\Controllers';
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->map();
    }

    public function map()
    {
        $this->mapAdminerRoutes();
    }

    public function mapAdminerRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(__DIR__.'/routes.php');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
