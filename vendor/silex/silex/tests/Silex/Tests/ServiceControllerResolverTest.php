<?php

/*
 * This file is part of the Silex framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Silex\Tests;

use Silex\ServiceControllerResolver;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

/**
 * Unit tests for ServiceControllerResolver, see ServiceControllerResolverRouterTest for some
 * integration tests
 */
class ServiceControllerResolverTest extends \PHPUnit_Framework_Testcase
{
    public function setup()
    {
        $this->mockResolver = $this->getMockBuilder('Symfony\Component\HttpKernel\Controller\ControllerResolverInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->app = new Application();
        $this->resolver = new ServiceControllerResolver($this->mockResolver, $this->app);
    }

    public function testShouldResolveServiceController()
    {
        $this->app['some_service'] = function() { return new \stdClass(); };

        $req = Request::create('/');
        $req->attributes->set('_controller', 'some_service:methodName');

        $this->assertEquals(
            array($this->app['some_service'], 'methodName'),
            $this->resolver->getController($req)
        );
    }

    public function testShouldDelegateNonStrings()
    {
        $req = Request::create('/');
        $req->attributes->set('_controller', function() {});

        $this->mockResolver->expects($this->once())
                           ->method('getController')
                           ->with($req)
                           ->will($this->returnValue(123));

        $this->assertEquals(123, $this->resolver->getController($req));
    }

    /**
     * Note: This doesn't test the regex extensively, just a common use case
     */
    public function testShouldDelegateNonMatchingSyntax()
    {
        $req = Request::create('/');
        $req->attributes->set('_controller', 'some_class::methodName');

        $this->mockResolver->expects($this->once())
                           ->method('getController')
                           ->with($req)
                           ->will($this->returnValue(123));

        $this->assertEquals(123, $this->resolver->getController($req));
    }

    /**
     * @expectedException          InvalidArgumentException
     * @expectedExceptionMessage   Service "some_service" does not exist.
     */
    public function testShouldThrowIfServiceIsMissing()
    {
        $req = Request::create('/');
        $req->attributes->set('_controller', 'some_service:methodName');
        $this->resolver->getController($req);
    }

    public function testShouldDelegateGetArguments()
    {
        $req = Request::create('/');
        $this->mockResolver->expects($this->once())
                           ->method('getArguments')
                           ->with($req)
                           ->will($this->returnValue(123));

        $this->assertEquals(123, $this->resolver->getArguments($req, function() {}));
    }
}
