UrlGeneratorServiceProvider
===========================

The *UrlGeneratorServiceProvider* provides a service for generating URLs for
named routes.

Parameters
----------

None.

Services
--------

* **url_generator**: An instance of `UrlGenerator
  <http://api.symfony.com/master/Symfony/Component/Routing/Generator/UrlGenerator.html>`_,
  using the `RouteCollection
  <http://api.symfony.com/master/Symfony/Component/Routing/RouteCollection.html>`_
  that is provided through the ``routes`` service. It has a ``generate``
  method, which takes the route name as an argument, followed by an array of
  route parameters.

Registering
-----------

.. code-block:: php

    $app->register(new Silex\Provider\UrlGeneratorServiceProvider());

Usage
-----

The UrlGenerator provider provides a ``url_generator`` service::

    $app->get('/', function () {
        return 'welcome to the homepage';
    })
    ->bind('homepage');

    $app->get('/hello/{name}', function ($name) {
        return "Hello $name!";
    })
    ->bind('hello');

    $app->get('/navigation', function () use ($app) {
        return '<a href="'.$app['url_generator']->generate('homepage').'">Home</a>'.
               ' | '.
               '<a href="'.$app['url_generator']->generate('hello', array('name' => 'Igor')).'">Hello Igor</a>';
    });

When using Twig, the service can be used like this:

.. code-block:: jinja

    {{ app.url_generator.generate('homepage') }}

Moreover, if you have ``twig-bridge`` in your ``composer.json``, you will have access to the ``path()`` and
``url()`` functions:

.. code-block:: jinja

    {{ path('homepage') }}
    {{ url('homepage') }} {# generates the absolute url http://example.org/ #}
    {{ path('hello', {name: 'Fabien'}) }}
    {{ url('hello', {name: 'Fabien'}) }} {# generates the absolute url http://example.org/hello/Fabien #}

Traits
------

``Silex\Application\UrlGeneratorTrait`` adds the following shortcuts:

* **path**: Generates a path.

* **url**: Generates an absolute URL.

.. code-block:: php

    $app->path('homepage');
    $app->url('homepage');
