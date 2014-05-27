<?php namespace League\Fractal\Test;

use League\Fractal\Resource\Item;

class ItemResourceTest extends \PHPUnit_Framework_TestCase
{
    protected $simpleItem = array('foo' => 'bar');

    public function testGetData()
    {
        $resource = new Item($this->simpleItem, function() {});
        $this->assertEquals($resource->getData(), $this->simpleItem);
    }

    public function testGetTransformer()
    {
        $resource = new Item($this->simpleItem, function () {
        });
        $this->assertTrue(is_callable($resource->getTransformer()));

        $transformer = function() {};
        $resource = new Item($this->simpleItem, $transformer);
        $this->assertEquals($resource->getTransformer(), $transformer);
    }
}
