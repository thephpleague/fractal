<?php

namespace League\Fractal\Test\Pagination;

use League\Fractal\Pagination\Cursor;
use Mockery;

class CursorTest extends \PHPUnit_Framework_TestCase
{

    public function testCursor()
    {
        $cursor = new Cursor(100, 90, 110, 10);

        $this->assertEquals($cursor->getCurrent(), 100);
        $this->assertEquals($cursor->getPrev(), 90);
        $this->assertEquals($cursor->getNext(), 110);
        $this->assertEquals($cursor->getCount(), 10);

        $cursor->setCurrent(110);
        $cursor->setPrev(106);
        $cursor->setNext(114);
        $cursor->setCount(4);

        $this->assertEquals($cursor->getCurrent(), 110);
        $this->assertEquals($cursor->getPrev(), 106);
        $this->assertEquals($cursor->getNext(), 114);
        $this->assertEquals($cursor->getCount(), 4);
    }

}
