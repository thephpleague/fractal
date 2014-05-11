<?php

use League\Fractal\Serializer\JsonApiSerializer;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Manager;
use League\Fractal\Scope;

class JsonApiSerializerTest extends PHPUnit_Framework_TestCase {

    public function testSerializingItemResource()
    {
        $manager = new Manager();
        $manager->parseIncludes('book');
        $manager->setSerializer(new JsonApiSerializer);

        $transformer = Mockery::mock('League\Fractal\TransformerAbstract[getAvailableIncludes,transform,processIncludedResources]');
        $transformer->shouldReceive('getAvailableIncludes')->twice()->andReturn(array('book'));
        $transformer->shouldReceive('transform')->once()->andReturnUsing(function (array $data) {
            return $data;
        });
        $transformer->shouldReceive('processIncludedResources')->once()->andReturn(array(
            'book' => array(
                'books' => array('yin' => 'yang')
            )
        ));

        $resource = new Item(array('bar' => 'baz'), $transformer, 'foo');

        $scope = new Scope($manager, $resource);

        $expected = array(
            'foo' => array(array('bar' => 'baz')),
            'linked' => array('books' => array('yin' => 'yang'))
        );

        $this->assertEquals($expected, $scope->toArray());
    }

    public function testSerializingCollectionResource()
    {
        $manager = new Manager();
        $manager->parseIncludes('book');
        $manager->setSerializer(new JsonApiSerializer);

        $transformer = Mockery::mock('League\Fractal\TransformerAbstract[getAvailableIncludes,transform,processIncludedResources]');
        $transformer->shouldReceive('getAvailableIncludes')->times(4)->andReturn(array('book'));
        $transformer->shouldReceive('transform')->twice()->andReturnUsing(function (array $data) {
            return $data;
        });
        $transformer->shouldReceive('processIncludedResources')->once()->andReturn(array(
            'book' => array(
                'books' => array('yin' => 'yang')
            )
        ));
        $transformer->shouldReceive('processIncludedResources')->once()->andReturn(array(
            'book' => array(
                'books' => array('ming' => 'mong')
            )
        ));

        $resource = new Collection(array(
            array('bar' => 'baz'),
            array('up' => 'down')
        ), $transformer, 'foo');

        $scope = new Scope($manager, $resource);

        $expected = array(
            'foo' => array(array('bar' => 'baz'), array('up' => 'down')),
            'linked' => array('books' => array('yin' => 'yang', 'ming' => 'mong'))
        );

        $this->assertEquals($expected, $scope->toArray());
    }

    public function tearDown()
    {
        Mockery::close();
    }

}
