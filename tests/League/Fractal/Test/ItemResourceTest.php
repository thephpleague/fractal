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
     * @covers League\Fractal\ItemResource::getTransformer
     */
    public function testGetTransformer()
    {
        $resource = new ItemResource($this->simpleItem, function (array $data) {
            return $data;
        });

        $this->assertTrue(is_callable($resource->getTransformer()));
    }
}
