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
use Silex\ControllerCollection;
use Silex\Route;
use Silex\Provider\MonologServiceProvider;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Application test cases.
 *
 * @author Igor Wiedler <igor@wiedler.ch>
 */
class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    public function testMatchReturnValue()
    {
        $app = new Application();

        $returnValue = $app->match('/foo', function () {});
        $this->assertInstanceOf('Silex\Controller', $returnValue);

        $returnValue = $app->get('/foo', function () {});
        $this->assertInstanceOf('Silex\Controller', $returnValue);

        $returnValue = $app->post('/foo', function () {});
        $this->assertInstanceOf('Silex\Controller', $returnValue);

        $returnValue = $app->put('/foo', function () {});
        $this->assertInstanceOf('Silex\Controller', $returnValue);

        $returnValue = $app->delete('/foo', function () {});
        $this->assertInstanceOf('Silex\Controller', $returnValue);
    }

    public function testConstructorInjection()
    {
        // inject a custom parameter
        $params = array('param' => 'value');
        $app = new Application($params);
        $this->assertSame($params['param'], $app['param']);

        // inject an existing parameter
        $params = array('locale' => 'value');
        $app = new Application($params);
        $this->assertSame($params['locale'], $app['locale']);
    }

    public function testGetRequest()
    {
        $request = Request::create('/');

        $app = new Application();
        $app->get('/', function (Request $req) use ($request) {
            return $request === $req ? 'ok' : 'ko';
        });

        $this->assertEquals('ok', $app->handle($request)->getContent());
    }

    public function testGetRoutesWithNoRoutes()
    {
        $app = new Application();

        $routes = $app['routes'];
        $this->assertInstanceOf('Symfony\Component\Routing\RouteCollection', $routes);
        $this->assertEquals(0, count($routes->all()));
    }

    public function testGetRoutesWithRoutes()
    {
        $app = new Application();

        $app->get('/foo', function () {
            return 'foo';
        });

        $app->get('/bar', function () {
            return 'bar';
        });

        $routes = $app['routes'];
        $this->assertInstanceOf('Symfony\Component\Routing\RouteCollection', $routes);
        $this->assertEquals(0, count($routes->all()));
        $app->flush();
        $this->assertEquals(2, count($routes->all()));
    }

    public function testOnCoreController()
    {
        $app = new Application();

        $app->get('/foo/{foo}', function (\ArrayObject $foo) {
            return $foo['foo'];
        })->convert('foo', function ($foo) { return new \ArrayObject(array('foo' => $foo)); });

        $response = $app->handle(Request::create('/foo/bar'));
        $this->assertEquals('bar', $response->getContent());

        $app->get('/foo/{foo}/{bar}', function (\ArrayObject $foo) {
            return $foo['foo'];
        })->convert('foo', function ($foo, Request $request) { return new \ArrayObject(array('foo' => $foo.$request->attributes->get('bar'))); });

        $response = $app->handle(Request::create('/foo/foo/bar'));
        $this->assertEquals('foobar', $response->getContent());
    }

    public function testOn()
    {
        $app = new Application();
        $app['pass'] = false;

        $app->on('test', function(Event $e) use ($app) {
            $app['pass'] = true;
        });

        $app['dispatcher']->dispatch('test');

        $this->assertTrue($app['pass']);
    }

    public function testAbort()
    {
        $app = new Application();

        try {
            $app->abort(404);
            $this->fail();
        } catch (HttpException $e) {
            $this->assertEquals(404, $e->getStatusCode());
        }
    }

    /**
    * @dataProvider escapeProvider
    */
    public function testEscape($expected, $text)
    {
        $app = new Application();

        $this->assertEquals($expected, $app->escape($text));
    }

    public function escapeProvider()
    {
        return array(
            array('&lt;', '<'),
            array('&gt;', '>'),
            array('&quot;', '"'),
            array("'", "'"),
            array('abc', 'abc'),
        );
    }

    public function testControllersAsMethods()
    {
        $app = new Application();

        $app->get('/{name}', 'Silex\Tests\FooController::barAction');

        $this->assertEquals('Hello Fabien', $app->handle(Request::create('/Fabien'))->getContent());
    }

    public function testHttpSpec()
    {
        $app = new Application();
        $app['charset'] = 'ISO-8859-1';

        $app->get('/', function () {
            return 'hello';
        });

        // content is empty for HEAD requests
        $response = $app->handle(Request::create('/', 'HEAD'));
        $this->assertEquals('', $response->getContent());

        // charset is appended to Content-Type
        $response = $app->handle(Request::create('/'));

        $this->assertEquals('text/html; charset=ISO-8859-1', $response->headers->get('Content-Type'));
    }

    public function testRoutesMiddlewares()
    {
        $app = new Application();

        $test = $this;

        $middlewareTarget = array();
        $beforeMiddleware1 = function (Request $request) use (&$middlewareTarget, $test) {
            $test->assertEquals('/reached', $request->getRequestUri());
            $middlewareTarget[] = 'before_middleware1_triggered';
        };
        $beforeMiddleware2 = function (Request $request) use (&$middlewareTarget, $test) {
            $test->assertEquals('/reached', $request->getRequestUri());
            $middlewareTarget[] = 'before_middleware2_triggered';
        };
        $beforeMiddleware3 = function (Request $request) use (&$middlewareTarget, $test) {
            throw new \Exception('This middleware shouldn\'t run!');
        };

        $afterMiddleware1 = function (Request $request, Response $response) use (&$middlewareTarget, $test) {
            $test->assertEquals('/reached', $request->getRequestUri());
            $middlewareTarget[] = 'after_middleware1_triggered';
        };
        $afterMiddleware2 = function (Request $request, Response $response) use (&$middlewareTarget, $test) {
            $test->assertEquals('/reached', $request->getRequestUri());
            $middlewareTarget[] = 'after_middleware2_triggered';
        };
        $afterMiddleware3 = function (Request $request, Response $response) use (&$middlewareTarget, $test) {
            throw new \Exception('This middleware shouldn\'t run!');
        };

        $app->get('/reached', function () use (&$middlewareTarget) {
            $middlewareTarget[] = 'route_triggered';

            return 'hello';
        })
        ->before($beforeMiddleware1)
        ->before($beforeMiddleware2)
        ->after($afterMiddleware1)
        ->after($afterMiddleware2);

        $app->get('/never-reached', function () use (&$middlewareTarget) {
            throw new \Exception('This route shouldn\'t run!');
        })
        ->before($beforeMiddleware3)
        ->after($afterMiddleware3);

        $result = $app->handle(Request::create('/reached'));

        $this->assertSame(array('before_middleware1_triggered', 'before_middleware2_triggered', 'route_triggered', 'after_middleware1_triggered', 'after_middleware2_triggered'), $middlewareTarget);
        $this->assertEquals('hello', $result->getContent());
    }

    public function testRoutesBeforeMiddlewaresWithResponseObject()
    {
        $app = new Application();

        $app->get('/foo', function () {
            throw new \Exception('This route shouldn\'t run!');
        })
        ->before(function () {
            return new Response('foo');
        });

        $request = Request::create('/foo');
        $result = $app->handle($request);

        $this->assertEquals('foo', $result->getContent());
    }

    public function testRoutesAfterMiddlewaresWithResponseObject()
    {
        $app = new Application();

        $app->get('/foo', function () {
            return new Response('foo');
        })
        ->after(function () {
            return new Response('bar');
        });

        $request = Request::create('/foo');
        $result = $app->handle($request);

        $this->assertEquals('bar', $result->getContent());
    }

    public function testRoutesBeforeMiddlewaresWithRedirectResponseObject()
    {
        $app = new Application();

        $app->get('/foo', function () {
            throw new \Exception('This route shouldn\'t run!');
        })
        ->before(function () use ($app) {
            return $app->redirect('/bar');
        });

        $request = Request::create('/foo');
        $result = $app->handle($request);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $result);
        $this->assertEquals('/bar', $result->getTargetUrl());
    }

    public function testRoutesBeforeMiddlewaresTriggeredAfterSilexBeforeFilters()
    {
        $app = new Application();

        $middlewareTarget = array();
        $middleware = function (Request $request) use (&$middlewareTarget) {
            $middlewareTarget[] = 'middleware_triggered';
        };

        $app->get('/foo', function () use (&$middlewareTarget) {
            $middlewareTarget[] = 'route_triggered';
        })
        ->before($middleware);

        $app->before(function () use (&$middlewareTarget) {
            $middlewareTarget[] = 'before_triggered';
        });

        $app->handle(Request::create('/foo'));

        $this->assertSame(array('before_triggered', 'middleware_triggered', 'route_triggered'), $middlewareTarget);
    }

    public function testRoutesAfterMiddlewaresTriggeredBeforeSilexAfterFilters()
    {
        $app = new Application();

        $middlewareTarget = array();
        $middleware = function (Request $request) use (&$middlewareTarget) {
            $middlewareTarget[] = 'middleware_triggered';
        };

        $app->get('/foo', function () use (&$middlewareTarget) {
            $middlewareTarget[] = 'route_triggered';
        })
        ->after($middleware);

        $app->after(function () use (&$middlewareTarget) {
            $middlewareTarget[] = 'after_triggered';
        });

        $app->handle(Request::create('/foo'));

        $this->assertSame(array('route_triggered', 'middleware_triggered', 'after_triggered'), $middlewareTarget);
    }

    public function testFinishFilter()
    {
        $containerTarget = array();

        $app = new Application();

        $app->finish(function () use (&$containerTarget) {
            $containerTarget[] = '4_filterFinish';
        });

        $app->get('/foo', function () use (&$containerTarget) {
            $containerTarget[] = '1_routeTriggered';

            return new StreamedResponse(function() use (&$containerTarget) {
                $containerTarget[] = '3_responseSent';
            });
        });

        $app->after(function () use (&$containerTarget) {
            $containerTarget[] = '2_filterAfter';
        });

        $app->run(Request::create('/foo'));

        $this->assertSame(array('1_routeTriggered', '2_filterAfter', '3_responseSent', '4_filterFinish'), $containerTarget);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testNonResponseAndNonNullReturnFromRouteBeforeMiddlewareShouldThrowRuntimeException()
    {
        $app = new Application();

        $middleware = function (Request $request) {
            return 'string return';
        };

        $app->get('/', function () {
            return 'hello';
        })
        ->before($middleware);

        $app->handle(Request::create('/'), HttpKernelInterface::MASTER_REQUEST, false);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testNonResponseAndNonNullReturnFromRouteAfterMiddlewareShouldThrowRuntimeException()
    {
        $app = new Application();

        $middleware = function (Request $request) {
            return 'string return';
        };

        $app->get('/', function () {
            return 'hello';
        })
        ->after($middleware);

        $app->handle(Request::create('/'), HttpKernelInterface::MASTER_REQUEST, false);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testAccessingRequestOutsideOfScopeShouldThrowRuntimeException()
    {
        $app = new Application();

        $request = $app['request'];
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testAccessingRequestOutsideOfScopeShouldThrowRuntimeExceptionAfterHandling()
    {
        $app = new Application();
        $app->get('/', function () {
            return 'hello';
        });
        $app->handle(Request::create('/'), HttpKernelInterface::MASTER_REQUEST, false);

        $request = $app['request'];
    }

    public function testSubRequest()
    {
        $app = new Application();
        $app->get('/sub', function (Request $request) {
            return new Response('foo');
        });
        $app->get('/', function (Request $request) use ($app) {
            return $app->handle(Request::create('/sub'), HttpKernelInterface::SUB_REQUEST);
        });

        $this->assertEquals('foo', $app->handle(Request::create('/'))->getContent());
    }

    public function testSubRequestDoesNotReplaceMainRequestAfterHandling()
    {
        $mainRequest = Request::create('/');
        $subRequest = Request::create('/sub');

        $app = new Application();
        $app->get('/sub', function (Request $request) {
            return new Response('foo');
        });
        $app->get('/', function (Request $request) use ($subRequest, $app) {
            $response = $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

            // request in app must be the main request here
            $response->setContent($response->getContent().' '.$app['request']->getPathInfo());

            return $response;
        });

        $this->assertEquals('foo /', $app->handle($mainRequest)->getContent());
    }

    public function testRegisterShouldReturnSelf()
    {
        $app = new Application();
        $provider = $this->getMock('Silex\ServiceProviderInterface');

        $this->assertSame($app, $app->register($provider));
    }

    public function testMountShouldReturnSelf()
    {
        $app = new Application();
        $mounted = new ControllerCollection(new Route());
        $mounted->get('/{name}', function ($name) { return new Response($name); });

        $this->assertSame($app, $app->mount('/hello', $mounted));
    }

    public function testSendFile()
    {
        $app = new Application();

        try {
            $response = $app->sendFile(__FILE__, 200, array('Content-Type: application/php'));
            $this->assertInstanceOf('Symfony\Component\HttpFoundation\BinaryFileResponse', $response);
            $this->assertEquals(__FILE__, (string) $response->getFile());
        } catch (\RuntimeException $e) {
            $this->assertFalse(class_exists('Symfony\Component\HttpFoundation\BinaryFileResponse'));
        }
    }

    public function testRedirectDoesNotRaisePHPNoticesWhenMonologIsRegistered()
    {
        $app = new Application();

        ErrorHandler::register();
        $app['monolog.logfile'] = 'php://memory';
        $app->register(new MonologServiceProvider());
        $app->get('/foo/', function() { return 'ok'; });

        $response = $app->handle(Request::create('/foo'));
        $this->assertEquals(301, $response->getStatusCode());
    }
}

class FooController
{
    public function barAction(Application $app, $name)
    {
        return 'Hello '.$app->escape($name);
    }
}
