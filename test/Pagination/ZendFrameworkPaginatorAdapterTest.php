<?php
namespace League\Fractal\Test\Pagination;

use League\Fractal\Pagination\ZendFrameworkPaginatorAdapter;
use Mockery;

class ZendFrameworkPaginatorAdapterTest extends \PHPUnit_Framework_TestCase
{
    public function testPaginationAdapter()
    {
        $items = array(
            'Item 1', 'Item 2', 'Item 3', 'Item 4', 'Item 5', 'Item 6', 'Item 7', 'Item 8', 'Item 9', 'Item 10',
            'Item 11', 'Item 12', 'Item 13', 'Item 14', 'Item 15', 'Item 16', 'Item 17', 'Item 18', 'Item 19', 'Item 20',
            'Item 21', 'Item 22', 'Item 23', 'Item 24', 'Item 25', 'Item 26', 'Item 27', 'Item 28', 'Item 29', 'Item 30',
            'Item 31', 'Item 32', 'Item 33', 'Item 34', 'Item 35', 'Item 36', 'Item 37', 'Item 38', 'Item 39', 'Item 40',
            'Item 41', 'Item 42', 'Item 43', 'Item 44', 'Item 45', 'Item 46', 'Item 47', 'Item 48', 'Item 49', 'Item 50',
        );

        $adapter = Mockery::mock('Zend\Paginator\Adapter\ArrayAdapter', array($items))->makePartial();

        $total = 50;
        $count = 10;
        $perPage = 10;
        $currentPage = 2;
        $lastPage = 5;

        $paginator = Mockery::mock('Zend\Paginator\Paginator', array($adapter))->makePartial();

        $paginator->shouldReceive('getCurrentPageNumber')->andReturn($currentPage);
        $paginator->shouldReceive('count')->andReturn($lastPage);
        $paginator->shouldReceive('getItemCountPerPage')->andReturn($perPage);

        $adapter = new ZendFrameworkPaginatorAdapter($paginator, function ($page) {
            return 'http://example.com/foo?page='.$page;
        });

        $this->assertInstanceOf('League\Fractal\Pagination\PaginatorInterface', $adapter);

        $this->assertEquals($currentPage, $adapter->getCurrentPage());
        $this->assertEquals($lastPage, $adapter->getLastPage());
        $this->assertEquals($count, $adapter->getCount());
        $this->assertEquals($total, $adapter->getTotal());
        $this->assertEquals($perPage, $adapter->getPerPage());
        $this->assertEquals('http://example.com/foo?page=1', $adapter->getUrl(1));
        $this->assertEquals('http://example.com/foo?page=3', $adapter->getUrl(3));
    }

    public function tearDown()
    {
        Mockery::close();
    }
}
