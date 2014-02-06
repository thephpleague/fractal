Using multiple monolog loggers
==============================

Having separate instances of `Monolog` for different parts of your system is
often desirable and allows you to configure them independently, allowing for fine
grained control of where your logging goes and in what detail.

This simple example allows you to quickly configure several monolog instances,
using the bundled handler, but each with a different channel. 

.. code-block:: php

    $app['monolog.factory'] = $app->protect(function ($name) use ($app) {
        $log = new $app['monolog.logger.class']($name);
        $log->pushHandler($app['monolog.handler']);

        return $log;
    });

    foreach (array('auth', 'payments', 'stats') as $channel) {
        $app['monolog.'.$channel] = $app->share(function ($app) use ($channel) {
            return $app['monolog.factory']($channel);
        });
    }

As your application grows, or your logging needs for certain areas of the
system become apparent, it should be straightforward to then configure that
particular service separately, including your customizations.

.. code-block:: php

    use Monolog\Handler\StreamHandler;

    $app['monolog.payments'] = $app->share(function ($app) {
        $log = new $app['monolog.logger.class']('payments');
        $handler = new StreamHandler($app['monolog.payments.logfile'], $app['monolog.payment.level']);
        $log->pushHandler($handler);

        return $log;
    });

Alternatively, you could attempt to make the factory more complicated, and rely
on some conventions, such as checking for an array of handlers registered with
the container with the channel name, defaulting to the bundled handler.

.. code-block:: php
    
    use Monolog\Handler\StreamHandler;
    use Monolog\Logger;

    $app['monolog.factory'] = $app->protect(function ($name) use ($app) {
        $log = new $app['monolog.logger.class']($name);

        $handlers = isset($app['monolog.'.$name.'.handlers'])
            ? $app['monolog.'.$name.'.handlers']
            : array($app['monolog.handler']);

        foreach ($handlers as $handler) {
            $log->pushHandler($handler);
        }

        return $log;
    });

    $app['monolog.payments.handlers'] = $app->share(function ($app) {
        return array(
            new StreamHandler(__DIR__.'/../payments.log', Logger::DEBUG),
        );
    });


