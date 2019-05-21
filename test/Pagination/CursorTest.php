<?php

namespace League\Fractal\Test\Pagination;

use League\Fractal\Pagination\Cursor;
use PHPUnit\Framework\TestCase;

class CursorTest extends TestCase
{
    public function testCursor()
    {
        $cursor = new Cursor(100, 90, 110, 10);

        $this->assertSame($cursor->getCurrent(), 100);
        $this->assertSame($cursor->getPrev(), 90);
        $this->assertSame($cursor->getNext(), 110);
        $this->assertSame($cursor->getCount(), 10);

        $cursor->setCurrent(110);
        $cursor->setPrev(106);
        $cursor->setNext(114);
        $cursor->setCount(4);

        $this->assertSame($cursor->getCurrent(), 110);
        $this->assertSame($cursor->getPrev(), 106);
        $this->assertSame($cursor->getNext(), 114);
        $this->assertSame($cursor->getCount(), 4);
    }
}
