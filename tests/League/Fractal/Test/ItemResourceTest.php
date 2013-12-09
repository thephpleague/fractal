<?php namespace League\Fractal\Test;

use League\Fractal\ItemResource;

class ItemResourceTest extends \PHPUnit_Framework_TestCase
{
    protected $simpleItem = array('foo' => 'bar');

    public function testGetData()
    {
        $resource = new ItemResource($this->simpleItem, function (array $data) {
            return $data;
        });

        $this->assertEquals($resource->getData(), $this->simpleItem);
    }

    public function testGetTransformer()
    {
        $resource = new ItemResource($this->simpleItem, function () {
        });
        $this->assertTrue(is_callable($resource->getTransformer()));

        $resource = new ItemResource($this->simpleItem, 'SomeClass');
        $this->assertEquals($resource->getTransformer(), 'SomeClass');
    }
}
