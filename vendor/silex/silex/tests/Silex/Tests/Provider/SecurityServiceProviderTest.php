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
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Symfony\Component\HttpKernel\Client;
use Symfony\Component\HttpFoundation\Request;

/**
 * SecurityServiceProvider
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class SecurityServiceProviderTest extends WebTestCase
{
    /**
     * @expectedException \LogicException
     */
    public function testWrongAuthenticationType()
    {
        $app = new Application();
        $app->register(new SecurityServiceProvider(), array(
            'security.firewalls' => array(
                'wrong' => array(
                    'foobar' => true,
                    'users' => array(),
                ),
            ),
        ));
        $app->get('/', function () {});
        $app->handle(Request::create('/'));
    }

    public function testFormAuthentication()
    {
        $app = $this->createApplication('form');

        $client = new Client($app);

        $client->request('get', '/');
        $this->assertEquals('ANONYMOUS', $client->getResponse()->getContent());

        $client->request('post', '/login_check', array('_username' => 'fabien', '_password' => 'bar'));
        $this->assertEquals('Bad credentials', $app['security.last_error']($client->getRequest()));
        // hack to re-close the session as the previous assertions re-opens it
        $client->getRequest()->getSession()->save();

        $client->request('post', '/login_check', array('_username' => 'fabien', '_password' => 'foo'));
        $this->assertEquals('', $app['security.last_error']($client->getRequest()));
        $client->getRequest()->getSession()->save();
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertEquals('http://localhost/', $client->getResponse()->getTargetUrl());

        $client->request('get', '/');
        $this->assertEquals('fabienAUTHENTICATED', $client->getResponse()->getContent());
        $client->request('get', '/admin');
        $this->assertEquals(403, $client->getResponse()->getStatusCode());

        $client->request('get', '/logout');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertEquals('http://localhost/', $client->getResponse()->getTargetUrl());

        $client->request('get', '/');
        $this->assertEquals('ANONYMOUS', $client->getResponse()->getContent());

        $client->request('get', '/admin');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertEquals('http://localhost/login', $client->getResponse()->getTargetUrl());

        $client->request('post', '/login_check', array('_username' => 'admin', '_password' => 'foo'));
        $this->assertEquals('', $app['security.last_error']($client->getRequest()));
        $client->getRequest()->getSession()->save();
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertEquals('http://localhost/admin', $client->getResponse()->getTargetUrl());

        $client->request('get', '/');
        $this->assertEquals('adminAUTHENTICATEDADMIN', $client->getResponse()->getContent());
        $client->request('get', '/admin');
        $this->assertEquals('admin', $client->getResponse()->getContent());
    }

    public function testHttpAuthentication()
    {
        $app = $this->createApplication('http');

        $client = new Client($app);

        $client->request('get', '/');
        $this->assertEquals(401, $client->getResponse()->getStatusCode());
        $this->assertEquals('Basic realm="Secured"', $client->getResponse()->headers->get('www-authenticate'));

        $client->request('get', '/', array(), array(), array('PHP_AUTH_USER' => 'dennis', 'PHP_AUTH_PW' => 'foo'));
        $this->assertEquals('dennisAUTHENTICATED', $client->getResponse()->getContent());
        $client->request('get', '/admin');
        $this->assertEquals(403, $client->getResponse()->getStatusCode());

        $client->restart();

        $client->request('get', '/');
        $this->assertEquals(401, $client->getResponse()->getStatusCode());
        $this->assertEquals('Basic realm="Secured"', $client->getResponse()->headers->get('www-authenticate'));

        $client->request('get', '/', array(), array(), array('PHP_AUTH_USER' => 'admin', 'PHP_AUTH_PW' => 'foo'));
        $this->assertEquals('adminAUTHENTICATEDADMIN', $client->getResponse()->getContent());
        $client->request('get', '/admin');
        $this->assertEquals('admin', $client->getResponse()->getContent());
    }

    public function testUserPasswordValidatorIsRegistered()
    {
        if (!is_dir(__DIR__.'/../../../../vendor/symfony/validator')) {
            $this->markTestSkipped('Validator dependency was not installed.');
        }

        $app = new Application();

        $app->register(new ValidatorServiceProvider());
        $app->register(new SecurityServiceProvider(), array(
            'security.firewalls' => array(
                'admin' => array(
                    'pattern' => '^/admin',
                    'http' => true,
                    'users' => array(
                        'admin' => array('ROLE_ADMIN', '513aeb0121909'),
                    )
                ),
            ),
        ));

        $app->boot();

        $this->assertInstanceOf('Symfony\Component\Security\Core\Validator\Constraints\UserPasswordValidator', $app['security.validator.user_password_validator']);
    }

    public function testExposedExceptions()
    {
        $app = $this->createApplication('form');
        $app['security.hide_user_not_found'] = false;

        $client = new Client($app);

        $client->request('get', '/');
        $this->assertEquals('ANONYMOUS', $client->getResponse()->getContent());

        $client->request('post', '/login_check', array('_username' => 'fabien', '_password' => 'bar'));
        $this->assertEquals('The presented password is invalid.', $app['security.last_error']($client->getRequest()));
        $client->getRequest()->getSession()->save();

        $client->request('post', '/login_check', array('_username' => 'unknown', '_password' => 'bar'));
        $this->assertEquals('Username "unknown" does not exist.', $app['security.last_error']($client->getRequest()));
        $client->getRequest()->getSession()->save();
    }

    public function createApplication($authenticationMethod = 'form')
    {
        $app = new Application();
        $app->register(new SessionServiceProvider());

        $app = call_user_func(array($this, 'add'.ucfirst($authenticationMethod).'Authentication'), $app);

        $app['session.test'] = true;

        return $app;
    }

    private function addFormAuthentication($app)
    {
        $app->register(new SecurityServiceProvider(), array(
            'security.firewalls' => array(
                'login' => array(
                    'pattern' => '^/login$',
                ),
                'default' => array(
                    'pattern' => '^.*$',
                    'anonymous' => true,
                    'form' => true,
                    'logout' => true,
                    'users' => array(
                        // password is foo
                        'fabien' => array('ROLE_USER', '5FZ2Z8QIkA7UTZ4BYkoC+GsReLf569mSKDsfods6LYQ8t+a8EW9oaircfMpmaLbPBh4FOBiiFyLfuZmTSUwzZg=='),
                        'admin'  => array('ROLE_ADMIN', '5FZ2Z8QIkA7UTZ4BYkoC+GsReLf569mSKDsfods6LYQ8t+a8EW9oaircfMpmaLbPBh4FOBiiFyLfuZmTSUwzZg=='),
                    ),
                ),
            ),
            'security.access_rules' => array(
                array('^/admin', 'ROLE_ADMIN'),
            ),
            'security.role_hierarchy' => array(
                'ROLE_ADMIN' => array('ROLE_USER'),
            ),
        ));

        $app->get('/login', function(Request $request) use ($app) {
            $app['session']->start();

            return $app['security.last_error']($request);
        });

        $app->get('/', function() use ($app) {
            $user = $app['security']->getToken()->getUser();

            $content = is_object($user) ? $user->getUsername() : 'ANONYMOUS';

            if ($app['security']->isGranted('IS_AUTHENTICATED_FULLY')) {
                $content .= 'AUTHENTICATED';
            }

            if ($app['security']->isGranted('ROLE_ADMIN')) {
                $content .= 'ADMIN';
            }

            return $content;
        });

        $app->get('/admin', function() use ($app) {
            return 'admin';
        });

        return $app;
    }

    private function addHttpAuthentication($app)
    {
        $app->register(new SecurityServiceProvider(), array(
            'security.firewalls' => array(
                'http-auth' => array(
                    'pattern' => '^.*$',
                    'http' => true,
                    'users' => array(
                        // password is foo
                        'dennis' => array('ROLE_USER', '5FZ2Z8QIkA7UTZ4BYkoC+GsReLf569mSKDsfods6LYQ8t+a8EW9oaircfMpmaLbPBh4FOBiiFyLfuZmTSUwzZg=='),
                        'admin'  => array('ROLE_ADMIN', '5FZ2Z8QIkA7UTZ4BYkoC+GsReLf569mSKDsfods6LYQ8t+a8EW9oaircfMpmaLbPBh4FOBiiFyLfuZmTSUwzZg=='),
                    ),
                ),
            ),
            'security.access_rules' => array(
                array('^/admin', 'ROLE_ADMIN'),
            ),
            'security.role_hierarchy' => array(
                'ROLE_ADMIN' => array('ROLE_USER'),
            ),
        ));

        $app->get('/', function() use ($app) {
            $user = $app['security']->getToken()->getUser();

            $content = is_object($user) ? $user->getUsername() : 'ANONYMOUS';

            if ($app['security']->isGranted('IS_AUTHENTICATED_FULLY')) {
                $content .= 'AUTHENTICATED';
            }

            if ($app['security']->isGranted('ROLE_ADMIN')) {
                $content .= 'ADMIN';
            }

            return $content;
        });

        $app->get('/admin', function() use ($app) {
            return 'admin';
        });

        return $app;
    }
}
