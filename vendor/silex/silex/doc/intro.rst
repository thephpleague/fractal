Introduction
============

Silex is a PHP microframework for PHP 5.3. It is built on the shoulders of
`Symfony2`_ and `Pimple`_ and also inspired by `Sinatra`_.

A microframework provides the guts for building simple single-file apps. Silex
aims to be:

* *Concise*: Silex exposes an intuitive and concise API that is fun to use.

* *Extensible*: Silex has an extension system based around the Pimple micro
  service-container that makes it even easier to tie in third party libraries.

* *Testable*: Silex uses Symfony2's HttpKernel which abstracts request and
  response. This makes it very easy to test apps and the framework itself. It
  also respects the HTTP specification and encourages its proper use.

In a nutshell, you define controllers and map them to routes, all in one step.

**Let's go!**::

    // web/index.php

    require_once __DIR__.'/../vendor/autoload.php';

    $app = new Silex\Application();

    $app->get('/hello/{name}', function ($name) use ($app) {
        return 'Hello '.$app->escape($name);
    });

    $app->run();

All that is needed to get access to the Framework is to include the
autoloader.

Next we define a route to ``/hello/{name}`` that matches for ``GET`` requests.
When the route matches, the function is executed and the return value is sent
back to the client.

Finally, the app is run. Visit ``/hello/world`` to see the result. It's really
that easy!

Installing Silex is as easy as it can get. `Download`_ the archive file,
extract it, and you're done!

.. _Download: http://silex.sensiolabs.org/download
.. _Symfony2: http://symfony.com/
.. _Pimple: http://pimple.sensiolabs.org/
.. _Sinatra: http://www.sinatrarb.com/
