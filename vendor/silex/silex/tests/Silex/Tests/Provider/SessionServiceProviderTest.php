<?php

/*
 * This file is part of the Silex framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Silex\Tests\Provider;

use Silex\Application;
use Silex\WebTestCase;
use Silex\Provider\SessionServiceProvider;
use Symfony\Component\HttpKernel\Client;

/**
 * SessionProvider test cases.
 *
 * @author Igor Wiedler <igor@wiedler.ch>
 * @author Fabien Potencier <fabien@symfony.com>
 */
class SessionServiceProviderTest extends WebTestCase
{
    public function testRegister()
    {
        /**
         * Smoke test
         */
        $defaultStorage = $this->app['session.storage.native'];

        $client = $this->createClient();

        $client->request('get', '/login');
        $this->assertEquals('Logged in successfully.', $client->getResponse()->getContent());

        $client->request('get', '/account');
        $this->assertEquals('This is your account.', $client->getResponse()->getContent());

        $client->request('get', '/logout');
        $this->assertEquals('Logged out successfully.', $client->getResponse()->getContent());

        $client->request('get', '/account');
        $this->assertEquals('You are not logged in.', $client->getResponse()->getContent());
    }

    public function createApplication()
    {
        $app = new Application();

        $app->register(new SessionServiceProvider(), array(
            'session.test' => true,
        ));

        $app->get('/login', function () use ($app) {
            $app['session']->set('logged_in', true);

            return 'Logged in successfully.';
        });

        $app->get('/account', function () use ($app) {
            if (!$app['session']->get('logged_in')) {
                return 'You are not logged in.';
            }

            return 'This is your account.';
        });

        $app->get('/logout', function () use ($app) {
            $app['session']->invalidate();

            return 'Logged out successfully.';
        });

        return $app;
    }

    public function testWithRoutesThatDoesNotUseSession()
    {
        $app = new Application();

        $app->register(new SessionServiceProvider(), array(
            'session.test' => true,
        ));

        $app->get('/', function () {
            return 'A welcome page.';
        });

        $app->get('/robots.txt', function () {
            return 'Informations for robots.';
        });

        $app['debug'] = true;
        $app['exception_handler']->disable();

        $client = new Client($app);

        $client->request('get', '/');
        $this->assertEquals('A welcome page.', $client->getResponse()->getContent());

        $client->request('get', '/robots.txt');
        $this->assertEquals('Informations for robots.', $client->getResponse()->getContent());
    }
}
