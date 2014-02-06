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
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware test cases.
 *
 * @author Igor Wiedler <igor@wiedler.ch>
 */
class MiddlewareTest extends \PHPUnit_Framework_TestCase
{
    public function testBeforeAndAfterFilter()
    {
        $i = 0;
        $test = $this;

        $app = new Application();

        $app->before(function () use (&$i, $test) {
            $test->assertEquals(0, $i);
            $i++;
        });

        $app->match('/foo', function () use (&$i, $test) {
            $test->assertEquals(1, $i);
            $i++;
        });

        $app->after(function () use (&$i, $test) {
            $test->assertEquals(2, $i);
            $i++;
        });

        $request = Request::create('/foo');
        $app->handle($request);

        $this->assertEquals(3, $i);
    }

    public function testAfterFilterWithResponseObject()
    {
        $i = 0;

        $app = new Application();

        $app->match('/foo', function () use (&$i) {
            $i++;

            return new Response('foo');
        });

        $app->after(function () use (&$i) {
            $i++;
        });

        $request = Request::create('/foo');
        $app->handle($request);

        $this->assertEquals(2, $i);
    }

    public function testMultipleFilters()
    {
        $i = 0;
        $test = $this;

        $app = new Application();

        $app->before(function () use (&$i, $test) {
            $test->assertEquals(0, $i);
            $i++;
        });

        $app->before(function () use (&$i, $test) {
            $test->assertEquals(1, $i);
            $i++;
        });

        $app->match('/foo', function () use (&$i, $test) {
            $test->assertEquals(2, $i);
            $i++;
        });

        $app->after(function () use (&$i, $test) {
            $test->assertEquals(3, $i);
            $i++;
        });

        $app->after(function () use (&$i, $test) {
            $test->assertEquals(4, $i);
            $i++;
        });

        $request = Request::create('/foo');
        $app->handle($request);

        $this->assertEquals(5, $i);
    }

    public function testFiltersShouldFireOnException()
    {
        $i = 0;

        $app = new Application();

        $app->before(function () use (&$i) {
            $i++;
        });

        $app->match('/foo', function () {
            throw new \RuntimeException();
        });

        $app->after(function () use (&$i) {
            $i++;
        });

        $app->error(function () {
            return 'error handled';
        });

        $request = Request::create('/foo');
        $app->handle($request);

        $this->assertEquals(2, $i);
    }

    public function testFiltersShouldFireOnHttpException()
    {
        $i = 0;

        $app = new Application();

        $app->before(function () use (&$i) {
            $i++;
        }, Application::EARLY_EVENT);

        $app->after(function () use (&$i) {
            $i++;
        });

        $app->error(function () {
            return 'error handled';
        });

        $request = Request::create('/nowhere');
        $app->handle($request);

        $this->assertEquals(2, $i);
    }

    public function testBeforeFilterPreventsBeforeMiddlewaresToBeExecuted()
    {
        $app = new Application();

        $app->before(function () { return new Response('app before'); });

        $app->get('/', function() {
            return new Response('test');
        })->before(function() {
            return new Response('middleware before');
        });

        $this->assertEquals('app before', $app->handle(Request::create('/'))->getContent());
    }

    public function testBeforeFilterExceptionsWhenHandlingAnException()
    {
        $app = new Application();

        $app->before(function () { throw new \RuntimeException(''); });

        // even if the before filter throws an exception, we must have the 404
        $this->assertEquals(404, $app->handle(Request::create('/'))->getStatusCode());
    }

    public function testRequestShouldBePopulatedOnBefore()
    {
        $app = new Application();

        $app->before(function () use ($app) {
            $app['project'] = $app['request']->get('project');
        });

        $app->match('/foo/{project}', function () use ($app) {
            return $app['project'];
        });

        $request = Request::create('/foo/bar');
        $this->assertEquals('bar', $app->handle($request)->getContent());

        $request = Request::create('/foo/baz');
        $this->assertEquals('baz', $app->handle($request)->getContent());
    }

    public function testBeforeFilterAccessesRequestAndCanReturnResponse()
    {
        $app = new Application();

        $app->before(function (Request $request) {
            return new Response($request->get('name'));
        });

        $app->match('/', function () use ($app) { throw new \Exception('Should never be executed'); });

        $request = Request::create('/?name=Fabien');
        $this->assertEquals('Fabien', $app->handle($request)->getContent());
    }

    public function testAfterFilterAccessRequestResponse()
    {
        $app = new Application();

        $app->after(function (Request $request, Response $response) {
            $response->setContent($response->getContent().'---');
        });

        $app->match('/', function () { return new Response('foo'); });

        $request = Request::create('/');
        $this->assertEquals('foo---', $app->handle($request)->getContent());
    }
}
