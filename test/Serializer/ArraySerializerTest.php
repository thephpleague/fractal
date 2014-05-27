<?php

use League\Fractal\Serializer\ArraySerializer;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Manager;
use League\Fractal\Scope;
use League\Fractal\Test\Stub\Transformer\GenericBookTransformer;

class ArraySerializerTest extends PHPUnit_Framework_TestCase {

    public function testSerializingItemResource()
    {
        $manager = new Manager();
        $manager->parseIncludes('author');
        $manager->setSerializer(new ArraySerializer());

        $bookData = array(
            'title' => 'Foo',
            'year' => '1991',
            '_author' => array(
                'name' => 'Dave',
            ),
        );

        $resource = new Item($bookData, new GenericBookTransformer(), 'book');

        // Try without metadata
        $scope = new Scope($manager, $resource);

        $expected = array(
            'title' => 'Foo',
            'year' => 1991,
            'author' => array(
                'name' => 'Dave',
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        // Same again with meta
        $resource->setMetaValue('foo', 'bar');

        $scope = new Scope($manager, $resource);

        $expected = array(
            'title' => 'Foo',
            'year' => 1991,
            'author' => array(
                'name' => 'Dave',
            ),
            'meta' => array(
                'foo' => 'bar',
            ),
        );

        $this->assertEquals($expected, $scope->toArray());
    }

    public function testSerializingCollectionResource()
    {
        $manager = new Manager();
        $manager->parseIncludes('author');
        $manager->setSerializer(new ArraySerializer());

        $booksData = array(
            array(
                'title' => 'Foo',
                'year' => '1991',
                '_author' => array(
                    'name' => 'Dave',
                ),
            ),
            array(
                'title' => 'Bar',
                'year' => '1997',
                '_author' => array(
                    'name' => 'Bob',
                ),
            ),
        );

        $resource = new Collection($booksData, new GenericBookTransformer(), 'book');

        // Try without metadata
        $scope = new Scope($manager, $resource);

        $expected = array(
            array(
                'title' => 'Foo',
                'year' => 1991,
                'author' => array(
                    'name' => 'Dave',
                ),
            ),
            array(
                'title' => 'Bar',
                'year' => 1997,
                'author' => array(
                    'name' => 'Bob',
                ),
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        // JSON array of JSON objects
        $expectedJson = '[{"title":"Foo","year":1991,"author":{"name":"Dave"}},{"title":"Bar","year":1997,"author":{"name":"Bob"}}]';
        $this->assertEquals($expectedJson, $scope->toJson());

        // Same again with metadata
        $resource->setMetaValue('foo', 'bar');

        $scope = new Scope($manager, $resource);

        $expected = array(
            array(
                'title' => 'Foo',
                'year' => 1991,
                'author' => array(
                    'name' => 'Dave',
                ),
            ),
            array(
                'title' => 'Bar',
                'year' => 1997,
                'author' => array(
                    'name' => 'Bob',
                ),
            ),
            'meta' => array(
                'foo' => 'bar',
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        // This JSON sucks, because when you add a string key then it has to string up all the other keys. Using meta in Array is shit
        $expectedJson = '{"0":{"title":"Foo","year":1991,"author":{"name":"Dave"}},"1":{"title":"Bar","year":1997,"author":{"name":"Bob"}},"meta":{"foo":"bar"}}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }


    public function tearDown()
    {
        Mockery::close();
    }

}
