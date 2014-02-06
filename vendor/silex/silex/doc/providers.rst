Providers
=========

Providers allow the developer to reuse parts of an application into another
one. Silex provides two types of providers defined by two interfaces:
``ServiceProviderInterface`` for services and ``ControllerProviderInterface``
for controllers.

Service Providers
-----------------

Loading providers
~~~~~~~~~~~~~~~~~

In order to load and use a service provider, you must register it on the
application::

    $app = new Silex\Application();

    $app->register(new Acme\DatabaseServiceProvider());

You can also provide some parameters as a second argument. These will be set
**after** the provider is registered, but **before** it is booted::

    $app->register(new Acme\DatabaseServiceProvider(), array(
        'database.dsn'      => 'mysql:host=localhost;dbname=myapp',
        'database.user'     => 'root',
        'database.password' => 'secret_root_password',
    ));

Conventions
~~~~~~~~~~~

You need to watch out in what order you do certain things when interacting
with providers. Just keep to these rules:

* Overriding existing services must occur **after** the provider is
  registered.

  *Reason: If the service already exists, the provider will overwrite it.*

* You can set parameters any time **after** the provider is registered, but
  **before** the service is accessed.

  *Reason: Providers can set default values for parameters. Just like with
  services, the provider will overwrite existing values.*

Make sure to stick to this behavior when creating your own providers.

Included providers
~~~~~~~~~~~~~~~~~~

There are a few providers that you get out of the box. All of these are within
the ``Silex\Provider`` namespace:

* :doc:`DoctrineServiceProvider <providers/doctrine>`
* :doc:`MonologServiceProvider <providers/monolog>`
* :doc:`SessionServiceProvider <providers/session>`
* :doc:`SerializerServiceProvider <providers/serializer>`
* :doc:`SwiftmailerServiceProvider <providers/swiftmailer>`
* :doc:`TwigServiceProvider <providers/twig>`
* :doc:`TranslationServiceProvider <providers/translation>`
* :doc:`UrlGeneratorServiceProvider <providers/url_generator>`
* :doc:`ValidatorServiceProvider <providers/validator>`
* :doc:`HttpCacheServiceProvider <providers/http_cache>`
* :doc:`FormServiceProvider <providers/form>`
* :doc:`SecurityServiceProvider <providers/security>`
* :doc:`ServiceControllerServiceProvider <providers/service_controller>`

Third party providers
~~~~~~~~~~~~~~~~~~~~~

Some service providers are developed by the community. Those third-party
providers are listed on `Silex' repository wiki
<https://github.com/fabpot/Silex/wiki/Third-Party-ServiceProviders>`_.

You are encouraged to share yours.

Creating a provider
~~~~~~~~~~~~~~~~~~~

Providers must implement the ``Silex\ServiceProviderInterface``::

    interface ServiceProviderInterface
    {
        function register(Application $app);

        function boot(Application $app);
    }

This is very straight forward, just create a new class that implements the two
methods. In the ``register()`` method, you can define services on the
application which then may make use of other services and parameters. In the
``boot()`` method, you can configure the application, just before it handles a
request.

Here is an example of such a provider::

    namespace Acme;

    use Silex\Application;
    use Silex\ServiceProviderInterface;

    class HelloServiceProvider implements ServiceProviderInterface
    {
        public function register(Application $app)
        {
            $app['hello'] = $app->protect(function ($name) use ($app) {
                $default = $app['hello.default_name'] ? $app['hello.default_name'] : '';
                $name = $name ?: $default;

                return 'Hello '.$app->escape($name);
            });
        }

        public function boot(Application $app)
        {
        }
    }

This class provides a ``hello`` service which is a protected closure. It takes
a ``name`` argument and will return ``hello.default_name`` if no name is
given. If the default is also missing, it will use an empty string.

You can now use this provider as follows::

    $app = new Silex\Application();

    $app->register(new Acme\HelloServiceProvider(), array(
        'hello.default_name' => 'Igor',
    ));

    $app->get('/hello', function () use ($app) {
        $name = $app['request']->get('name');

        return $app['hello']($name);
    });

In this example we are getting the ``name`` parameter from the query string,
so the request path would have to be ``/hello?name=Fabien``.

Controllers providers
---------------------

Loading providers
~~~~~~~~~~~~~~~~~

In order to load and use a controller provider, you must "mount" its
controllers under a path::

    $app = new Silex\Application();

    $app->mount('/blog', new Acme\BlogControllerProvider());

All controllers defined by the provider will now be available under the
``/blog`` path.

Creating a provider
~~~~~~~~~~~~~~~~~~~

Providers must implement the ``Silex\ControllerProviderInterface``::

    interface ControllerProviderInterface
    {
        function connect(Application $app);
    }

Here is an example of such a provider::

    namespace Acme;

    use Silex\Application;
    use Silex\ControllerProviderInterface;

    class HelloControllerProvider implements ControllerProviderInterface
    {
        public function connect(Application $app)
        {
            // creates a new controller based on the default route
            $controllers = $app['controllers_factory'];

            $controllers->get('/', function (Application $app) {
                return $app->redirect('/hello');
            });

            return $controllers;
        }
    }

The ``connect`` method must return an instance of ``ControllerCollection``.
``ControllerCollection`` is the class where all controller related methods are
defined (like ``get``, ``post``, ``match``, ...).

.. tip::

    The ``Application`` class acts in fact as a proxy for these methods.

You can now use this provider as follows::

    $app = new Silex\Application();

    $app->mount('/blog', new Acme\HelloControllerProvider());

In this example, the ``/blog/`` path now references the controller defined in
the provider.

.. tip::

    You can also define a provider that implements both the service and the
    controller provider interface and package in the same class the services
    needed to make your controllers work.
