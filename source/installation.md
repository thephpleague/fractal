---
layout: layout
title: Installation
---

Installation
============

Plates is available on [Packagist](https://packagist.org/packages/league/plates) and can be installed using [Composer](https://getcomposer.org/):

~~~.language-javascript
{
    "require": {
        "league/fractal": "0.7.*"
    }
}
~~~

Most modern frameworks will include Composer out of the box, but ensure the following file is included:

~~~.language-php
<?php

// Include the Composer autoloader
require 'vendor/autoload.php';
~~~

You can also use Fractal without using Composer by registing an autoloader function:

~~~.language-php
spl_autoload_register(function ($class) {
    $prefix = 'League\\Fractal\\';
    $base_dir = __DIR__ . '/src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // no, move to the next registered autoloader
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});
~~~

Or, use any other [PSR-4](http://www.php-fig.org/psr/psr-4/) compatible autoloader.