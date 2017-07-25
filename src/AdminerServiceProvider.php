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
		Route::middleware(['encrypt_cookies', 'add_queue_cookies','start_session'])
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
		$this->app['router']->aliasMiddleware('encrypt_cookies', \Illuminate\Cookie\Middleware\EncryptCookies::class);
		$this->app['router']->aliasMiddleware('add_queue_cookies', \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class);
		$this->app['router']->aliasMiddleware('start_session', \Illuminate\Session\Middleware\StartSession::class);
	}
}
