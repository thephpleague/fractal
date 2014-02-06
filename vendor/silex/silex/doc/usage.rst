Usage
=====

This chapter describes how to use Silex.

Installation
------------

If you want to get started fast, `download`_ Silex as an archive and extract
it, you should have the following directory structure:

.. code-block:: text

    ├── composer.json
    ├── composer.lock
    ├── vendor
    │   └── ...
    └── web
        └── index.php

If you want more flexibility, use Composer instead. Create a
``composer.json``:

.. code-block:: json

    {
        "require": {
            "silex/silex": "~1.1"
        }
    }

And run Composer to install Silex and all its dependencies:

.. code-block:: bash

    $ curl -s http://getcomposer.org/installer | php
    $ php composer.phar install

.. tip::

    By default, Silex relies on the stable Symfony components. If you want to
    use their master version instead, add ``"minimum-stability": "dev"`` in
    your ``composer.json`` file.

Upgrading
---------

Upgrading Silex to the latest version is as easy as running the ``update``
command:

.. code-block:: bash

    $ php composer.phar update

Bootstrap
---------

To bootstrap Silex, all you need to do is require the ``vendor/autoload.php``
file and create an instance of ``Silex\Application``. After your controller
definitions, call the ``run`` method on your application::

    // web/index.php

    require_once __DIR__.'/../vendor/autoload.php';

    $app = new Silex\Application();

    // definitions

    $app->run();

Then, you have to configure your web server (read the :doc:`dedicated chapter
<web_servers>` for more information).

.. tip::

    When developing a website, you might want to turn on the debug mode to
    ease debugging::

        $app['debug'] = true;

.. tip::

    If your application is hosted behind a reverse proxy at address $ip, and you
    want Silex to trust the ``X-Forwarded-For*`` headers, you will need to run
    your application like this::

        use Symfony\Component\HttpFoundation\Request;

        Request::setTrustedProxies(array($ip));
        $app->run();

Routing
-------

In Silex you define a route and the controller that is called when that
route is matched.

A route pattern consists of:

* *Pattern*: The route pattern defines a path that points to a resource. The
  pattern can include variable parts and you are able to set RegExp
  requirements for them.

* *Method*: One of the following HTTP methods: ``GET``, ``POST``, ``PUT`` or
  ``DELETE``. This describes the interaction with the resource. Commonly only
  ``GET`` and ``POST`` are used, but it is possible to use the others as well.

The controller is defined using a closure like this::

    function () {
        // do something
    }

Closures are anonymous functions that may import state from outside of their
definition. This is different from globals, because the outer state does not
have to be global. For instance, you could define a closure in a function and
import local variables of that function.

.. note::

    Closures that do not import scope are referred to as lambdas. Because in
    PHP all anonymous functions are instances of the ``Closure`` class, we
    will not make a distinction here.

The return value of the closure becomes the content of the page.

Example GET route
~~~~~~~~~~~~~~~~~

Here is an example definition of a ``GET`` route::

    $blogPosts = array(
        1 => array(
            'date'      => '2011-03-29',
            'author'    => 'igorw',
            'title'     => 'Using Silex',
            'body'      => '...',
        ),
    );

    $app->get('/blog', function () use ($blogPosts) {
        $output = '';
        foreach ($blogPosts as $post) {
            $output .= $post['title'];
            $output .= '<br />';
        }

        return $output;
    });

Visiting ``/blog`` will return a list of blog post titles. The ``use``
statement means something different in this context. It tells the closure to
import the $blogPosts variable from the outer scope. This allows you to use it
from within the closure.

Dynamic routing
~~~~~~~~~~~~~~~

Now, you can create another controller for viewing individual blog posts::

    $app->get('/blog/{id}', function (Silex\Application $app, $id) use ($blogPosts) {
        if (!isset($blogPosts[$id])) {
            $app->abort(404, "Post $id does not exist.");
        }

        $post = $blogPosts[$id];

        return  "<h1>{$post['title']}</h1>".
                "<p>{$post['body']}</p>";
    });

This route definition has a variable ``{id}`` part which is passed to the
closure.

The current ``Application`` is automatically injected by Silex to the Closure
thanks to the type hinting.

When the post does not exist, we are using ``abort()`` to stop the request
early. It actually throws an exception, which we will see how to handle later
on.

Example POST route
~~~~~~~~~~~~~~~~~~

POST routes signify the creation of a resource. An example for this is a
feedback form. We will use the ``mail`` function to send an e-mail::

    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;

    $app->post('/feedback', function (Request $request) {
        $message = $request->get('message');
        mail('feedback@yoursite.com', '[YourSite] Feedback', $message);

        return new Response('Thank you for your feedback!', 201);
    });

