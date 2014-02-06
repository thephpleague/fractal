How to use PdoSessionStorage to store sessions in the database
==============================================================

By default, the :doc:`SessionServiceProvider </providers/session>` writes
session information in files using Symfony2 NativeFileSessionStorage. Most
medium to large websites use a database to store sessions instead of files,
because databases are easier to use and scale in a multi-webserver
environment.

Symfony2's `NativeSessionStorage
<http://api.symfony.com/master/Symfony/Component/HttpFoundation/Session/Storage/NativeSessionStorage.html>`_
has multiple storage handlers and one of them uses PDO to store sessions,
`PdoSessionHandler
<http://api.symfony.com/master/Symfony/Component/HttpFoundation/Session/Storage/Handler/PdoSessionHandler.html>`_.
To use it, replace the ``session.storage.handler`` service in your application
like explained below.

With a dedicated PDO service
----------------------------

.. code-block:: php

    use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;

    $app->register(new Silex\Provider\SessionServiceProvider());

    $app['pdo.dsn'] = 'mysql:dbname=mydatabase';
    $app['pdo.user'] = 'myuser';
    $app['pdo.password'] = 'mypassword';

    $app['session.db_options'] = array(
        'db_table'      => 'session',
        'db_id_col'     => 'session_id',
        'db_data_col'   => 'session_value',
        'db_time_col'   => 'session_time',
    );

    $app['pdo'] = $app->share(function () use ($app) {
        return new PDO(
            $app['pdo.dsn'],
            $app['pdo.user'],
            $app['pdo.password']
        );
    });

    $app['session.storage.handler'] = $app->share(function () use ($app) {
        return new PdoSessionHandler(
            $app['pdo'],
            $app['session.db_options'],
            $app['session.storage.options']
        );
    });

Using the DoctrineServiceProvider
---------------------------------

When using the :doc:`DoctrineServiceProvider </providers/doctrine>` You don't
have to make another database connection, simply pass the getWrappedConnection method.

.. code-block:: php

    use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;

    $app->register(new Silex\Provider\SessionServiceProvider());

    $app['session.db_options'] = array(
        'db_table'      => 'session',
        'db_id_col'     => 'session_id',
        'db_data_col'   => 'session_value',
        'db_time_col'   => 'session_time',
    );

    $app['session.storage.handler'] = $app->share(function () use ($app) {
        return new PdoSessionHandler(
            $app['db']->getWrappedConnection(),
            $app['session.db_options'],
            $app['session.storage.options']
        );
    });

Database structure
------------------

PdoSessionStorage needs a database table with 3 columns:

* ``session_id``: ID column (VARCHAR(255) or larger)
* ``session_value``: Value column (TEXT or CLOB)
* ``session_time``: Time column (INTEGER)

You can find examples of SQL statements to create the session table in the
`Symfony2 cookbook
<http://symfony.com/doc/current/cookbook/configuration/pdo_session_storage.html#example-sql-statements>`_
