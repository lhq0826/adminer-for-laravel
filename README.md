# adminer-for-laravel 5.*
[Adminer](https://www.adminer.org) is a full-featured database management tool written in PHP.

#Installation
```
composer require leung/laravel-adminer
```

open your `config/app.php` and add this line in providers section
```
Simple\Adminer\AdminerServiceProvider::class
```

Modify app/Http/Middleware/VerifyCsrfToken.php, add adminer to $except array:
```
protected $except = [
     'adminer'
 ];
 ```