It is pretty straightforward.

.. note::

    There is a :doc:`SwiftmailerServiceProvider <providers/swiftmailer>`
    included that you can use instead of ``mail()``.

The current ``request`` is automatically injected by Silex to the Closure
thanks to the type hinting. It is an instance of `Request
<http://api.symfony.com/master/Symfony/Component/HttpFoundation/Request.html>`_,
so you can fetch variables using the request ``get`` method.

Instead of returning a string we are returning an instance of `Response
<http://api.symfony.com/master/Symfony/Component/HttpFoundation/Response.html>`_.
This allows setting an HTTP status code, in this case it is set to ``201
Created``.

.. note::

    Silex always uses a ``Response`` internally, it converts strings to
    responses with status code ``200 Ok``.

Other methods
~~~~~~~~~~~~~

You can create controllers for most HTTP methods. Just call one of these
methods on your application: ``get``, ``post``, ``put``, ``delete``::

    $app->put('/blog/{id}', function ($id) {
        ...
    });

    $app->delete('/blog/{id}', function ($id) {
        ...
    });

.. tip::

    Forms in most web browsers do not directly support the use of other HTTP
    methods. To use methods other than GET and POST you can utilize a special
    form field with a name of ``_method``. The form's ``method`` attribute must
    be set to POST when using this field::

        <form action="/my/target/route/" method="post">
            ...
            <input type="hidden" id="_method" name="_method" value="PUT" />
        </form>

    If using Symfony Components 2.2+ you will need to explicitly enable this
    method override::

        use Symfony\Component\HttpFoundation\Request;

        Request::enableHttpMethodParameterOverride();
        $app->run();

You can also call ``match``, which will match all methods. This can be
restricted via the ``method`` method::

    $app->match('/blog', function () {
        ...
    });

    $app->match('/blog', function () {
        ...
    })
    ->method('PATCH');

    $app->match('/blog', function () {
        ...
    })
    ->method('PUT|POST');

.. note::

    The order in which the routes are defined is significant. The first
    matching route will be used, so place more generic routes at the bottom.


Route variables
~~~~~~~~~~~~~~~

As it has been shown before you can define variable parts in a route like
this::

    $app->get('/blog/{id}', function ($id) {
        ...
    });

It is also possible to have more than one variable part, just make sure the
closure arguments match the names of the variable parts::

    $app->get('/blog/{postId}/{commentId}', function ($postId, $commentId) {
        ...
    });

While it's not suggested, you could also do this (note the switched
arguments)::

    $app->get('/blog/{postId}/{commentId}', function ($commentId, $postId) {
        ...
    });

You can also ask for the current Request and Application objects::

    $app->get('/blog/{id}', function (Application $app, Request $request, $id) {
        ...
    });

.. note::

    Note for the Application and Request objects, Silex does the injection
    based on the type hinting and not on the variable name::

        $app->get('/blog/{id}', function (Application $foo, Request $bar, $id) {
            ...
        });

Route variables converters
~~~~~~~~~~~~~~~~~~~~~~~~~~

Before injecting the route variables into the controller, you can apply some
converters::

    $app->get('/user/{id}', function ($id) {
        // ...
    })->convert('id', function ($id) { return (int) $id; });

This is useful when you want to convert route variables to objects as it
allows to reuse the conversion code across different controllers::

    $userProvider = function ($id) {
        return new User($id);
    };

    $app->get('/user/{user}', function (User $user) {
        // ...
    })->convert('user', $userProvider);

    $app->get('/user/{user}/edit', function (User $user) {
        // ...
    })->convert('user', $userProvider);

The converter callback also receives the ``Request`` as its second argument::

    $callback = function ($post, Request $request) {
        return new Post($request->attributes->get('slug'));
    };

    $app->get('/blog/{id}/{slug}', function (Post $post) {
        // ...
    })->convert('post', $callback);

Requirements
~~~~~~~~~~~~

In some cases you may want to only match certain expressions. You can define
requirements using regular expressions by calling ``assert`` on the
``Controller`` object, which is returned by the routing methods.

The following will make sure the ``id`` argument is numeric, since ``\d+``
matches any amount of digits::

    $app->get('/blog/{id}', function ($id) {
        ...
    })
    ->assert('id', '\d+');

You can also chain these calls::

    $app->get('/blog/{postId}/{commentId}', function ($postId, $commentId) {
        ...
    })
    ->assert('postId', '\d+')
    ->assert('commentId', '\d+');

Default values
~~~~~~~~~~~~~~

You can define a default value for any route variable by calling ``value`` on
the ``Controller`` object::

    $app->get('/{pageName}', function ($pageName) {
        ...
    })
    ->value('pageName', 'index');

