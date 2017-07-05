# adminer-for-laravel 5.*
[Adminer](https://www.adminer.org) is a full-featured database management tool written in PHP.

## Installation
```php
composer require leung/laravel-adminer
```

open your `config/app.php` and add this line in providers section
```php
Simple\Adminer\AdminerServiceProvider::class
```

Modify app/Http/Middleware/VerifyCsrfToken.php, add adminer to $except array:
```php
protected $except = [
     'adminer'
 ];
 ```
add route
```php
Route::any('adminer', '\Simple\Adminer\Controllers\AdminerController@index');
```
