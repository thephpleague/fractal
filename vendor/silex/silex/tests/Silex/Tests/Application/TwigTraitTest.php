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
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * TwigTrait test cases.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @requires PHP 5.4
 */
class TwigTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testRender()
    {
        $app = $this->createApplication();

        $app['twig'] = $mailer = $this->getMockBuilder('Twig_Environment')->disableOriginalConstructor()->getMock();
        $mailer->expects($this->once())->method('render')->will($this->returnValue('foo'));

        $response = $app->render('view');
        $this->assertEquals('Symfony\Component\HttpFoundation\Response', get_class($response));
        $this->assertEquals('foo', $response->getContent());
    }

    public function testRenderKeepResponse()
    {
        $app = $this->createApplication();

        $app['twig'] = $mailer = $this->getMockBuilder('Twig_Environment')->disableOriginalConstructor()->getMock();
        $mailer->expects($this->once())->method('render')->will($this->returnValue('foo'));

        $response = $app->render('view', array(), new Response('', 404));
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testRenderForStream()
    {
        $app = $this->createApplication();

        $app['twig'] = $mailer = $this->getMockBuilder('Twig_Environment')->disableOriginalConstructor()->getMock();
        $mailer->expects($this->once())->method('display')->will($this->returnCallback(function () { echo 'foo'; }));

        $response = $app->render('view', array(), new StreamedResponse());
        $this->assertEquals('Symfony\Component\HttpFoundation\StreamedResponse', get_class($response));

        ob_start();
        $response->send();
        $this->assertEquals('foo', ob_get_clean());
    }

    public function testRenderView()
    {
        $app = $this->createApplication();

        $app['twig'] = $mailer = $this->getMockBuilder('Twig_Environment')->disableOriginalConstructor()->getMock();
        $mailer->expects($this->once())->method('render');

        $app->renderView('view');
    }

    public function createApplication()
    {
        $app = new TwigApplication();
        $app->register(new TwigServiceProvider());

        return $app;
    }
}
