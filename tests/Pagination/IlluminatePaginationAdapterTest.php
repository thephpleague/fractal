<?php
namespace League\Fractal\Test\Pagination;

use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use Mockery;

class IlluminatePaginationAdapterTest extends \PHPUnit_Framework_TestCase
{
    public function testPaginationAdapter()
    {
        $paginator   = Mockery::mock('Illuminate\Pagination\Paginator')
            ->makePartial();
        $environment = Mockery::mock('Illuminate\Pagination\Environment')
            ->makePartial();

        $environment->setCurrentPage(2);
        $environment->setBaseUrl('http://example.com/foo');
        $environment->setPageName('page');

        $paginator->shouldReceive('getEnvironment')->andReturn($environment);
        $paginator->shouldReceive('getItems')->andReturn(array(
            'Item 0',
            'Item 1',
            'Item 2',
            'Item 3',
            'Item 4'
        ));
        $paginator->shouldReceive('getCurrentPage')->andReturn('2');
        $paginator->shouldReceive('getLastPage')->andReturn('10');
        $paginator->shouldReceive('getTotal')->andReturn('50');
        $paginator->shouldReceive('getCount')->andReturn('5');
        $paginator->shouldReceive('getPerPage')->andReturn('5');

        $adapter = new IlluminatePaginatorAdapter($paginator);

        $this->assertInstanceOf(
            'League\Fractal\Pagination\PaginatorInterface',
            $adapter
        );

        $this->assertEquals(2, $adapter->getCurrentPage());
        $this->assertEquals(10, $adapter->getLastPage());
        $this->assertEquals(50, $adapter->getTotal());
        $this->assertEquals(5, $adapter->getCount());
        $this->assertEquals(5, $adapter->getPerPage());
        $this->assertEquals(
            'http://example.com/foo?page=1',
            $adapter->getUrl(1)
        );
        $this->assertEquals(
            'http://example.com/foo?page=3',
            $adapter->getUrl(3)
        );
    }

    public function tearDown()
    {
        Mockery::close();
    }
}

