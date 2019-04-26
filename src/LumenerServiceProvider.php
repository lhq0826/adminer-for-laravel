<?php

namespace Lumener;

use Illuminate\Support\ServiceProvider;
use Lumener\Console\UpdateCommand;
use Lumener\Console\StylizeCommand;

class LumenerServiceProvider extends ServiceProvider
{
    protected $namespace = 'Lumener\Controllers';
    protected $middleware = [
            'encrypt_cookies' => \Illuminate\Cookie\Middleware\EncryptCookies::class,
            'add_queue_cookies' => \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            'start_session' => \Illuminate\Session\Middleware\StartSession::class
        ];
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
          __DIR__.'/public/adminer.css' => base_path('public'),
        ], 'public');
        $this->publishes([
          __DIR__.'/config/lumener.php' => base_path('config'),
        ], 'config');
        $this->loadViewsFrom(__DIR__.'/views', 'adminer');
        $this->path = config('lumener.route', 'lumener');
        $this->name = 'lumener';
        $this->map();
    }

    public function map()
    {
        $this->mapAdminerRoutes();
    }

    public function mapAdminerRoutes()
    {
        if (method_exists(\Route::class, "middleware")) {
            // Laravel
            // $url = route($this->name);
            // if ($url) {
            //     // TODO: Merge routes for laravel
            //     $this->path = $url;
            // }
            \Route::middleware(array_keys($this->middleware))
                ->namespace($this->namespace)
                ->group(function () {
                    Route::any($this->path, 'LumenerController@index');
                });
        } else {
            // Lumen
            $named = $this->app->router->namedRoutes;
            if (isset($named[$this->name])) {
                $uri = $named[$this->name];
                $route = $this->app->router->getRoutes()[$uri];
                $this->middleware = array_unique(array_merge(
                    array_keys($this->middleware),
                    $route['action']['middleware']
                ));
                $this->path = $uri;
            }
            $this->app->router->group([
                'namespace' => $this->namespace,
                'middleware' => $this->middleware,
                'as' => $this->name
            ], function ($router) {
                $router->addRoute(
                    ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
                    $this->path,
                    'LumenerController@index'
                );
            });
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if (method_exists($this->app, 'routeMiddleware')) {
            // Lumen
            $this->app->routeMiddleware(
                $this->middleware
                );
        } elseif (!method_exists($this->app['router'], 'middleware')) {
            // Laravel 5.4 no longer has middleware function on the router class
            foreach ($this->middleware as $alias => $class) {
                $this->app['router']->aliasMiddleware($alias, $class);
            }
        } else {
            foreach ($middleware as $alias => $class) {
                $this->app['router']->middleware($alias, $class);
            }
        }


        $this->app->singleton(
            'command.adminer.update',
            function ($app) {
                return new UpdateCommand($app['files']);
            }
        );
        $this->commands(
            'command.adminer.update'
        );

        $this->app->singleton(
            'command.adminer.stylize',
            function ($app) {
                return new StylizeCommand($app['files']);
            }
        );
        $this->commands(
            'command.adminer.stylize'
        );
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('command.adminer.update');
    }
}
