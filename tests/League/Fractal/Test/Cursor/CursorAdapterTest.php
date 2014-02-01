<?php

namespace League\Fractal\Test\Cursor;

use League\Fractal\Cursor\Cursor;
use Mockery;

class CursorAdapterTest extends \PHPUnit_Framework_TestCase
{

    public function testCursorAdapter()
    {
        $cursor = new Cursor(100, 110, 10);

        $this->assertEquals($cursor->getCurrent(), 100);
        $this->assertEquals($cursor->getNext(), 110);
        $this->assertEquals($cursor->getCount(), 10);

        $cursor->setCurrent(110);
        $cursor->setNext(114);
        $cursor->setCount(4);

        $this->assertEquals($cursor->getCurrent(), 110);
        $this->assertEquals($cursor->getNext(), 114);
        $this->assertEquals($cursor->getCount(), 4);
    }

}
