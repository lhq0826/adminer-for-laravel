# Lumener
This package integrates the adminer interface into your Lumen or Laravel project by acting as a wrapper and taking care of incompatibility issues. Lumener also provides means to update or stylize the adminer code through the artisan command line.
[Adminer](https://www.adminer.org) is a full-featured database management tool written in PHP.

This was initially forked from [leung/laravel-adminer](https://github.com/lhq0826/adminer-for-laravel). The package was developed and tested using Lumen. So, Laravel support is untested (Although no changes should break it). Feel free to test on Laravel and open issues and/or submit a pull request.

## Requirements
### General
* guzzlehttp/guzzle
### Lumen
* illuminate/cookie
* illuminate/session
***
## Installation

```bash
composer require hgists/lumener
```
***
## Additional Steps [***Lumen Only***]
Adminer uses cookies to store the user session. If you haven't already, you must enable cookies and session.

#### Extra Packages
You must manually require the cookie and session packages.
```bash
composer require illuminate/cookie
composer require illuminate/session
```
#### Binding Session
Then you must add the required bindings in `bootstrap/app.php` before `return $app;`
```php
// Enable cookies/session
$app->singleton('cookie', function () use ($app) {
    return $app->loadComponent('session', 'Illuminate\Cookie\CookieServiceProvider', 'cookie');
});
$app->bind(Illuminate\Session\SessionManager::class, function ($app) {
    return $app->make('session');
});
$app->bind('Illuminate\Contracts\Cookie\QueueingFactory', 'cookie');
$app->register(Illuminate\Session\SessionServiceProvider::class);
$app->withFacades();
```
***

## Provider

The provider will automatically create any required roots and enable the artisan commands.

### Lumen

open your `bootstrap/app.php` and add this line anywhere before `return $app;`
```php
$app->register(\Lumener\LumenerServiceProvider::class);
```

### Laravel ≤ 5.4
Open your `config/app.php` and add this line in providers section
```php
Lumener\LumenerServiceProvider::class
```

### Laravel 5.5+ providers
Auto package discovery should add the provider.
```php
   // no need to add anything!!!
```
***
## Artisan Commands

### Updating Adminer

```bash
php artisan lumener:update [OPTIONAL --force]
```

You can configure your composer.json to do this after each commit:

```js
"scripts": {
    "post-install-cmd": [
        "php artisan lumener:update"
    ],
    "post-update-cmd": [
        "php artisan lumener:update"
    ]
}
```

### Themes

```php
php artisan lumener:stylize [OPTIONAL --file] [OPTIONAL --url]
```
#### Default

If no arguments provided, this command will install the default theme already packed in with Lumener: [Material Design for Adminer](https://github.com/arcs-/Adminer-Material-Theme)
```php
php artisan lumener:stylize
```
![alt text](https://camo.githubusercontent.com/3ff37a054b36216ccb8f9cf4259eead8ff12318d/68747470733a2f2f7374696c6c682e6172742f70726f6a6563742f61646d696e65722f707265766965772e706e67 "Logo Title Text 1")

#### File

```php
php artisan lumener:stylize --file=/home/Downloads/adminer.css
```

#### URL

```php
php artisan lumener:stylize --url=https://raw.githubusercontent.com/pappu687/adminer-theme/master/adminer.css
```

For themes containing images/JavaScript you will have to copy the files manually to your `public` path.

### Plugins
Install any [plugin](https://www.adminer.org/en/plugins/)
```php
php artisan lumener:plugin [OPTIONAL --file] [OPTIONAL --url]
```
Plugins must be enabled in `config('lumener.adminer_plugins')`. Refer to the config section.
#### Default

If no arguments provided, this command will install the plugin.php file which is required for any plugins to run.
```php
php artisan lumener:plugin
```

#### File

```php
php artisan lumener:plugin --file=/home/Downloads/designer.php
```

#### URL

```php
php artisan lumener:plugin --url=https://raw.github.com/vrana/adminer/master/plugins/database-hide.php
```
***
## Custom Config
You don't need to create a config file as all configuration parameters have a fallback value. You can follow the following instructions to customize the configuration.
Create a `config/lumener.php` file or use `php artisan vendor:publish` (Laravel only, includes default theme)
For **Lumen**, you must also add the following line to `bootstrap/app.php` before `return $app;`
```php
$app->configure('lumener');
```
### Config File (defaults)
```php
return [
  /* For Lumen, a route that has ("as" => "lumener") will be automatically
    merged into main route while keeping its original path and middleware.
    For Laravel, this MUST be identical to any route with custom middleware. */
  "name" => "Lumener",
  "route" => "lumener",
  "redundant_vars" => ['redirect','cookie','view'],
  // adminer_version can be exact (e.g. v4.7.1) if version_source is NOT "url"
  "adminer_version" => "https://api.github.com/repos/vrana/adminer/releases/latest",
  "version_source" => "url",

  // Check https://github.com/vrana/adminer/releases for custom releases
  //  (e.g. adminer-{version}-mysql-en.php or editor-{version}.php)
  //  This format supports v4.2.5+
  "adminer_source" => "https://github.com/vrana/adminer/releases/download/v{version}/adminer-{version}.php",


  /**
   * Plugins
   */

  // plugin.php is required to use any plugin
  // Automatically used when no file/url is supplied for the plugin command
  "plugin_source" => "https://raw.github.com/vrana/adminer/master/plugins/plugin.php",

  // Uncomment this section to enable plugins
  // "adminer_plugins" => [
  //   // No constructor arguments
  //   // "AdminerDumpXml" => [],
  //   // With constructor arguments
  //   // "AdminerFileUpload" => ["data/"],
  // ],


  /**
   * Autologin Settings
   */
  "adminer_autologin" => false,

  // Uncomment this section to override .env values
  // "db_host" => "127.0.0.1",
  // "db_port" => 3306,
  // "db_username" => "root",
  // "db_password" => "toor",
  // "db_database" => "mydatabase"
];

```
***
## Custom Middleware

Middleware is added in alongside the routes via the service provider. To override this, you may add a lumener route to your own routes file (e.g. `routes/web.php`)

##### Lumen Example

```php
$router->addRoute(null, 'lumener', ['middleware' => ['auth'], 'uses' => '\Simple\Adminer\Controllers\AdminerController@index', 'as' => 'lumener']);
```
Using `null` for methods is important to successfully merge the custom route with the default route.
The route path here will override the one in `config('lumener.route')`.
##### Laravel Example

```php
Route::any('lumener', '\Lumener\Controllers\LumenerController@index')->middleware('lumener_custom_middleware')->name('lumener');
```
The route path used here MUST match the one in `config('lumener.route')`, otherwise both routes will exist independently.

To add a custom middleware group, you will need to add it to `$middlewareGroups` in your `app/Http/Kernel.php`
```php
protected $middlewareGroups = [
    ...
    'lumener_custom_middleware' => [
        \App\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,

        // you may create customized middleware to fit your needs
        ...
    ],
];
```


Also add the lumener route to `$except` if you are using the example group to avoid CSRF issues
```php
protected $except = [
     'lumener'
 ];
 ```
