SecurityServiceProvider
=======================

The *SecurityServiceProvider* manages authentication and authorization for
your applications.

Parameters
----------

* **security.hide_user_not_found** (optional): Defines whether to hide user not
  found exception or not. Defaults to ``true``.

Services
--------

* **security**: The main entry point for the security provider. Use it to get
  the current user token.

* **security.authentication_manager**: An instance of
  `AuthenticationProviderManager
  <http://api.symfony.com/master/Symfony/Component/Security/Core/Authentication/AuthenticationProviderManager.html>`_,
  responsible for authentication.

* **security.access_manager**: An instance of `AccessDecisionManager
  <http://api.symfony.com/master/Symfony/Component/Security/Core/Authorization/AccessDecisionManager.html>`_,
  responsible for authorization.

* **security.session_strategy**: Define the session strategy used for
  authentication (default to a migration strategy).

* **security.user_checker**: Checks user flags after authentication.

* **security.last_error**: Returns the last authentication errors when given a
  Request object.

* **security.encoder_factory**: Defines the encoding strategies for user
  passwords (default to use a digest algorithm for all users).

* **security.encoder.digest**: The encoder to use by default for all users.

.. note::

    The service provider defines many other services that are used internally
    but rarely need to be customized.

Registering
-----------

.. code-block:: php

    $app->register(new Silex\Provider\SecurityServiceProvider(), array(
        'security.firewalls' => // see below
    ));

.. note::

    The Symfony Security Component comes with the "fat" Silex archive but not
    with the regular one. If you are using Composer, add it as a dependency to
    your ``composer.json`` file:

    .. code-block:: json

        "require": {
            "symfony/security": "~2.3"
        }

.. caution::

    The security features are only available after the Application has been
    booted. So, if you want to use it outside of the handling of a request,
    don't forget to call ``boot()`` first::

        $application->boot();

.. caution::

    If you're using a form to authenticate users, you need to enable
    ``SessionServiceProvider``.

Usage
-----

The Symfony Security component is powerful. To learn more about it, read the
`Symfony2 Security documentation
<http://symfony.com/doc/2.3/book/security.html>`_.

.. tip::

    When a security configuration does not behave as expected, enable logging
    (with the Monolog extension for instance) as the Security Component logs a
    lot of interesting information about what it does and why.

Below is a list of recipes that cover some common use cases.

Accessing the current User
~~~~~~~~~~~~~~~~~~~~~~~~~~

The current user information is stored in a token that is accessible via the
``security`` service::

    $token = $app['security']->getToken();

If there is no information about the user, the token is ``null``. If the user
is known, you can get it with a call to ``getUser()``::

    if (null !== $token) {
        $user = $token->getUser();
    }

The user can be a string, an object with a ``__toString()`` method, or an
instance of `UserInterface
<http://api.symfony.com/master/Symfony/Component/Security/Core/User/UserInterface.html>`_.

Securing a Path with HTTP Authentication
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The following configuration uses HTTP basic authentication to secure URLs
under ``/admin/``::

    $app['security.firewalls'] = array(
        'admin' => array(
            'pattern' => '^/admin',
            'http' => true,
            'users' => array(
                // raw password is foo
                'admin' => array('ROLE_ADMIN', '5FZ2Z8QIkA7UTZ4BYkoC+GsReLf569mSKDsfods6LYQ8t+a8EW9oaircfMpmaLbPBh4FOBiiFyLfuZmTSUwzZg=='),
            ),
        ),
    );

The ``pattern`` is a regular expression (it can also be a `RequestMatcher
<http://api.symfony.com/master/Symfony/Component/HttpFoundation/RequestMatcher.html>`_
instance); the ``http`` setting tells the security layer to use HTTP basic
authentication and the ``users`` entry defines valid users.

Each user is defined with the following information:

* The role or an array of roles for the user (roles are strings beginning with
  ``ROLE_`` and ending with anything you want);

* The user encoded password.

.. caution::

    All users must at least have one role associated with them.

The default configuration of the extension enforces encoded passwords. To
generate a valid encoded password from a raw password, use the
``security.encoder_factory`` service::

    // find the encoder for a UserInterface instance
    $encoder = $app['security.encoder_factory']->getEncoder($user);

    // compute the encoded password for foo
    $password = $encoder->encodePassword('foo', $user->getSalt());

When the user is authenticated, the user stored in the token is an instance of
`User
<http://api.symfony.com/master/Symfony/Component/Security/Core/User/User.html>`_

Securing a Path with a Form
~~~~~~~~~~~~~~~~~~~~~~~~~~~

Using a form to authenticate users is very similar to the above configuration.
Instead of using the ``http`` setting, use the ``form`` one and define these
two parameters:

* **login_path**: The login path where the user is redirected when he is
  accessing a secured area without being authenticated so that he can enter
  his credentials;

