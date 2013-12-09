<?php namespace League\Fractal\Test;

use League\Fractal\ItemResource;

class ItemResourceTest extends \PHPUnit_Framework_TestCase
{
    protected $simpleItem = array('foo' => 'bar');

    /**
     * @covers League\Fractal\ItemResource::getData
     */
    public function testGetData()
    {
        $resource = new ItemResource($this->simpleItem, function (array $data) {
            return $data;
        });

        $this->assertEquals($resource->getData(), $this->simpleItem);
    }

    /**
     * @covers League\Fractal\ItemResource::getProcessor
     */
    public function testGetProcessor()
    {
        $resource = new ItemResource($this->simpleItem, function (array $data) {
            return $data;
        });

        $this->assertTrue(is_callable($resource->getProcessor()));
    }
}
