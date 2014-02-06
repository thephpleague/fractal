<?php

/*
 * This file is part of the Silex framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Silex\Tests;

use Silex\Application;

use Symfony\Component\HttpFoundation\Request;

class LazyDispatcherTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function beforeMiddlewareShouldNotCreateDispatcherEarly()
    {
        $dispatcherCreated = false;

        $app = new Application();
        $app['dispatcher'] = $app->share($app->extend('dispatcher', function ($dispatcher, $app) use (&$dispatcherCreated) {
            $dispatcherCreated = true;

            return $dispatcher;
        }));

        $app->before(function () {});

        $this->assertFalse($dispatcherCreated);

        $request = Request::create('/');
        $app->handle($request);

        $this->assertTrue($dispatcherCreated);
    }

    /** @test */
    public function eventHelpersShouldDirectlyAddListenersAfterBoot()
    {
        $app = new Application();

        $fired = false;
        $app->get("/", function () use ($app, &$fired) {
            $app->finish(function () use (&$fired) {
                $fired = true;
            });
        });

        $request = Request::create('/');
        $response = $app->handle($request);
        $app->terminate($request, $response);

        $this->assertTrue($fired, 'Event was not fired');
    }
}
