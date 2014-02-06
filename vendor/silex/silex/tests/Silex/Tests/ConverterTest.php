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

/**
 * Converter listener test cases.
 *
 * @author Igor Wiedler <igor@wiedler.ch>
 */
class ConverterTest extends \PHPUnit_Framework_TestCase
{
    public function testConvertingNonExistentAttributeShouldNotCallConverter()
    {
        $called = false;
        $converter = function () use (&$called) {
            $called = true;
        };

        $app = new Application();
        $app->get('/', function () { return 'hallo'; });
        $app['controllers']->convert('foo', $converter);

        $request = Request::create('/');
        $app->handle($request);

        $this->assertFalse($called);
    }
}
