## [rgehan/router-php](https://github.com/rgehan/router-php)

This is a really simple router, it has no external dependency.
It allows you to define routes, for specific verbs, and map them to a method in a controller class.

I loosely imitates Laravel router.

## Installation
Simple require it with Composer
```
composer require rgehan/router-php
```

## Usage
This code should reside in a PHP file where all requests are redirected 
to (with an `.htaccess` file for example).
```php
<?php

// Requires the Composer autoloader, allowing us to use external modules
require_once(__DIR__ . "/../vendor/autoload.php");

use rgehan\RouterPHP\Router;

// Sets the namespace in which the Router will look for controllers
Router::setControllerNamespace('\\rgehan\\myProject\\controllers\\');

// Sets the variables that will be passed to all controllers methods
Router::setRoutesGlobalParameters(['global variable 1', 123, [1, 2, 3]]);

// Defines the routes
Router::get('/', 'HomeController@index');
Router::get('/home', 'HomeController@index');
Router::get('/articles', 'ArticlesController@index');
Router::get('/article', 'ArticlesController@get');

Router::post('/search/name', 'ArticlesController@search');

Router::delete('/article', 'ArticlesController@delete');

Router::update('/article', 'ArticlesController@delete');

// Dispatch the current request to the correct controller/method
Router::dispatch();
```

## Current limitation
Unlike many routers, this one doesn't allow you to define dynamic parameters in the url (such as `/article/{id}` for example).
You still have to rely on good'ol `$_GET`, or `$_POST` to get your data.

After all, it's just a router, not a full framework :)

