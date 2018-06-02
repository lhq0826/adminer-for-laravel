# adminer-for-laravel 5.*
[Adminer](https://www.adminer.org) is a full-featured database management tool written in PHP.
This package automates setup for adminer and routes your.domain.here/adminer to the the adminer.php script.

## Installation

#### Composer
```bash
composer require leung/laravel-adminer
```
OR
#### Add directly to your `composer.json`
```json
"require": {
    "leung/laravel-adminer": "^2.0"
},
```

## Providers

### Laravel <5.4 
open your `config/app.php` and add this line in providers section
```php
Simple\Adminer\AdminerServiceProvider::class
```
###### OR prevent it being on production 
open your `app/Providers/AppServiceProvider.php` and add this line in the `register()` function
```php
if ($this->app->environment() !== 'production') {
    $this->app->register(\Simple\Adminer\AdminerServiceProvider::class);
}
```

### Laravel 5.5 providers 
auto included via automatic package discovery
```php
   // no need to add anything!!!
```

## Update adminer.php

#### Linux command line update
```bash
php artisan adminer:update
```

You can configure your composer.json to do this after each commit:

```js
"scripts": {
    "post-install-cmd": [
        "php artisan adminer:update"
    ],
    "post-update-cmd": [
        "php artisan adminer:update"
    ]
}
```
---
## [Optional] Middleware

#### Middleware is added in via the service provider 
to override this you may add a adminer route to your App routes.php

```php
    Route::any('adminer', '\Simple\Adminer\Controllers\AdminerController@index')->middleware('adminer_custom_middleware'); // where you defined your middleware in app/Http/Kernel.php
```

#### Add middleware group example
to add a custom middleware group you will need to add it to middlewareGroups in your `app/Http/Kernel.php`
```php
protected $middlewareGroups = [
    ...
    'adminer_custom_middleware' => [
        \App\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,

        // you may create customized middleware to fit your needs
        ...
    ],
];
```

#### Modify app/Http/Middleware/VerifyCsrfToken.php
also add adminer to $except array if you are using the example optional adminer group
```php
protected $except = [
     'adminer'
 ];
 ```

