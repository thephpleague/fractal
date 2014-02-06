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

use Silex\LazyUrlMatcher;

/**
 * LazyUrlMatcher test case.
 *
 * @author Leszek Prabucki <leszek.prabucki@gmail.com>
 */
class LazyUrlMatcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Silex\LazyUrlMatcher::getUrlMatcher
     */
    public function testUserMatcherIsCreatedLazily()
    {
        $callCounter = 0;
        $urlMatcher = $this->getMock('Symfony\Component\Routing\Matcher\UrlMatcherInterface');

        $matcher = new LazyUrlMatcher(function () use ($urlMatcher, &$callCounter) {
            $callCounter++;

            return $urlMatcher;
        });

        $this->assertEquals(0, $callCounter);
        $matcher->match('path');
        $this->assertEquals(1, $callCounter);
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Factory supplied to LazyUrlMatcher must return implementation of UrlMatcherInterface.
     */
    public function testThatCanInjectUrlMatcherOnly()
    {
        $matcher = new LazyUrlMatcher(function () {
            return 'someMatcher';
        });

        $matcher->match('path');
    }

    /**
     * @covers Silex\LazyUrlMatcher::match
     */
    public function testMatchIsProxy()
    {
        $urlMatcher = $this->getMock('Symfony\Component\Routing\Matcher\UrlMatcherInterface');
        $urlMatcher->expects($this->once())
            ->method('match')
            ->with('path')
            ->will($this->returnValue('matcherReturnValue'));

        $matcher = new LazyUrlMatcher(function () use ($urlMatcher) {
            return $urlMatcher;
        });
        $result = $matcher->match('path');

        $this->assertEquals('matcherReturnValue', $result);
    }

    /**
     * @covers Silex\LazyUrlMatcher::setContext
     */
    public function testSetContextIsProxy()
    {
        $context = $this->getMock('Symfony\Component\Routing\RequestContext');
        $urlMatcher = $this->getMock('Symfony\Component\Routing\Matcher\UrlMatcherInterface');
        $urlMatcher->expects($this->once())
            ->method('setContext')
            ->with($context);

        $matcher = new LazyUrlMatcher(function () use ($urlMatcher) {
            return $urlMatcher;
        });
        $result = $matcher->setContext($context);
    }

    /**
     * @covers Silex\LazyUrlMatcher::getContext
     */
    public function testGetContextIsProxy()
    {
        $context = $this->getMock('Symfony\Component\Routing\RequestContext');
        $urlMatcher = $this->getMock('Symfony\Component\Routing\Matcher\UrlMatcherInterface');
        $urlMatcher->expects($this->once())
            ->method('getContext')
            ->will($this->returnValue($context));

        $matcher = new LazyUrlMatcher(function () use ($urlMatcher) {
            return $urlMatcher;
        });
        $resultContext = $matcher->getContext();

        $this->assertSame($resultContext, $context);
    }
}
