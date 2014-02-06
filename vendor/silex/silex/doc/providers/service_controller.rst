ServiceControllerServiceProvider
================================

As your Silex application grows, you may wish to begin organizing your
controllers in a more formal fashion. Silex can use controller classes out of
the box, but with a bit of work, your controllers can be created as services,
giving you the full power of dependency injection and lazy loading.

.. ::todo Link above to controller classes cookbook

Why would I want to do this?
----------------------------

- Dependency Injection over Service Location

  Using this method, you can inject the actual dependencies required by your
  controller and gain total inversion of control, while still maintaining the
  lazy loading of your controllers and it's dependencies. Because your
  dependencies are clearly defined, they are easily mocked, allowing you to test
  your controllers in isolation.

- Framework Independence

  Using this method, your controllers start to become more independent of the
  framework you are using. Carefully crafted, your controllers will become
  reusable with multiple frameworks. By keeping careful control of your
  dependencies, your controllers could easily become compatible with Silex,
  Symfony (full stack) and Drupal, to name just a few.

Parameters
----------

There are currently no parameters for the ``ServiceControllerServiceProvider``.

Services
--------

There are no extra services provided, the ``ServiceControllerServiceProvider``
simply extends the existing **resolver** service.

Registering
-----------

.. code-block:: php

    $app->register(new Silex\Provider\ServiceControllerServiceProvider());

Usage
-----

In this slightly contrived example of a blog API, we're going to change the
``/posts.json`` route to use a controller, that is defined as a service.

.. code-block:: php

    use Silex\Application;
    use Demo\Repository\PostRepository;

    $app = new Application();

    $app['posts.repository'] = $app->share(function() {
        return new PostRepository;
    });

    $app->get('/posts.json', function() use ($app) {
        return $app->json($app['posts.repository']->findAll());
    });

Rewriting your controller as a service is pretty simple, create a Plain Ol' PHP
Object with your ``PostRepository`` as a dependency, along with an
``indexJsonAction`` method to handle the request. Although not shown in the
example below, you can use type hinting and parameter naming to get the
parameters you need, just like with standard Silex routes.

If you are a TDD/BDD fan (and you should be), you may notice that this
controller has well defined responsibilities and dependencies, and is easily
tested/specced. You may also notice that the only external dependency is on
``Symfony\Component\HttpFoundation\JsonResponse``, meaning this controller could
easily be used in a Symfony (full stack) application, or potentially with other
applications or frameworks that know how to handle a `Symfony/HttpFoundation
<http://symfony.com/doc/master/components/http_foundation/introduction.html>`_
``Response`` object.

.. code-block:: php

    namespace Demo\Controller;

    use Demo\Repository\PostRepository;
    use Symfony\Component\HttpFoundation\JsonResponse;

    class PostController
    {
        protected $repo;

        public function __construct(PostRepository $repo)
        {
            $this->repo = $repo;
        }

        public function indexJsonAction()
        {
            return new JsonResponse($this->repo->findAll());
        }
    }

And lastly, define your controller as a service in the application, along with
your route. The syntax in the route definition is the name of the service,
followed by a single colon (:), followed by the method name.

.. code-block:: php

    $app['posts.controller'] = $app->share(function() use ($app) {
        return new PostController($app['posts.repository']);
    });

    $app->get('/posts.json', "posts.controller:indexJsonAction");
