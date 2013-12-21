<?php namespace League\Fractal\Test;

use League\Fractal\Resource\Collection;
use Mockery;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    protected $simpleCollection = array(
        array('foo' => 'bar'),
        array('baz' => 'ban'),
    );

    public function testGetData()
    {
        $resource = new Collection($this->simpleCollection, function (array $data) {
            return $data;
        });

        $this->assertEquals($resource->getData(), $this->simpleCollection);
    }

    public function testGetTransformer()
    {
        $resource = new Collection($this->simpleCollection, function () {
        });
        $this->assertTrue(is_callable($resource->getTransformer()));

        $resource = new Collection($this->simpleCollection, 'SomeClass');
        $this->assertEquals($resource->getTransformer(), 'SomeClass');
    }

    /**
     * @covers League\Fractal\Resource\Collection::setPaginator
     */
    public function testSetAvailableEmbeds()
    {
        $paginator = Mockery::mock('League\Fractal\Pagination\IlluminatePaginationAdapter');
        $collection = Mockery::mock('League\Fractal\Resource\Collection')->makePartial();
        $this->assertInstanceOf('League\Fractal\Resource\Collection', $collection->setPaginator($paginator));
    }

    public function testGetPaginator()
    {
        $paginator = Mockery::mock('League\Fractal\Pagination\IlluminatePaginationAdapter');
        $collection = Mockery::mock('League\Fractal\Resource\Collection')->makePartial();
        $collection->setPaginator($paginator);
        $this->assertInstanceOf('Illuminate\Pagination\Paginator', $collection->getPaginator());
    }

    public function tearDown()
    {
        Mockery::close();
    }
}
