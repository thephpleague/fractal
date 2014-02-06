<?php

/*
 * This file is part of the Silex framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Silex\Tests\Application;

use Silex\Application;
use Silex\Provider\SecurityServiceProvider;
use Symfony\Component\Security\Core\User\User;
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
    public function testUser()
    {
        $request = Request::create('/');

        $app = $this->createApplication();
        $app->get('/', function () { return 'foo'; });
        $app->handle($request);
        $this->assertNull($app->user());

        $request->headers->set('PHP_AUTH_USER', 'fabien');
        $request->headers->set('PHP_AUTH_PW', 'foo');
        $app->handle($request);
        $this->assertInstanceOf('Symfony\Component\Security\Core\User\UserInterface', $app->user());
        $this->assertEquals('fabien', $app->user()->getUsername());
    }

    public function testEncodePassword()
    {
        $app = $this->createApplication();

        $user = new User('foo', 'bar');
        $this->assertEquals('5FZ2Z8QIkA7UTZ4BYkoC+GsReLf569mSKDsfods6LYQ8t+a8EW9oaircfMpmaLbPBh4FOBiiFyLfuZmTSUwzZg==', $app->encodePassword($user, 'foo'));
    }

    public function createApplication()
    {
        $app = new SecurityApplication();
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

        return $app;
    }
}
