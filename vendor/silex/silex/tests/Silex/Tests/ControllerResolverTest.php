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

use Silex\ControllerResolver;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

/**
 * ControllerResolver test cases.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ControllerResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testGetArguments()
    {
        $app = new Application();
        $resolver = new ControllerResolver($app);

        $controller = function (Application $app) {};

        $args = $resolver->getArguments(Request::create('/'), $controller);
        $this->assertSame($app, $args[0]);
    }
}
