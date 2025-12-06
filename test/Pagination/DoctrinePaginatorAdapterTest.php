<?php

namespace League\Fractal\Test\Pagination;

use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use League\Fractal\Pagination\DoctrinePaginatorAdapter;
use League\Fractal\Test\Stub\SimpleTraversable;
use Mockery;
use PHPUnit\Framework\TestCase;

class DoctrinePaginatorAdapterTest extends TestCase
{
    public function testPaginationAdapter()
    {
        $total       = 50;
        $count       = 5;
        $perPage     = 5;
        $currentPage = 2;
        $lastPage    = 10;

        //Mock the doctrine paginator
        $paginator = Mockery::mock('Doctrine\ORM\Tools\Pagination\Paginator')->makePartial();
        $paginator->shouldReceive('count')->andReturn($total);


        //Mock the query that the paginator is acting on
        $query = Mockery::mock('Doctrine\ORM\AbstractQuery');
        $query->shouldReceive('getFirstResult')->andReturn(($currentPage - 1) * $perPage);
        $query->shouldReceive('getMaxResults')->andReturn($perPage);
        $paginator->shouldReceive('getQuery')->andReturn($query);

        //Mock the iterator of the paginator
        $iterator = new \ArrayIterator(range(1, $count));
        $paginator->shouldReceive('getIterator')->andReturn($iterator);

        $adapter = new DoctrinePaginatorAdapter($paginator, function ($page) {
            return 'http://example.com/foo?page=' . $page;
        });

        $this->assertInstanceOf(
            'League\Fractal\Pagination\PaginatorInterface',
            $adapter,
        );

        $this->assertSame($currentPage, $adapter->getCurrentPage());
        $this->assertSame($lastPage, $adapter->getLastPage());
        $this->assertSame($count, $adapter->getCount());
        $this->assertSame($total, $adapter->getTotal());
        $this->assertSame($perPage, $adapter->getPerPage());
        $this->assertSame(
            'http://example.com/foo?page=1',
            $adapter->getUrl(1),
        );
        $this->assertSame(
            'http://example.com/foo?page=3',
            $adapter->getUrl(3),
        );
    }

    public function testCountingTraversables()
    {
        $traversable = new SimpleTraversable(range(1, 100));
        $adapter = Mockery::mock('Doctrine\ORM\Tools\Pagination\Paginator');
        $adapter->shouldReceive('getIterator')->andReturn($traversable);
        $adapter = new DoctrinePaginatorAdapter($adapter, function ($page) {
            return (string) $page;
        });

        $this->assertEquals($traversable->key(), 0);
        $this->assertEquals($adapter->getCount(), 100);
        $this->assertEquals($traversable->key(), 0);
    }

    public function tearDown(): void
    {
        Mockery::close();
    }
}
