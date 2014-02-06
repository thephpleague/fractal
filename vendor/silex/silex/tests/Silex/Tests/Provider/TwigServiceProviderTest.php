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
use Silex\Provider\TwigServiceProvider;

use Symfony\Component\HttpFoundation\Request;

/**
 * TwigProvider test cases.
 *
 * @author Igor Wiedler <igor@wiedler.ch>
 */
class TwigServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testRegisterAndRender()
    {
        $app = new Application();

        $app->register(new TwigServiceProvider(), array(
            'twig.templates'    => array('hello' => 'Hello {{ name }}!'),
        ));

        $app->get('/hello/{name}', function ($name) use ($app) {
            return $app['twig']->render('hello', array('name' => $name));
        });

        $request = Request::create('/hello/john');
        $response = $app->handle($request);
        $this->assertEquals('Hello john!', $response->getContent());
    }

    public function testRenderFunction()
    {
        $app = new Application();

        $app->register(new TwigServiceProvider(), array(
            'twig.templates'    => array(
                'hello' => '{{ render("/foo") }}',
                'foo'   => 'foo',
            ),
        ));

        $app->get('/hello', function () use ($app) {
            return $app['twig']->render('hello');
        });

        $app->get('/foo', function () use ($app) {
            return $app['twig']->render('foo');
        });

        $request = Request::create('/hello');
        $response = $app->handle($request);
        $this->assertEquals('foo', $response->getContent());
    }

    public function testLoaderPriority()
    {
        $app = new Application();
        $app->register(new TwigServiceProvider(), array(
            'twig.templates'    => array('foo' => 'foo'),
        ));
        $loader = $this->getMock('\Twig_LoaderInterface');
        $loader->expects($this->never())->method('getSource');
        $app['twig.loader.filesystem'] = $app->share(function ($app) use ($loader) {
            return $loader;
        });
        $this->assertEquals('foo', $app['twig.loader']->getSource('foo'));
    }
}
