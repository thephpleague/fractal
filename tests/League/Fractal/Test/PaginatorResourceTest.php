<?php namespace League\Fractal\Test;

use League\Fractal\PaginatorResource;

use Illuminate\Pagination\Environment;
use Illuminate\Pagination\Paginator;

use Mockery as m;

class PaginatorResourceTest extends \PHPUnit_Framework_TestCase
{
    protected $simpleCollection = [
        ['foo' => 'bar'],
        ['baz' => 'ban'],
    ];

    /**
     * @covers League\Fractal\PaginatorResource::getData
     */
    public function testGetData()
    {
        $paginator = m::mock('Illuminate\Pagination\Paginator');
        $paginator->shouldReceive('getCollection')->once()->andReturn($this->simpleCollection);

        $resource = new PaginatorResource($paginator, function (array $data) {
            return $data;
        });

        $this->assertEquals($resource->getData(), $this->simpleCollection);
    }

    /**
     * @covers League\Fractal\PaginatorResource::getPaginator
     */
    public function testGetPaginator()
    {
        $paginator = m::mock('Illuminate\Pagination\Paginator');
        $paginator->shouldReceive('getCollection')->once()->andReturn($this->simpleCollection);

        $resource = new PaginatorResource($paginator, function (array $data) {
            return $data;
        });

        $this->assertInstanceOf('Illuminate\Pagination\Paginator', $resource->getPaginator());
    }

    /**
     * @covers League\Fractal\PaginatorResource::getProcessor
     */
    public function testGetProcessor()
    {
        $paginator = m::mock('Illuminate\Pagination\Paginator');
        $paginator->shouldReceive('getCollection')->once()->andReturn($this->simpleCollection);

        $resource = new PaginatorResource($paginator, function (array $data) {
            return $data;
        });

        $this->assertTrue(is_callable($resource->getProcessor()));
    }

    public function tearDown()
    {
        m::close();
    }
}
