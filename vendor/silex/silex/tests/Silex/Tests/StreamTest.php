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
 * Stream test cases.
 *
 * @author Igor Wiedler <igor@wiedler.ch>
 */
class StreamTest extends \PHPUnit_Framework_TestCase
{
    public function testStreamReturnsStreamingResponse()
    {
        $app = new Application();

        $response = $app->stream();
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\StreamedResponse', $response);
        $this->assertSame(false, $response->getContent());
    }

    public function testStreamActuallyStreams()
    {
        $i = 0;

        $stream = function () use (&$i) {
            $i++;
        };

        $app = new Application();
        $response = $app->stream($stream);

        $this->assertEquals(0, $i);

        $request = Request::create('/stream');
        $response->prepare($request);
        $response->sendContent();

        $this->assertEquals(1, $i);
    }
}
