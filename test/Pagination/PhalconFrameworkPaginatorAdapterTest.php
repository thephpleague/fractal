<?php

namespace League\Fractal\Test\Pagination;

use League\Fractal\Pagination\PhalconFrameworkPaginatorAdapter;
use PHPUnit\Framework\TestCase;

class PhalconFrameworkPaginatorAdapterTest extends TestCase
{
    public function testPaginationAdapter()
    {
        $resultset = new \stdClass();
        $resultset->items       = array_fill(1, 10, 'fractal');
        $resultset->current     = 3;
        $resultset->first       = 1;
        $resultset->last        = 5;
        $resultset->next        = 4;
        $resultset->previous    = 2;
        $resultset->total_items = 50;
        $resultset->total_pages = 10;

        $adapter = new PhalconFrameworkPaginatorAdapter($resultset);
        $this->assertInstanceOf('League\Fractal\Pagination\PaginatorInterface', $adapter);
        $this->assertSame(3, $adapter->getCurrentPage());
        $this->assertSame(10, $adapter->getCount());
        $this->assertSame(50, $adapter->getTotal());
        $this->assertSame(10, $adapter->getPerPage());
        $this->assertSame(5, $adapter->getLastPage());
        $this->assertSame(4, $adapter->getNext());
    }
}
