<?php namespace League\Fractal\Test\Resource;

use League\Fractal\Resource\Item;
use Mockery;
use PHPUnit\Framework\TestCase;

class ItemTest extends TestCase
{
    protected $simpleItem = ['foo' => 'bar'];

    public function testGetData()
    {
        $item = new Item($this->simpleItem, function () {});

        $this->assertSame($item->getData(), $this->simpleItem);
    }

    public function testGetTransformer()
    {
        $item = new Item($this->simpleItem, function () {});

        $this->assertTrue(is_callable($item->getTransformer()));

        $transformer = 'thismightbeacallablestring';
        $item = new Item($this->simpleItem, $transformer);

        $this->assertSame($item->getTransformer(), $transformer);
    }

    /**
     * @covers \League\Fractal\Resource\Item::setResourceKey
     */
    public function testSetResourceKey()
    {
        $item = Mockery::mock('League\Fractal\Resource\Item')->makePartial();

        $this->assertSame($item, $item->setResourceKey('foo'));
    }

    /**
     * @covers \League\Fractal\Resource\Item::getResourceKey
     */
    public function testGetResourceKey()
    {
        $item = Mockery::mock('League\Fractal\Resource\Item')->makePartial();
        $item->setResourceKey('foo');

        $this->assertSame('foo', $item->getResourceKey());
    }
}
