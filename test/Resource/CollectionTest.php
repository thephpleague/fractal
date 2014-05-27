<?php namespace League\Fractal\Test;

use League\Fractal\Pagination\Cursor;
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
     * @covers League\Fractal\Resource\Collection::setCursor
     */
    public function testSetCursor()
    {
        $cursor = Mockery::mock('League\Fractal\Pagination\Cursor');
        $collection = Mockery::mock('League\Fractal\Resource\Collection')->makePartial();
        $this->assertInstanceOf('League\Fractal\Resource\Collection', $collection->setCursor($cursor));
    }

    /**
     * @covers League\Fractal\Resource\Collection::getCursor
     */
    public function testGetCursor()
    {
        $cursor = new Cursor;
        $collection = Mockery::mock('League\Fractal\Resource\Collection')->makePartial();
        $collection->setCursor($cursor);
        $this->assertInstanceOf('League\Fractal\Pagination\Cursor', $collection->getCursor());
    }

    /**
     * @covers League\Fractal\Resource\Collection::setPaginator
     */
    public function testSetPaginator()
    {
        $paginator = Mockery::mock('League\Fractal\Pagination\IlluminatePaginatorAdapter');
        $collection = Mockery::mock('League\Fractal\Resource\Collection')->makePartial();
        $this->assertInstanceOf('League\Fractal\Resource\Collection', $collection->setPaginator($paginator));
    }

    /**
     * @covers League\Fractal\Resource\Collection::getPaginator
     */
    public function testGetPaginator()
    {
        $paginator = Mockery::mock('League\Fractal\Pagination\IlluminatePaginatorAdapter');
        $collection = Mockery::mock('League\Fractal\Resource\Collection')->makePartial();
        $collection->setPaginator($paginator);
        $this->assertInstanceOf('Illuminate\Pagination\Paginator', $collection->getPaginator());
    }

    /**
     * @covers League\Fractal\Resource\Collection::setMetaValue
     */
    public function testSetMetaValue()
    {
        $collection = Mockery::mock('League\Fractal\Resource\Collection')->makePartial();
        $this->assertInstanceOf('League\Fractal\Resource\Collection', $collection->setMetaValue('foo', 'bar'));
    }

    /**
     * @covers League\Fractal\Resource\Collection::getMetaValue
     */
    public function testGetMeta()
    {
        $collection = Mockery::mock('League\Fractal\Resource\Collection')->makePartial();
        $collection->setMetaValue('foo', 'bar');
        $this->assertEquals(array('foo' => 'bar'), $collection->getMeta());
        $this->assertEquals('bar', $collection->getMetaValue('foo'));
    }

    /**
     * @covers League\Fractal\Resource\Collection::setResourceKey
     */
    public function testSetResourceKey()
    {
        $collection = Mockery::mock('League\Fractal\Resource\Collection')->makePartial();
        $this->assertInstanceOf('League\Fractal\Resource\Collection', $collection->setResourceKey('foo'));
    }

    /**
     * @covers League\Fractal\Resource\Collection::getResourceKey
     */
    public function testGetResourceKey()
    {
        $collection = Mockery::mock('League\Fractal\Resource\Collection')->makePartial();
        $collection->setResourceKey('foo');
        $this->assertEquals('foo', $collection->getResourceKey());
    }


    public function tearDown()
    {
        Mockery::close();
    }
}