* **check_path**: The check URL used by Symfony to validate the credentials of
  the user.

Here is how to secure all URLs under ``/admin/`` with a form::

    $app['security.firewalls'] = array(
        'admin' => array(
            'pattern' => '^/admin/',
            'form' => array('login_path' => '/login', 'check_path' => '/admin/login_check'),
            'users' => array(
                'admin' => array('ROLE_ADMIN', '5FZ2Z8QIkA7UTZ4BYkoC+GsReLf569mSKDsfods6LYQ8t+a8EW9oaircfMpmaLbPBh4FOBiiFyLfuZmTSUwzZg=='),
            ),
        ),
    );

Always keep in mind the following two golden rules:

* The ``login_path`` path must always be defined **outside** the secured area
  (or if it is in the secured area, the ``anonymous`` authentication mechanism
  must be enabled -- see below);

* The ``check_path`` path must always be defined **inside** the secured area.

For the login form to work, create a controller like the following::

    use Symfony\Component\HttpFoundation\Request;

    $app->get('/login', function(Request $request) use ($app) {
        return $app['twig']->render('login.html', array(
            'error'         => $app['security.last_error']($request),
            'last_username' => $app['session']->get('_security.last_username'),
        ));
    });

The ``error`` and ``last_username`` variables contain the last authentication
error and the last username entered by the user in case of an authentication
error.

Create the associated template:

.. code-block:: jinja

    <form action="{{ path('admin_login_check') }}" method="post">
        {{ error }}
        <input type="text" name="_username" value="{{ last_username }}" />
        <input type="password" name="_password" value="" />
        <input type="submit" />
    </form>

.. note::

    The ``admin_login_check`` route is automatically defined by Silex and its
    name is derived from the ``check_path`` value (all ``/`` are replaced with
    ``_`` and the leading ``/`` is stripped).

Defining more than one Firewall
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You are not limited to define one firewall per project.

Configuring several firewalls is useful when you want to secure different
parts of your website with different authentication strategies or for
different users (like using an HTTP basic authentication for the website API
and a form to secure your website administration area).

It's also useful when you want to secure all URLs except the login form::

    $app['security.firewalls'] = array(
        'login' => array(
            'pattern' => '^/login$',
        ),
        'secured' => array(
            'pattern' => '^.*$',
            'form' => array('login_path' => '/login', 'check_path' => '/login_check'),
            'users' => array(
                'admin' => array('ROLE_ADMIN', '5FZ2Z8QIkA7UTZ4BYkoC+GsReLf569mSKDsfods6LYQ8t+a8EW9oaircfMpmaLbPBh4FOBiiFyLfuZmTSUwzZg=='),
            ),
        ),
    );

The order of the firewall configurations is significant as the first one to
match wins. The above configuration first ensures that the ``/login`` URL is
not secured (no authentication settings), and then it secures all other URLs.

.. tip::

    You can toggle all registered authentication mechanisms for a particular
    area on and off with the ``security`` flag::

        $app['security.firewalls'] = array(
            'api' => array(
                'pattern' => '^/api',
                'security' => $app['debug'] ? false : true,
                'wsse' => true,

                // ...
            ),
        );

Adding a Logout
~~~~~~~~~~~~~~~

When using a form for authentication, you can let users log out if you add the
``logout`` setting, where ``logout_path`` must match the main firewall
pattern::

    $app['security.firewalls'] = array(
        'secured' => array(
            'pattern' => '^/admin/',
            'form' => array('login_path' => '/login', 'check_path' => '/admin/login_check'),
            'logout' => array('logout_path' => '/admin/logout'),

            // ...
        ),
    );

A route is automatically generated, based on the configured path (all ``/``
are replaced with ``_`` and the leading ``/`` is stripped):

.. code-block:: jinja

    <a href="{{ path('admin_logout') }}">Logout</a>

Allowing Anonymous Users
~~~~~~~~~~~~~~~~~~~~~~~~

When securing only some parts of your website, the user information are not
available in non-secured areas. To make the user accessible in such areas,
enabled the ``anonymous`` authentication mechanism::

    $app['security.firewalls'] = array(
        'unsecured' => array(
            'anonymous' => true,

            // ...
        ),
    );

When enabling the anonymous setting, a user will always be accessible from the
security context; if the user is not authenticated, it returns the ``anon.``
string.

Checking User Roles
~~~~~~~~~~~~~~~~~~~

To check if a user is granted some role, use the ``isGranted()`` method on the
security context::

    if ($app['security']->isGranted('ROLE_ADMIN')) {
        // ...
    }

You can check roles in Twig templates too:

.. code-block:: jinja

    {% if is_granted('ROLE_ADMIN') %}
        <a href="/secured?_switch_user=fabien">Switch to Fabien</a>
    {% endif %}

