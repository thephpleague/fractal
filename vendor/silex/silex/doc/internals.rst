Internals
=========

This chapter will tell you a bit about how Silex works
internally.

Silex
-----

Application
~~~~~~~~~~~

The application is the main interface to Silex. It implements Symfony2's
`HttpKernelInterface
<http://api.symfony.com/master/Symfony/Component/HttpKernel/HttpKernelInterface.html>`_,
so you can pass a `Request
<http://api.symfony.com/master/Symfony/Component/HttpFoundation/Request.html>`_
to the ``handle`` method and it will return a `Response
<http://api.symfony.com/master/Symfony/Component/HttpFoundation/Response.html>`_.

It extends the ``Pimple`` service container, allowing for flexibility on the
outside as well as the inside. You could replace any service, and you are also
able to read them.

The application makes strong use of the `EventDispatcher
<http://api.symfony.com/master/Symfony/Component/EventDispatcher/EventDispatcher.html>`_
to hook into the Symfony2 `HttpKernel
<http://api.symfony.com/master/Symfony/Component/HttpKernel/HttpKernel.html>`_
events. This allows fetching the ``Request``, converting string responses into
``Response`` objects and handling Exceptions. We also use it to dispatch some
custom events like before/after middlewares and errors.

Controller
~~~~~~~~~~

The Symfony2 `Route
<http://api.symfony.com/master/Symfony/Component/Routing/Route.html>`_ is
actually quite powerful. Routes can be named, which allows for URL generation.
They can also have requirements for the variable parts. In order to allow
setting these through a nice interface, the ``match`` method (which is used by
``get``, ``post``, etc.) returns an instance of the ``Controller``, which
wraps a route.

ControllerCollection
~~~~~~~~~~~~~~~~~~~~

One of the goals of exposing the `RouteCollection
<http://api.symfony.com/master/Symfony/Component/Routing/RouteCollection.html>`_
was to make it mutable, so providers could add stuff to it. The challenge here
is the fact that routes know nothing about their name. The name only has
meaning in context of the ``RouteCollection`` and cannot be changed.

To solve this challenge we came up with a staging area for routes. The
``ControllerCollection`` holds the controllers until ``flush`` is called, at
which point the routes are added to the ``RouteCollection``. Also, the
controllers are then frozen. This means that they can no longer be modified
and will throw an Exception if you try to do so.

Unfortunately no good way for flushing implicitly could be found, which is why
flushing is now always explicit. The Application will flush, but if you want
to read the ``ControllerCollection`` before the request takes place, you will
have to call flush yourself.

The ``Application`` provides a shortcut ``flush`` method for flushing the
``ControllerCollection``.

.. tip::

    Instead of creating an instance of ``RouteCollection`` yourself, use the
    ``$app['controllers_factory']`` factory instead.

Symfony2
--------

Following Symfony2 components are used by Silex:

* **HttpFoundation**: For ``Request`` and ``Response``.

* **HttpKernel**: Because we need a heart.

* **Routing**: For matching defined routes.

* **EventDispatcher**: For hooking into the HttpKernel.

For more information, `check out the Symfony website <http://symfony.com/>`_.
