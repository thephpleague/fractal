<?php namespace League\Fractal\Test;

use League\Fractal\PaginatorResource;

use Illuminate\Pagination\Environment;
use Illuminate\Pagination\Paginator;

use Mockery as m;

class PaginatorResourceTest extends \PHPUnit_Framework_TestCase
{
    protected $simpleCollection = array(
        array('foo' => 'bar'),
        array('baz' => 'ban'),
    );

    public function testGetData()
    {
        $paginator = m::mock('Illuminate\Pagination\Paginator');
        $paginator->shouldReceive('getCollection')->once()->andReturn($this->simpleCollection);

        $resource = new PaginatorResource($paginator, function (array $data) {
            return $data;
        });

        $this->assertEquals($resource->getData(), $this->simpleCollection);
    }

    public function testGetPaginator()
    {
        $paginator = m::mock('Illuminate\Pagination\Paginator');
        $paginator->shouldReceive('getCollection')->once()->andReturn($this->simpleCollection);

        $resource = new PaginatorResource($paginator, function () {});

        $this->assertInstanceOf('Illuminate\Pagination\Paginator', $resource->getPaginator());
    }

    public function testGetTransformer()
    {
        $paginator = m::mock('Illuminate\Pagination\Paginator');
        $paginator->shouldReceive('getCollection')->times(2)->andReturn($this->simpleCollection);

        $resource = new PaginatorResource($paginator, function () {
        });
        $this->assertTrue(is_callable($resource->getTransformer()));

        $resource = new PaginatorResource($paginator, 'SomeClass');
        $this->assertEquals($resource->getTransformer(), 'SomeClass');
    }

    public function tearDown()
    {
        m::close();
    }
}