This will allow matching ``/``, in which case the ``pageName`` variable will
have the value ``index``.

Named routes
~~~~~~~~~~~~

Some providers (such as ``UrlGeneratorProvider``) can make use of named
routes. By default Silex will generate a route name for you, that cannot
really be used. You can give a route a name by calling ``bind`` on the
``Controller`` object that is returned by the routing methods::

    $app->get('/', function () {
        ...
    })
    ->bind('homepage');

    $app->get('/blog/{id}', function ($id) {
        ...
    })
    ->bind('blog_post');


.. note::

    It only makes sense to name routes if you use providers that make use of
    the ``RouteCollection``.

Controllers in classes
~~~~~~~~~~~~~~~~~~~~~~

If you don't want to use anonymous functions, you can also define your
controllers as methods. By using the ``ControllerClass::methodName`` syntax,
you can tell Silex to lazily create the controller object for you::

    $app->get('/', 'Igorw\Foo::bar');

    use Silex\Application;
    use Symfony\Component\HttpFoundation\Request;

    namespace Igorw
    {
        class Foo
        {
            public function bar(Request $request, Application $app)
            {
                ...
            }
        }
    }

This will load the ``Igorw\Foo`` class on demand, create an instance and call
the ``bar`` method to get the response. You can use ``Request`` and
``Silex\Application`` type hints to get ``$request`` and ``$app`` injected.

For an even stronger separation between Silex and your controllers, you can
:doc:`define your controllers as services <providers/service_controller>`.

Global Configuration
--------------------

If a controller setting must be applied to all controllers (a converter, a
middleware, a requirement, or a default value), you can configure it on
``$app['controllers']``, which holds all application controllers::

    $app['controllers']
        ->value('id', '1')
        ->assert('id', '\d+')
        ->requireHttps()
        ->method('get')
        ->convert('id', function () { /* ... */ })
        ->before(function () { /* ... */ })
    ;

These settings are applied to already registered controllers and they become
the defaults for new controllers.

.. note::

    The global configuration does not apply to controller providers you might
    mount as they have their own global configuration (read the
    :doc:`dedicated chapter<organizing_controllers>` for more information).

Error handlers
--------------

If some part of your code throws an exception you will want to display some
kind of error page to the user. This is what error handlers do. You can also
use them to do additional things, such as logging.

To register an error handler, pass a closure to the ``error`` method which
takes an ``Exception`` argument and returns a response::

    use Symfony\Component\HttpFoundation\Response;

    $app->error(function (\Exception $e, $code) {
        return new Response('We are sorry, but something went terribly wrong.');
    });

You can also check for specific errors by using the ``$code`` argument, and
handle them differently::

    use Symfony\Component\HttpFoundation\Response;

    $app->error(function (\Exception $e, $code) {
        switch ($code) {
            case 404:
                $message = 'The requested page could not be found.';
                break;
            default:
                $message = 'We are sorry, but something went terribly wrong.';
        }

        return new Response($message);
    });

.. note::

    As Silex ensures that the Response status code is set to the most
    appropriate one depending on the exception, setting the status on the
    response won't work. If you want to overwrite the status code (which you
    should not without a good reason), set the ``X-Status-Code`` header::

        return new Response('Error', 404 /* ignored */, array('X-Status-Code' => 200));

You can restrict an error handler to only handle some Exception classes by
setting a more specific type hint for the Closure argument::

    $app->error(function (\LogicException $e, $code) {
        // this handler will only handle \LogicException exceptions
        // and exceptions that extends \LogicException
    });

If you want to set up logging you can use a separate error handler for that.
Just make sure you register it before the response error handlers, because
once a response is returned, the following handlers are ignored.

.. note::

    Silex ships with a provider for `Monolog
    <https://github.com/Seldaek/monolog>`_ which handles logging of errors.
    Check out the *Providers* chapter for details.

.. tip::

    Silex comes with a default error handler that displays a detailed error
    message with the stack trace when **debug** is true, and a simple error
    message otherwise. Error handlers registered via the ``error()`` method
    always take precedence but you can keep the nice error messages when debug
    is turned on like this::

        use Symfony\Component\HttpFoundation\Response;

        $app->error(function (\Exception $e, $code) use ($app) {
            if ($app['debug']) {
                return;
            }

            // logic to handle the error and return a Response
        });

The error handlers are also called when you use ``abort`` to abort a request
early::

    $app->get('/blog/{id}', function (Silex\Application $app, $id) use ($blogPosts) {
        if (!isset($blogPosts[$id])) {
            $app->abort(404, "Post $id does not exist.");
        }

        return new Response(...);
    });

Redirects
---------

