<?php

/*
 * This file is part of the Silex framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Silex\Tests\Route;

use Silex\Application;
use Silex\Provider\SecurityServiceProvider;
use Symfony\Component\HttpFoundation\Request;

/**
 * SecurityTrait test cases.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @requires PHP 5.4
 */
class SecurityTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testSecure()
    {
        $app = new Application();
        $app['route_class'] = 'Silex\Tests\Route\SecurityRoute';
        $app->register(new SecurityServiceProvider(), array(
            'security.firewalls' => array(
                'default' => array(
                    'http' => true,
                    'users' => array(
                        'fabien' => array('ROLE_ADMIN', '5FZ2Z8QIkA7UTZ4BYkoC+GsReLf569mSKDsfods6LYQ8t+a8EW9oaircfMpmaLbPBh4FOBiiFyLfuZmTSUwzZg=='),
                    ),
                ),
            ),
        ));

        $app->get('/', function () { return 'foo'; })
            ->secure('ROLE_ADMIN')
        ;

        $request = Request::create('/');
        $response = $app->handle($request);
        $this->assertEquals(401, $response->getStatusCode());

        $request = Request::create('/');
        $request->headers->set('PHP_AUTH_USER', 'fabien');
        $request->headers->set('PHP_AUTH_PW', 'foo');
        $response = $app->handle($request);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
