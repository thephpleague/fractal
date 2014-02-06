<?php

/*
 * This file is part of the Silex framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Silex\Provider;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bridge\Monolog\Handler\DebugHandler;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Monolog Provider.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class MonologServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['logger'] = function () use ($app) {
            return $app['monolog'];
        };

        if ($bridge = class_exists('Symfony\Bridge\Monolog\Logger')) {
            $app['monolog.handler.debug'] = function () use ($app) {
                return new DebugHandler($app['monolog.level']);
            };
        }

        $app['monolog.logger.class'] = $bridge ? 'Symfony\Bridge\Monolog\Logger' : 'Monolog\Logger';

        $app['monolog'] = $app->share(function ($app) {
            $log = new $app['monolog.logger.class']($app['monolog.name']);

            $log->pushHandler($app['monolog.handler']);

            if ($app['debug'] && isset($app['monolog.handler.debug'])) {
                $log->pushHandler($app['monolog.handler.debug']);
            }

            return $log;
        });

        $app['monolog.handler'] = function () use ($app) {
            return new StreamHandler($app['monolog.logfile'], $app['monolog.level']);
        };

        $app['monolog.level'] = function () {
            return Logger::DEBUG;
        };

        $app['monolog.name'] = 'myapp';
    }

    public function boot(Application $app)
    {
        $app->before(function (Request $request) use ($app) {
            $app['monolog']->addInfo('> '.$request->getMethod().' '.$request->getRequestUri());
        });

        /*
         * Priority -4 is used to come after those from SecurityServiceProvider (0)
         * but before the error handlers added with Silex\Application::error (defaults to -8)
         */
        $app->error(function (\Exception $e) use ($app) {
            $message = sprintf('%s: %s (uncaught exception) at %s line %s', get_class($e), $e->getMessage(), $e->getFile(), $e->getLine());
            if ($e instanceof HttpExceptionInterface && $e->getStatusCode() < 500) {
                $app['monolog']->addError($message, array('exception' => $e));
            } else {
                $app['monolog']->addCritical($message, array('exception' => $e));
            }
        }, -4);

        $app->after(function (Request $request, Response $response) use ($app) {
            if ($response instanceof RedirectResponse) {
                $app['monolog']->addInfo('< '.$response->getStatusCode().' '.$response->getTargetUrl());
            } else {
                $app['monolog']->addInfo('< '.$response->getStatusCode());
            }
        });
    }
}
