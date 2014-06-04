<?php
namespace League\Fractal\Test\Pagination;

use Illuminate\Pagination\Factory as PaginationFactory;
use Illuminate\Pagination\Paginator;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use Mockery;

class IlluminatePaginationAdapterTest extends \PHPUnit_Framework_TestCase
{
    public function testPaginationAdapter()
    {
        $factory = new PaginationFactory('page');

        $perPage = 2;
        $total = 10;

        // 2 Items, because perPage is 2. Normally the paginate() method would have that covered
        $items = array(
            'Item 1',
            'Item 2',
        );

        $factory->setBaseUrl('http://example.com/foo');
        $factory->setCurrentPage(2);

        $paginator = $factory->make($items, $total, $perPage);

        $adapter = new IlluminatePaginatorAdapter($paginator);

        $this->assertInstanceOf(
            'League\Fractal\Pagination\PaginatorInterface',
            $adapter
        );

        $this->assertEquals(2, $adapter->getCurrentPage());
        $this->assertEquals(5, $adapter->getLastPage());
        $this->assertEquals($total, $adapter->getTotal());
        $this->assertEquals($perPage, $adapter->getPerPage());
        $this->assertEquals($perPage, $adapter->getCount());
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

