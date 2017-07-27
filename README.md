# adminer-for-laravel 5.*
[Adminer](https://www.adminer.org) is a full-featured database management tool written in PHP.

## Installation

### Composer
```php
composer require leung/laravel-adminer
```
OR
### Update `composer.json`
```php
"require": {
    "leung/laravel-adminer": "^1.1"
},
```


## Providers

### Laravel <5.4 open your `config/app.php` and add this line in providers section
```php
Simple\Adminer\AdminerServiceProvider::class
```
### Laravel 5.5 providers auto included via automatic package discovery
```php
   // no need to add anything!!!
```

---
## Middleware

### Modify app/Http/Middleware/VerifyCsrfToken.php, add adminer to $except array:
```php
protected $except = [
     'adminer'
 ];
 ```
Middleware is added in service provider, to override this you may add a adminer route to your App routes.php
```php
    Route::any('adminer', '\Simple\Adminer\AdminerController@index')->middleware('custom_middleware'); // where you defined your middleware in app/Http/Kernel.php
```

[optional] add middleware group Example

```php
protected $middlewareGroups = [
    ...
    'adminer' => [
        \App\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,

        // you may create customized middleware to fit your needs
        ...
    ],
];
```