You can check if a user is "fully authenticated" (not an anonymous user for
instance) with the special ``IS_AUTHENTICATED_FULLY`` role:

.. code-block:: jinja

    {% if is_granted('IS_AUTHENTICATED_FULLY') %}
        <a href="{{ path('logout') }}">Logout</a>
    {% else %}
        <a href="{{ path('login') }}">Login</a>
    {% endif %}

Of course you will need to define a ``login`` route for this to work.

.. tip::

    Don't use the ``getRoles()`` method to check user roles.

.. caution::

    ``isGranted()`` throws an exception when no authentication information is
    available (which is the case on non-secured area).

Impersonating a User
~~~~~~~~~~~~~~~~~~~~

If you want to be able to switch to another user (without knowing the user
credentials), enable the ``switch_user`` authentication strategy::

    $app['security.firewalls'] = array(
        'unsecured' => array(
            'switch_user' => array('parameter' => '_switch_user', 'role' => 'ROLE_ALLOWED_TO_SWITCH'),

            // ...
        ),
    );

Switching to another user is now a matter of adding the ``_switch_user`` query
parameter to any URL when logged in as a user who has the
``ROLE_ALLOWED_TO_SWITCH`` role:

.. code-block:: jinja

    {% if is_granted('ROLE_ALLOWED_TO_SWITCH') %}
        <a href="?_switch_user=fabien">Switch to user Fabien</a>
    {% endif %}

You can check that you are impersonating a user by checking the special
``ROLE_PREVIOUS_ADMIN``. This is useful for instance to allow the user to
switch back to his primary account:

.. code-block:: jinja

    {% if is_granted('ROLE_PREVIOUS_ADMIN') %}
        You are an admin but you've switched to another user,
        <a href="?_switch_user=_exit"> exit</a> the switch.
    {% endif %}

Defining a Role Hierarchy
~~~~~~~~~~~~~~~~~~~~~~~~~

Defining a role hierarchy allows to automatically grant users some additional
roles::

    $app['security.role_hierarchy'] = array(
        'ROLE_ADMIN' => array('ROLE_USER', 'ROLE_ALLOWED_TO_SWITCH'),
    );

With this configuration, all users with the ``ROLE_ADMIN`` role also
automatically have the ``ROLE_USER`` and ``ROLE_ALLOWED_TO_SWITCH`` roles.

Defining Access Rules
~~~~~~~~~~~~~~~~~~~~~

Roles are a great way to adapt the behavior of your website depending on
groups of users, but they can also be used to further secure some areas by
defining access rules::

    $app['security.access_rules'] = array(
        array('^/admin', 'ROLE_ADMIN', 'https'),
        array('^.*$', 'ROLE_USER'),
    );

With the above configuration, users must have the ``ROLE_ADMIN`` to access the
``/admin`` section of the website, and ``ROLE_USER`` for everything else.
Furthermore, the admin section can only be accessible via HTTPS (if that's not
the case, the user will be automatically redirected).

.. note::

    The first argument can also be a `RequestMatcher
    <http://api.symfony.com/master/Symfony/Component/HttpFoundation/RequestMatcher.html>`_
    instance.

Defining a custom User Provider
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Using an array of users is simple and useful when securing an admin section of
a personal website, but you can override this default mechanism with you own.

The ``users`` setting can be defined as a service that returns an instance of
`UserProviderInterface
<http://api.symfony.com/master/Symfony/Component/Security/Core/User/UserProviderInterface.html>`_::

    'users' => $app->share(function () use ($app) {
        return new UserProvider($app['db']);
    }),

Here is a simple example of a user provider, where Doctrine DBAL is used to
store the users::

    use Symfony\Component\Security\Core\User\UserProviderInterface;
    use Symfony\Component\Security\Core\User\UserInterface;
    use Symfony\Component\Security\Core\User\User;
    use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
    use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
    use Doctrine\DBAL\Connection;

    class UserProvider implements UserProviderInterface
    {
        private $conn;

        public function __construct(Connection $conn)
        {
            $this->conn = $conn;
        }

        public function loadUserByUsername($username)
        {
            $stmt = $this->conn->executeQuery('SELECT * FROM users WHERE username = ?', array(strtolower($username)));

            if (!$user = $stmt->fetch()) {
                throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
            }

            return new User($user['username'], $user['password'], explode(',', $user['roles']), true, true, true, true);
        }

        public function refreshUser(UserInterface $user)
        {
            if (!$user instanceof User) {
                throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
            }

            return $this->loadUserByUsername($user->getUsername());
        }

        public function supportsClass($class)
        {
            return $class === 'Symfony\Component\Security\Core\User\User';
        }
    }

