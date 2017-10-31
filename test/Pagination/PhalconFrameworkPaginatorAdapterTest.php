<?php

namespace League\Fractal\Test\Pagination;

use League\Fractal\Pagination\PhalconFrameworkPaginatorAdapter;
use Mockery;
use PHPUnit\Framework\TestCase;

class PhalconFrameworkPaginatorAdapterTest extends TestCase
{
    public function testPaginationAdapter()
    {
        $total = 50;
        $count = 10;
        $perPage = 10;
        $currentPage = 2;
        $lastPage = 5;

        $paginate =[
            'last'        => $lastPage,
            'current'     => $currentPage,
            'total_items' => $total,
            'total_pages' => $count,

        ];

        $paginator = Mockery::mock('Phalcon\Paginator\Adapter\QueryBuilder');
        $paginator->shouldReceive('currentPage')->andReturn($currentPage);
        $paginator->shouldReceive('lastPage')->andReturn($lastPage);
        $paginator->shouldReceive('count')->andReturn($count);
        $paginator->shouldReceive('total')->andReturn($total);
        $paginator->shouldReceive('getPaginate')->andReturn((object) $paginate);

        $adapter = new PhalconFrameworkPaginatorAdapter($paginator);

        $this->assertInstanceOf('League\Fractal\Pagination\PaginatorInterface', $adapter);
        $this->assertSame($currentPage, $adapter->getCurrentPage());
        $this->assertSame($lastPage, $adapter->getLastPage());
        $this->assertSame($count, $adapter->getCount());
        $this->assertSame($total, $adapter->getTotal());
    }

    public function tearDown()
    {
        Mockery::close();
    }
}
