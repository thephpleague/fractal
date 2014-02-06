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

use Silex\Controller;
use Silex\ControllerCollection;
use Silex\Exception\ControllerFrozenException;
use Silex\Route;

/**
 * ControllerCollection test cases.
 *
 * @author Igor Wiedler <igor@wiedler.ch>
 */
class ControllerCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testGetRouteCollectionWithNoRoutes()
    {
        $controllers = new ControllerCollection(new Route());
        $routes = $controllers->flush();
        $this->assertEquals(0, count($routes->all()));
    }

    public function testGetRouteCollectionWithRoutes()
    {
        $controllers = new ControllerCollection(new Route());
        $controllers->match('/foo', function () {});
        $controllers->match('/bar', function () {});

        $routes = $controllers->flush();
        $this->assertEquals(2, count($routes->all()));
    }

    public function testControllerFreezing()
    {
        $controllers = new ControllerCollection(new Route());

        $fooController = $controllers->match('/foo', function () {})->bind('foo');
        $barController = $controllers->match('/bar', function () {})->bind('bar');

        $controllers->flush();

        try {
            $fooController->bind('foo2');
            $this->fail();
        } catch (ControllerFrozenException $e) {
        }

        try {
            $barController->bind('bar2');
            $this->fail();
        } catch (ControllerFrozenException $e) {
        }
    }

    public function testConflictingRouteNames()
    {
        $controllers = new ControllerCollection(new Route());

        $mountedRootController = $controllers->match('/', function () {});

        $mainRootController = new Controller(new Route('/'));
        $mainRootController->bind($mainRootController->generateRouteName('main_'));

        $controllers->flush();

        $this->assertNotEquals($mainRootController->getRouteName(), $mountedRootController->getRouteName());
    }

    public function testUniqueGeneratedRouteNames()
    {
        $controllers = new ControllerCollection(new Route());

        $controllers->match('/a-a', function () {});
        $controllers->match('/a_a', function () {});

        $routes = $controllers->flush();

        $this->assertCount(2, $routes->all());
        $this->assertEquals(array('_a_a', '_a_a_'), array_keys($routes->all()));
    }

    public function testAssert()
    {
        $controllers = new ControllerCollection(new Route());
        $controllers->assert('id', '\d+');
        $controller = $controllers->match('/{id}/{name}/{extra}', function () {})->assert('name', '\w+')->assert('extra', '.*');
        $controllers->assert('extra', '\w+');

        $this->assertEquals('\d+', $controller->getRoute()->getRequirement('id'));
        $this->assertEquals('\w+', $controller->getRoute()->getRequirement('name'));
        $this->assertEquals('\w+', $controller->getRoute()->getRequirement('extra'));
    }

    public function testValue()
    {
        $controllers = new ControllerCollection(new Route());
        $controllers->value('id', '1');
        $controller = $controllers->match('/{id}/{name}/{extra}', function () {})->value('name', 'Fabien')->value('extra', 'Symfony');
        $controllers->value('extra', 'Twig');

        $this->assertEquals('1', $controller->getRoute()->getDefault('id'));
        $this->assertEquals('Fabien', $controller->getRoute()->getDefault('name'));
        $this->assertEquals('Twig', $controller->getRoute()->getDefault('extra'));
    }

    public function testConvert()
    {
        $controllers = new ControllerCollection(new Route());
        $controllers->convert('id', '1');
        $controller = $controllers->match('/{id}/{name}/{extra}', function () {})->convert('name', 'Fabien')->convert('extra', 'Symfony');
        $controllers->convert('extra', 'Twig');

        $this->assertEquals(array('id' => '1', 'name' => 'Fabien', 'extra' => 'Twig'), $controller->getRoute()->getOption('_converters'));
    }

    public function testRequireHttp()
    {
        $controllers = new ControllerCollection(new Route());
        $controllers->requireHttp();
        $controller = $controllers->match('/{id}/{name}/{extra}', function () {})->requireHttps();

        $this->assertEquals('https', $controller->getRoute()->getRequirement('_scheme'));

        $controllers->requireHttp();

        $this->assertEquals('http', $controller->getRoute()->getRequirement('_scheme'));
    }

    public function testBefore()
    {
        $controllers = new ControllerCollection(new Route());
        $controllers->before('mid1');
        $controller = $controllers->match('/{id}/{name}/{extra}', function () {})->before('mid2');
        $controllers->before('mid3');

        $this->assertEquals(array('mid1', 'mid2', 'mid3'), $controller->getRoute()->getOption('_before_middlewares'));
    }

    public function testAfter()
    {
        $controllers = new ControllerCollection(new Route());
        $controllers->after('mid1');
        $controller = $controllers->match('/{id}/{name}/{extra}', function () {})->after('mid2');
        $controllers->after('mid3');

        $this->assertEquals(array('mid1', 'mid2', 'mid3'), $controller->getRoute()->getOption('_after_middlewares'));
    }

    public function testRouteExtension()
    {
        $route = new MyRoute1();

        $controller = new ControllerCollection($route);
        $controller->foo('foo');

        $this->assertEquals('foo', $route->foo);
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testRouteMethodDoesNotExist()
    {
        $route = new MyRoute1();

        $controller = new ControllerCollection($route);
        $controller->bar();
    }
}

class MyRoute1 extends Route
{
    public $foo;

    public function foo($value)
    {
        $this->foo = $value;
    }
}
