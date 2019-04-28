# Lumener

[![Scrutinizer Code Quality][ico-quality]][link-quality]
[![Build Status][ico-build]][link-build]
[![Latest Packagist Version][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)

[Adminer](https://www.adminer.org) is a full-featured database management tool written in PHP.
This package integrates the adminer interface into your Lumen or Laravel project by acting as a wrapper and taking care of incompatibility issues. Lumener also provides means to update, stylize or extend adminer through the artisan commands.

This was initially forked from [leung/laravel-adminer](https://github.com/lhq0826/adminer-for-laravel). The package was developed and tested using Lumen. So, Laravel support is untested (Although no changes should break it). Feel free to test on Laravel and open issues and/or submit a pull request!

## Requirements
### General
* guzzlehttp/guzzle
### Lumen
* illuminate/cookie
* illuminate/session
***
## Installation

```bash
# Install package
composer require hgists/lumener
# Download latest Adminer
php artisan lumener:update
# [Optional] Apply theme
php artisan lumener:stylize
```
For Lumen or Laravel 5.4 or older, see the next section.
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

### Laravel 5.5+
Auto package discovery should add the provider.
***
## Config
You don't need to create a config file as all configuration parameters have a fallback value. You can follow the following instructions to customize the configuration.
* Create a `config/lumener.php` file or use `php artisan vendor:publish` (Laravel only)
* For **Lumen**, you must also add the following line to `bootstrap/app.php` before `return $app;`
```php
$app->configure('lumener');
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

You can also create a route to update lumener with the action `\Lumener\Controllers\LumenerController@update`.

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

For more themes, check the [Adminer](https://www.adminer.org/#extras) website.

#### File

```php
php artisan lumener:stylize --file=/home/Downloads/adminer.css
```

#### URL

```php
php artisan lumener:stylize --url=https://raw.githubusercontent.com/vrana/adminer/master/designs/lucas-sandery/adminer.css
```

For themes containing images/JavaScript you will have to copy the files manually to your `public` path.

### Plugins
Install or update any [plugin](https://www.adminer.org/en/plugins/) given its path or url.
```php
php artisan lumener:plugin [OPTIONAL --file] [OPTIONAL --url]
```
Plugins must be enabled in `config('lumener.adminer.plugins.enabled')`. Refer to the config section.
#### Default

If no arguments provided, this command will install the `plugin.php` file which is required for any plugins to run.
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
## Extensions
Adminer supports [Extensions](https://www.adminer.org/en/extension/). In fact, Lumener takes advantage of quite a few extension functions. However, more extensions can be added using another user-defined class. This can be done all while preserving the original Lumener extensions, unless a conflict arises. Please take some time to check `src/logic/adminer_object` before writing your own extensions to be aware of potential conflicts.

To add your own extensions, set `config('lumener.adminer.extension_file')`.
```php
"adminer" => [
    ...
    "extension_file" => base_path("app/Logic/LumenerExtension.php")
    ...
]
```
**Example file:**
```php
<?php
// Lumener and $plugins are already defined before this file is included
class ExtendedLumener extends Lumener
{
    function permanentLogin() {
      // key used for permanent login
      return 'ca41d8e9879df648e9a43cefa97bc12d';
    }
}

if (empty($plugins)) {
    return new ExtendedLumener();
}
return new ExtendedLumener($plugins);
```
***
## Custom Route

You can modify route attributes in `config('lumener.route')`.

### Lumen Special
You may add a route to your own routes file (e.g. `routes/web.php`) with the name `lumener` and it will override all attributes, except for namespace.
```php
$router->addRoute(null, 'lumener', ['middleware' => ['auth'], 'as' => 'lumener']);
```
This also works if you add the route inside an existing group.
```php
$router->group(
    ['middleware' => ['encrypt_cookies', 'auth', 'level:100'], 'prefix' => 'admin'],
    function () use ($router) {
        $router->addRoute(null, 'lumener', ['as' => 'lumener']);
    }
);
```
Using specific HTTP methods is not supported, please keep it `null`.
The route path and options here will override `config('lumener.route')`.

### Laravel Special
You can define a middleware group named `lumener` and it will be automatically used in the `LumenerController`.

Additionally, add the lumener route to `$except` to avoid CSRF issues
```php
protected $except = [
     'lumener'
 ];
 ```

 ## Embedding

 The route can be redirected to a function in a user-defined controller. This is done by overriding the `uses` option either in `config('lumener.route.options.uses')` or in the user-defined route (**Lumen**).

 The following code is a simple example of how embedding might work.

 ```php
class AdminController{
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    public function lumener()
    {
        // If you are using a Content Seucrity Policy, define it here
        define("LUMENER_CSP", [["form-action" => "'self'"]]);
        $controller = new \Lumener\Controllers\LumenerController($this->request);
        $content = $controller->index();
        return view('admin.dashboard', ['content' => $content]);
    }
}
// Don't forget to use {!! $content !!} in blade as $content is HTML
 ```

 ## Credits

 - [Hesham Meneisi][link-author]
 - [All Contributors][link-contributors]

 ## License

 The MIT License (MIT). Please see [License File][link-license] for more information.

 [ico-version]: https://img.shields.io/packagist/v/ognjenm/serverreqcheck.svg?style=flat-square
 [ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
 [ico-build]: https://scrutinizer-ci.com/g/HeshamMeneisi/Lumener/badges/build.png?b=master
 [ico-quality]: https://scrutinizer-ci.com/g/HeshamMeneisi/Lumener/badges/quality-score.png?b=master

 [link-packagist]: https://packagist.org/packages/hgists/lumener
 [link-quality]:   https://scrutinizer-ci.com/g/HeshamMeneisi/Lumener/?branch=master
 [link-build]: https://scrutinizer-ci.com/g/HeshamMeneisi/Lumener/build-status/master
 [link-author]: https://github.com/heshammeneisi
 [link-contributors]: ../../contributors
 [link-license]: /LICENSE
