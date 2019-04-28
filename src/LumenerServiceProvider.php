<?php

namespace Lumener;

use Illuminate\Support\ServiceProvider;
use Lumener\Console\UpdateCommand;
use Lumener\Console\StylizeCommand;
use Lumener\Console\PluginCommand;

class LumenerServiceProvider extends ServiceProvider
{
    protected $namespace = 'Lumener\Controllers';
    protected $route_options;
    protected $route_path;
    protected $route_name;
    protected $middleware = [
        // 'start_session' => \Illuminate\Session\Middleware\StartSession::class
    ];
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        define("LUMENER_STORAGE", config('lumener.storage', base_path('storage/lumener')));
        if (!is_dir(LUMENER_STORAGE)) {
            mkdir(LUMENER_STORAGE);
        }

        $this->route_path = config('lumener.route.path', 'lumener');

        $route_options = config('lumener.route.options', []);
        if (!isset($route_options['as'])) {
            $route_options['as'] = 'lumener';
        }
        if (!isset($route_options['uses'])) {
            $route_options['uses'] = 'LumenerController@index';
        }
        if (isset($route_options['middleware'])) {
            $route_options['middleware'] =
             array_unique(array_merge(
                 array_keys($this->middleware),
                 is_array($route_options['middleware']) ?
                  $route_options['middleware'] : [$route_options['middleware']]
            ));
        } else {
            $route_options['middleware'] = array_keys($this->middleware);
        }
        $this->route_options = $route_options;
        $this->route_name = $route_options['as'];
        $this->map();
    }

    public function map()
    {
        $this->mapAdminerRoutes();
    }

    public function mapAdminerRoutes()
    {
        if ($this->app->router instanceof \Laravel\Lumen\Routing\Router) {
            // Lumen
            $this->mapAdminerRoutesForLumen();
        } else {
            // Laravel
            $this->mapAdminerRoutesForLaravel();
        }
    }

    public function mapAdminerRoutesForLumen()
    {
        $named = $this->app->router->namedRoutes;
        if (isset($named[$this->route_name])) {
            $uri = $named[$this->route_name];
            $route = $this->app->router->getRoutes()[$uri];
            foreach ($route['action'] as $key => $value) {
                if ($key == "middleware") {
                    $this->route_options['middleware'] =
                     array_unique(array_merge(
                         $this->route_options['middleware'],
                         is_array($value) ? $value : [$value]
                    ));
                } elseif ($key == "uses") {
                    $this->route_options[$key] = "\\{$value}";
                } else {
                    $this->route_options[$key] = $value;
                }
            }
            $this->route_path = $uri;
        }
        $this->route_options['namespace'] = $this->namespace;
        $this->app->router->group($this->route_options, function ($router) {
            $router->addRoute(
                ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
                $this->route_path,
                ['uses' => $this->route_options['uses']]
            );
            $this->route_options['as'] = "lumener-resources";
            $this->route_options['uses'] = 'LumenerController@getResource';
            $router->get(
                $this->route_path . '/resources',
                ['uses' => 'LumenerController@getResource',
                 'as' => 'lumener-resources']
            );
        });
    }

    public function mapAdminerRoutesForLaravel()
    {
        // TODO: Merge routes for laravel
        \Route::namespace($this->namespace)
            ->group(function () {
                \Route::any($this->route_path, $this->route_options);
                $this->route_options['uses'] = "LumenerController@getResource'";
                \Route::get($this->route_path.'/resources', $this->route_options);
            });
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
            foreach ($this->middleware as $alias => $class) {
                $this->app['router']->middleware($alias, $class);
            }
        }


        $this->app->singleton(
            'command.lumener.update',
            function (/** @scrutinizer ignore-unused */ $app) {
                return new UpdateCommand();
            }
        );

        $this->app->singleton(
            'command.lumener.stylize',
            function (/** @scrutinizer ignore-unused */ $app) {
                return new StylizeCommand();
            }
        );
        $this->app->singleton(
            'command.lumener.plugin',
            function (/** @scrutinizer ignore-unused */ $app) {
                return new PluginCommand();
            }
        );
        $this->commands(
            ['command.lumener.update',
            'command.lumener.stylize',
            'command.lumener.plugin']
        );
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
                'command.lumener.update',
                'command.lumener.stylize',
                'command.lumener.plugin'
             ];
    }
}
