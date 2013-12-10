<?php namespace League\Fractal\Test;

use League\Fractal\Resource\Collection;

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
}
