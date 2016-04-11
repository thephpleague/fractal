<?php

namespace League\Fractal\Test\Hal;

use League\Fractal\Hal\Currie;

class CurrieTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException InvalidArgumentException
     */
    public function testNotValidStringParam()
    {
        new Currie(true, 'href', true, []);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testNotValidBoolParam()
    {
        new Currie('name', 'href', 'true', []);
    }
}
