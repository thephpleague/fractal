<?php namespace League\Fractal\Test;

use League\Fractal\Resource\Item;
use Mockery;

class ItemTest extends \PHPUnit_Framework_TestCase
{
    protected $simpleItem = array('foo' => 'bar');

    public function testGetData()
    {
        $resource = new Item($this->simpleItem, function () {});
        $this->assertEquals($resource->getData(), $this->simpleItem);
    }

    public function testGetTransformer()
    {
        $resource = new Item($this->simpleItem, function () {
        });
        $this->assertTrue(is_callable($resource->getTransformer()));

        $transformer = 'thismightbeacallablestring';
        $resource = new Item($this->simpleItem, $transformer);
        $this->assertEquals($resource->getTransformer(), $transformer);
    }

    /**
     * @covers League\Fractal\Resource\Item::setResourceKey
     */
    public function testSetResourceKey()
    {
        $collection = Mockery::mock('League\Fractal\Resource\Item')->makePartial();
        $this->assertInstanceOf('League\Fractal\Resource\Item', $collection->setResourceKey('foo'));
    }

    /**
     * @covers League\Fractal\Resource\Item::getResourceKey
     */
    public function testGetResourceKey()
    {
        $collection = Mockery::mock('League\Fractal\Resource\Item')->makePartial();
        $collection->setResourceKey('foo');
        $this->assertEquals('foo', $collection->getResourceKey());
    }

    public function testCanSetAndGetMeta()
    {
        $meta = array(
            'foo' => 'bar',
            'bar' => [
                'baz' => 'qux',
            ],
        );

        $item = new Item();

        $item->setMeta($meta);

        $this->assertSame($meta, $item->getMeta());
    }
}