In this example, instances of the default ``User`` class are created for the
users, but you can define your own class; the only requirement is that the
class must implement `UserInterface
<http://api.symfony.com/master/Symfony/Component/Security/Core/User/UserInterface.html>`_

And here is the code that you can use to create the database schema and some
sample users::

    use Doctrine\DBAL\Schema\Table;

    $schema = $app['db']->getSchemaManager();
    if (!$schema->tablesExist('users')) {
        $users = new Table('users');
        $users->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => true));
        $users->setPrimaryKey(array('id'));
        $users->addColumn('username', 'string', array('length' => 32));
        $users->addUniqueIndex(array('username'));
        $users->addColumn('password', 'string', array('length' => 255));
        $users->addColumn('roles', 'string', array('length' => 255));

        $schema->createTable($users);

        $app['db']->insert('users', array(
          'username' => 'fabien',
          'password' => '5FZ2Z8QIkA7UTZ4BYkoC+GsReLf569mSKDsfods6LYQ8t+a8EW9oaircfMpmaLbPBh4FOBiiFyLfuZmTSUwzZg==',
          'roles' => 'ROLE_USER'
        ));

        $app['db']->insert('users', array(
          'username' => 'admin',
          'password' => '5FZ2Z8QIkA7UTZ4BYkoC+GsReLf569mSKDsfods6LYQ8t+a8EW9oaircfMpmaLbPBh4FOBiiFyLfuZmTSUwzZg==',
          'roles' => 'ROLE_ADMIN'
        ));
    }

.. tip::

    If you are using the Doctrine ORM, the Symfony bridge for Doctrine
    provides a user provider class that is able to load users from your
    entities.

Defining a custom Encoder
~~~~~~~~~~~~~~~~~~~~~~~~~

By default, Silex uses the ``sha512`` algorithm to encode passwords.
Additionally, the password is encoded multiple times and converted to base64.
You can change these defaults by overriding the ``security.encoder.digest``
service::

    use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

    $app['security.encoder.digest'] = $app->share(function ($app) {
        // use the sha1 algorithm
        // don't base64 encode the password
        // use only 1 iteration
        return new MessageDigestPasswordEncoder('sha1', false, 1);
    });

Defining a custom Authentication Provider
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The Symfony Security component provides a lot of ready-to-use authentication
providers (form, HTTP, X509, remember me, ...), but you can add new ones
easily. To register a new authentication provider, create a service named
``security.authentication_listener.factory.XXX`` where ``XXX`` is the name you want to
use in your configuration::

    $app['security.authentication_listener.factory.wsse'] = $app->protect(function ($name, $options) use ($app) {
        // define the authentication provider object
        $app['security.authentication_provider.'.$name.'.wsse'] = $app->share(function () use ($app) {
            return new WsseProvider($app['security.user_provider.default'], __DIR__.'/security_cache');
        });

        // define the authentication listener object
        $app['security.authentication_listener.'.$name.'.wsse'] = $app->share(function () use ($app) {
            return new WsseListener($app['security'], $app['security.authentication_manager']);
        });

        return array(
            // the authentication provider id
            'security.authentication_provider.'.$name.'.wsse',
            // the authentication listener id
            'security.authentication_listener.'.$name.'.wsse',
            // the entry point id
            null,
            // the position of the listener in the stack
            'pre_auth'
        );
    });

You can now use it in your configuration like any other built-in
authentication provider::

    $app->register(new Silex\Provider\SecurityServiceProvider(), array(
        'security.firewalls' => array(
            'default' => array(
                'wsse' => true,

                // ...
            ),
        ),
    ));

Instead of ``true``, you can also define an array of options that customize
the behavior of your authentication factory; it will be passed as the second
argument of your authentication factory (see above).

This example uses the authentication provider classes as described in the
Symfony `cookbook`_.

Stateless Authentication
~~~~~~~~~~~~~~~~~~~~~~~~

By default, a session cookie is created to persist the security context of
the user. However, if you use certificates, HTTP authentication, WSSE and so
on, the credentials are sent for each request. In that case, you can turn off
persistence by activating the ``stateless`` authentication flag::

    $app['security.firewalls'] = array(
        'default' => array(
            'stateless' => true,
            'wsse' => true,

            // ...
        ),
    );

Traits
------

``Silex\Application\SecurityTrait`` adds the following shortcuts:

* **user**: Returns the current user.

* **encodePassword**: Encode a given password.

.. code-block:: php

    $user = $app->user();

    $encoded = $app->encodePassword($user, 'foo');

``Silex\Route\SecurityTrait`` adds the following methods to the controllers:

* **secure**: Secures a controller for the given roles.

.. code-block:: php

    $app->get('/', function () {
        // do something but only for admins
    })->secure('ROLE_ADMIN');

.. _cookbook: http://symfony.com/doc/current/cookbook/security/custom_authentication_provider.html
