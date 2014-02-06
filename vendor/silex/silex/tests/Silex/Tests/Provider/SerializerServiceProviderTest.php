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
use Silex\Provider\SerializerServiceProvider;

/**
 * SerializerServiceProvider test cases.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class SerializerServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testRegister()
    {
        $app = new Application();

        $app->register(new SerializerServiceProvider);

        $this->assertInstanceOf("Symfony\Component\Serializer\Serializer", $app['serializer']);
        $this->assertTrue($app['serializer']->supportsEncoding('xml'));
        $this->assertTrue($app['serializer']->supportsEncoding('json'));
        $this->assertTrue($app['serializer']->supportsDecoding('xml'));
        $this->assertTrue($app['serializer']->supportsDecoding('json'));
    }
}
