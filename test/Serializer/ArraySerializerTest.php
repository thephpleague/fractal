<?php

use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Scope;
use League\Fractal\Serializer\ArraySerializer;
use League\Fractal\Test\Stub\Transformer\GenericBookTransformer;

class ArraySerializerTest extends PHPUnit_Framework_TestCase
{
    private $bookItemInput = array(
        'title' => 'Foo',
        'year' => '1991',
        '_author' => array(
            'name' => 'Dave',
        ),
    );

    private $bookCollectionInput = array(
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

    public function testSerializingItemResource()
    {
        $manager = new Manager();
        $manager->parseIncludes('author');
        $manager->setSerializer(new ArraySerializer());

        $resource = new Item($this->bookItemInput, new GenericBookTransformer(), 'book');

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

        $resource = new Collection($this->bookCollectionInput, new GenericBookTransformer(), 'books');

        // Try without metadata
        $scope = new Scope($manager, $resource);

        $expected = array(
            'books' => array(
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
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        // JSON array of JSON objects
        $expectedJson = '{"books":[{"title":"Foo","year":1991,"author":{"name":"Dave"}},{"title":"Bar","year":1997,"author":{"name":"Bob"}}]}';
        $this->assertEquals($expectedJson, $scope->toJson());

        // Same again with metadata
        $resource->setMetaValue('foo', 'bar');

        $scope = new Scope($manager, $resource);

        $expected = array(
            'books' => array(
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
            ),
            'meta' => array(
                'foo' => 'bar',
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"books":[{"title":"Foo","year":1991,"author":{"name":"Dave"}},{"title":"Bar","year":1997,"author":{"name":"Bob"}}],"meta":{"foo":"bar"}}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

    public function testSerializingCollectionResourceWithoutName()
    {
        $manager = new Manager();
        $manager->parseIncludes('author');
        $manager->setSerializer(new ArraySerializer());

        $resource = new Collection($this->bookCollectionInput, new GenericBookTransformer());

        // Try without metadata
        $scope = new Scope($manager, $resource);

        // JSON array of JSON objects
        $expectedJson = '{"data":[{"title":"Foo","year":1991,"author":{"name":"Dave"}},{"title":"Bar","year":1997,"author":{"name":"Bob"}}]}';
        $this->assertEquals($expectedJson, $scope->toJson());

        // Same again with metadata
        $resource->setMetaValue('foo', 'bar');

        $scope = new Scope($manager, $resource);

        $expectedJson = '{"data":[{"title":"Foo","year":1991,"author":{"name":"Dave"}},{"title":"Bar","year":1997,"author":{"name":"Bob"}}],"meta":{"foo":"bar"}}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

    public function tearDown()
    {
        Mockery::close();
    }
}