You can redirect to another page by returning a redirect response, which you
can create by calling the ``redirect`` method::

    $app->get('/', function () use ($app) {
        return $app->redirect('/hello');
    });

This will redirect from ``/`` to ``/hello``.

Forwards
--------

When you want to delegate the rendering to another controller, without a
round-trip to the browser (as for a redirect), use an internal sub-request::

    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpKernel\HttpKernelInterface;

    $app->get('/', function () use ($app) {
        // redirect to /hello
        $subRequest = Request::create('/hello', 'GET');

        return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    });

.. tip::

    If you are using ``UrlGeneratorProvider``, you can also generate the URI::

        $request = Request::create($app['url_generator']->generate('hello'), 'GET');

There's some more things that you need to keep in mind though. In most cases you
will want to forward some parts of the current master request to the sub-request.
That includes: Cookies, server information, session.
Read more on :doc:`how to make sub-requests <cookbook/sub_requests>`.

JSON
----

If you want to return JSON data, you can use the ``json`` helper method.
Simply pass it your data, status code and headers, and it will create a JSON
response for you::

    $app->get('/users/{id}', function ($id) use ($app) {
        $user = getUser($id);

        if (!$user) {
            $error = array('message' => 'The user was not found.');
            return $app->json($error, 404);
        }

        return $app->json($user);
    });

Streaming
---------

It's possible to create a streaming response, which is important in cases when
you cannot buffer the data being sent::

    $app->get('/images/{file}', function ($file) use ($app) {
        if (!file_exists(__DIR__.'/images/'.$file)) {
            return $app->abort(404, 'The image was not found.');
        }

        $stream = function () use ($file) {
            readfile($file);
        };

        return $app->stream($stream, 200, array('Content-Type' => 'image/png'));
    });

If you need to send chunks, make sure you call ``ob_flush`` and ``flush``
after every chunk::

    $stream = function () {
        $fh = fopen('http://www.example.com/', 'rb');
        while (!feof($fh)) {
          echo fread($fh, 1024);
          ob_flush();
          flush();
        }
        fclose($fh);
    };

Sending a file
--------------

If you want to return a file, you can use the ``sendFile`` helper method.
It eases returning files that would otherwise not be publicly available. Simply
pass it your file path, status code, headers and the content disposition and it
will create a ``BinaryFileResponse`` based response for you::

    $app->get('/files/{path}', function ($path) use ($app) {
        if (!file_exists('/base/path/' . $path)) {
            $app->abort(404);
        }

        return $app->sendFile('/base/path/' . $path);
    });

To further customize the response before returning it, check the API doc for
`Symfony\Component\HttpFoundation\BinaryFileResponse
<http://api.symfony.com/master/Symfony/Component/HttpFoundation/BinaryFileResponse.html>`_::

    return $app
        ->sendFile('/base/path/' . $path)
        ->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'pic.jpg')
    ;

.. note::

    HttpFoundation 2.2 or greater is required for this feature to be available.

Traits
------

Silex comes with PHP traits that define shortcut methods.

.. caution::

    You need to use PHP 5.4 or later to benefit from this feature.

Almost all built-in service providers have some corresponding PHP traits. To
use them, define your own Application class and include the traits you want::

    use Silex\Application;

    class MyApplication extends Application
    {
        use Application\TwigTrait;
        use Application\SecurityTrait;
        use Application\FormTrait;
        use Application\UrlGeneratorTrait;
        use Application\SwiftmailerTrait;
        use Application\MonologTrait;
        use Application\TranslationTrait;
    }

You can also define your own Route class and use some traits::

    use Silex\Route;

    class MyRoute extends Route
    {
        use Route\SecurityTrait;
    }

To use your newly defined route, override the ``$app['route_class']``
setting::

    $app['route_class'] = 'MyRoute';

Read each provider chapter to learn more about the added methods.

Security
--------

Make sure to protect your application against attacks.

Escaping
~~~~~~~~

When outputting any user input (either route variables GET/POST variables
obtained from the request), you will have to make sure to escape it correctly,
to prevent Cross-Site-Scripting attacks.

* **Escaping HTML**: PHP provides the ``htmlspecialchars`` function for this.
  Silex provides a shortcut ``escape`` method::

      $app->get('/name', function (Silex\Application $app) {
          $name = $app['request']->get('name');
          return "You provided the name {$app->escape($name)}.";
      });

  If you use the Twig template engine you should use its escaping or even
  auto-escaping mechanisms.

* **Escaping JSON**: If you want to provide data in JSON format you should
  use the Silex ``json`` function::

      $app->get('/name.json', function (Silex\Application $app) {
          $name = $app['request']->get('name');
          return $app->json(array('name' => $name));
      });

.. _download: http://silex.sensiolabs.org/download
