# Сáша frameworks
PHP7 Hmvc framework built for performance & simplicity
-----

**Сáша** is a full HMVC framework for PHP.
It was built with 2 concept in mind : Simplicity & Performance.

Performance : Everything is timed in order to find bottleneck quickly.

Simplicity : I make some questionable choice in order to avoid too much abstractions. Too many abstraction tends to have a lot of php class / interface loaded and since PHP is born to died at every request, compilation/running time (even with opcache) will be slow. 

## Warning
Be aware that this package is still in heavy developpement. 
Some breaking change will occure. Thank's for your comprehension.

## Installation

Install the latest version with

```bash
$ composer require cawa/cawa
```

## Basic Usage

```php
use Cawa\Core\App;
use Cawa\Router\Route;

putenv('APP_ENV=' . App::DEV);

$app = App::create(__DIR__);
$app->init();
App::router()->addRoutes([
    Route::create()->setName("main")->setMatch("/{{O:<name>[A-Za-z0-9]+}}")->setController(function(array $args = array())
    {
        return "Hello " . ($args["name"] ?? "Guest");
    }),
]);
$app->handle();
$app->end();
```

## Features
- HMVC framework : Because simple MVC sucks 
- Template : Phtml & Twig template engine support
- Http Router : strict type http router, with localized url, controller & callback 
- Error Handler : catch all error with pretty html output  
- Intl & l18n : really simple key value store v
- Events : because events is cool & help put timer on all your application 
- Db : Abstraction layer without realiying on PDO 
- HttpClient : a really simple http client that just work
- Cache : Redis & Apc cache abstraction
- Log : PSR3 logger with syslog handler (others coming asap)
- Session : file session (db, ... coming asap) without using the php session handler 
- Collection : filerable, sortable with callable
- Email : with Swift_Mailer as backend
- Date : with Carbon as backend

## About

### Performance 
My first bench can run a simple hello world at 6k queries per second.
In the same hardware, I ran symfony3 simple hello world app at 350 queries per second.

Plan is to support one of these lib in order to bosst performance: 
- [Swoole](https://github.com/swoole/swoole-src)
- [Amp](https://github.com/amphp/amp)
- [Icicle](https://github.com/icicleio/icicle)


### Requirements
- Сáша only work with PHP 7 or above
- [mbstring](http://php.net/manual/en/book.mbstring.php)
- [intl](http://php.net/manual/en/book.intl.php)
- [curl](http://php.net/manual/fr/book.curl.php)


### License

Cawa is licensed under the GPL v3 License - see the `LICENSE` file for details
