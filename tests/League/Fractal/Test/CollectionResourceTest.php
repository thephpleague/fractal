<?php namespace League\Fractal\Test;

use League\Fractal\CollectionResource;

class CollectionResourceTest extends \PHPUnit_Framework_TestCase
{
    protected $simpleCollection = [
        ['foo' => 'bar'],
        ['baz' => 'ban'],
    ];

    /**
     * @covers League\Fractal\CollectionResource::getData
     */
    public function testGetData()
    {
        $resource = new CollectionResource($this->simpleCollection, function (array $data) {
            return $data;
        });

        $this->assertEquals($resource->getData(), $this->simpleCollection);
    }

    /**
     * @covers League\Fractal\CollectionResource::getProcessor
     */
    public function testGetProcessor()
    {
        $resource = new CollectionResource($this->simpleCollection, function (array $data) {
            return $data;
        });

        $this->assertTrue(is_callable($resource->getProcessor()));
    }
}
