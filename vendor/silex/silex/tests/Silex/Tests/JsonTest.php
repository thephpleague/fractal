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

/**
 * JSON test cases.
 *
 * @author Igor Wiedler <igor@wiedler.ch>
 */
class JsonTest extends \PHPUnit_Framework_TestCase
{
    public function testJsonReturnsJsonResponse()
    {
        $app = new Application();

        $response = $app->json();
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $response);
        $response = json_decode($response->getContent(), true);
        $this->assertSame(array(), $response);
    }

    public function testJsonUsesData()
    {
        $app = new Application();

        $response = $app->json(array('foo' => 'bar'));
        $this->assertSame('{"foo":"bar"}', $response->getContent());
    }

    public function testJsonUsesStatus()
    {
        $app = new Application();

        $response = $app->json(array(), 202);
        $this->assertSame(202, $response->getStatusCode());
    }

    public function testJsonUsesHeaders()
    {
        $app = new Application();

        $response = $app->json(array(), 200, array('ETag' => 'foo'));
        $this->assertSame('foo', $response->headers->get('ETag'));
    }
}
