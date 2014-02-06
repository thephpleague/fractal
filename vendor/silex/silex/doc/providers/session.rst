SessionServiceProvider
======================

The *SessionServiceProvider* provides a service for storing data persistently
between requests.

Parameters
----------

* **session.storage.save_path** (optional): The path for the
  ``NativeFileSessionHandler``, defaults to the value of
  ``sys_get_temp_dir()``.

* **session.storage.options**: An array of options that is passed to the
  constructor of the ``session.storage`` service.

  In case of the default `NativeSessionStorage
  <http://api.symfony.com/master/Symfony/Component/HttpFoundation/Session/Storage/NativeSessionStorage.html>`_,
  the most useful options are:

  * **name**: The cookie name (_SESS by default)
  * **id**: The session id (null by default)
  * **cookie_lifetime**: Cookie lifetime
  * **cookie_path**: Cookie path
  * **cookie_domain**: Cookie domain
  * **cookie_secure**: Cookie secure (HTTPS)
  * **cookie_httponly**: Whether the cookie is http only

  However, all of these are optional. Sessions last as long as the browser is
  open. To override this, set the ``lifetime`` option.

  For a full list of available options, read the `PHP
  <http://php.net/session.configuration>`_ official documentation.

* **session.test**: Whether to simulate sessions or not (useful when writing
  functional tests).

Services
--------

* **session**: An instance of Symfony2's `Session
  <http://api.symfony.com/master/Symfony/Component/HttpFoundation/Session/Session.html>`_.

* **session.storage**: A service that is used for persistence of the session
  data.

* **session.storage.handler**: A service that is used by the
  ``session.storage`` for data access. Defaults to a `NativeFileSessionHandler
  <http://api.symfony.com/master/Symfony/Component/HttpFoundation/Session/Storage/Handler/NativeFileSessionHandler.html>`_
  storage handler.

Registering
-----------

.. code-block:: php

    $app->register(new Silex\Provider\SessionServiceProvider());

Usage
-----

The Session provider provides a ``session`` service. Here is an example that
authenticates a user and creates a session for him::

    use Symfony\Component\HttpFoundation\Response;

    $app->get('/login', function () use ($app) {
        $username = $app['request']->server->get('PHP_AUTH_USER', false);
        $password = $app['request']->server->get('PHP_AUTH_PW');

        if ('igor' === $username && 'password' === $password) {
            $app['session']->set('user', array('username' => $username));
            return $app->redirect('/account');
        }

        $response = new Response();
        $response->headers->set('WWW-Authenticate', sprintf('Basic realm="%s"', 'site_login'));
        $response->setStatusCode(401, 'Please sign in.');
        return $response;
    });

    $app->get('/account', function () use ($app) {
        if (null === $user = $app['session']->get('user')) {
            return $app->redirect('/login');
        }

        return "Welcome {$user['username']}!";
    });


Custom Session Configurations
-----------------------------

If your system is using a custom session configuration (such as a redis handler
from a PHP extension) then you need to disable the NativeFileSessionHandler by
setting ``session.storage.handler`` to null. You will have to configure the
``session.save_path`` ini setting yourself in that case.

.. code-block:: php

    $app['session.storage.handler'] = null;

