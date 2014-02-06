SerializerServiceProvider
===========================

The *SerializerServiceProvider* provides a service for serializing objects.

Parameters
----------

None.

Services
--------

* **serializer**: An instance of `Symfony\Component\Serializer\Serializer
  <http://api.symfony.com/master/Symfony/Component/Serializer/Serializer.html>`_.

* **serializer.encoders**: `Symfony\Component\Serializer\Encoder\JsonEncoder
  <http://api.symfony.com/master/Symfony/Component/Serializer/Encoder/JsonEncoder.html>`_
  and `Symfony\Component\Serializer\Encoder\XmlEncoder
  <http://api.symfony.com/master/Symfony/Component/Serializer/Encoder/XmlEncoder.html>`_.

* **serializer.normalizers**: `Symfony\Component\Serializer\Normalizer\CustomNormalizer
  <http://api.symfony.com/master/Symfony/Component/Serializer/Normalizer/CustomNormalizer.html>`_
  and `Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer
  <http://api.symfony.com/master/Symfony/Component/Serializer/Normalizer/GetSetMethodNormalizer.html>`_.

Registering
-----------

.. code-block:: php

    $app->register(new Silex\Provider\SerializerServiceProvider());

Usage
-----

The ``SerializerServiceProvider`` provider provides a ``serializer`` service:

.. code-block:: php

    use Silex\Application;
    use Silex\Provider\SerializerServiceProvider;
    use Symfony\Component\HttpFoundation\Response;
    
    $app = new Application();
    
    $app->register(new SerializerServiceProvider());
    
    // only accept content types supported by the serializer via the assert method.
    $app->get("/pages/{id}.{_format}", function ($id) use ($app) {
        // assume a page_repository service exists that returns Page objects. The
        // object returned has getters and setters exposing the state.
        $page = $app['page_repository']->find($id);
        $format = $app['request']->getRequestFormat();
    
        if (!$page instanceof Page) {
            $app->abort("No page found for id: $id");
        }
    
        return new Response($app['serializer']->serialize($page, $format), 200, array(
            "Content-Type" => $app['request']->getMimeType($format)
        ));
    })->assert("_format", "xml|json")
      ->assert("id", "\d+");

