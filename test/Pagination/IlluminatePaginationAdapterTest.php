<?php
namespace League\Fractal\Test\Pagination;

use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use Mockery;

class IlluminatePaginationAdapterTest extends \PHPUnit_Framework_TestCase
{
    public function testPaginationAdapter()
    {
        if (class_exists('Illuminate\Pagination\Factory')) {
            $envClass = 'Illuminate\Pagination\Factory';
        } elseif (class_exists('Illuminate\Pagination\Environment')) {
            $envClass = 'Illuminate\Pagination\Environment';
        } else {
            return $this->markTestSkipped('A pagination class was not available.');
        }

        $env = Mockery::mock($envClass)->makePartial();

        $env->setCurrentPage(2);
        $env->setBaseUrl('http://example.com/foo');
        $env->setPageName('page');

        $items = array(
            'Item 0',
            'Item 1',
            'Item 2',
            'Item 3',
            'Item 4',
        );

        $total       = 50;
        $perPage     = 5;
        $currentPage = 2;
        $lastPage    = 10;

        $paginator = Mockery::mock('Illuminate\Pagination\Paginator', array($env, $items, $total, $perPage))->makePartial();

        $paginator->shouldReceive('getCurrentPage')->andReturn($currentPage);
        $paginator->shouldReceive('getLastPage')->andReturn($lastPage);

        $adapter = new IlluminatePaginatorAdapter($paginator);

        $this->assertInstanceOf(
            'League\Fractal\Pagination\PaginatorInterface',
            $adapter
        );

        $this->assertEquals($currentPage, $adapter->getCurrentPage());
        $this->assertEquals($lastPage, $adapter->getLastPage());
        $this->assertEquals(count($items), $adapter->getCount());
        $this->assertEquals($total, $adapter->getTotal());
        $this->assertEquals($perPage, $adapter->getPerPage());
        $this->assertEquals(
            'http://example.com/foo?page=1',
            $adapter->getUrl(1)
        );
        $this->assertEquals(
            'http://example.com/foo?page=3',
            $adapter->getUrl(3)
        );

        // Test appending
        $paginator->appends(array('term1' => 'test1', 'term2' => 'test2'));
        $this->assertContains(
            'term1=test1&term2=test2',
            $adapter->getUrl(7)
        );
    }

    public function tearDown()
    {
        Mockery::close();
    }
}
