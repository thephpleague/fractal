<?php

use League\Fractal\Serializer\DataArraySerializer;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Manager;
use League\Fractal\Scope;
use League\Fractal\Test\Stub\Transformer\GenericBookTransformer;

class DataArraySerializerTest extends PHPUnit_Framework_TestCase {

    public function testSerializingItemResource()
    {
        $manager = new Manager();
        $manager->parseIncludes('author');
        $manager->setSerializer(new DataArraySerializer());

        $bookData = array(
            'title' => 'Foo',
            'year' => '1991',
            '_author' => array(
                'name' => 'Dave',
            ),
        );

        // Try without metadata
        $resource = new Item($bookData, new GenericBookTransformer(), 'book');
        $scope = new Scope($manager, $resource);

        $expected = array(
            'data' => array(
                'title' => 'Foo',
                'year' => 1991,
                'author' => array(
                    'data' => array(
                        'name' => 'Dave',
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $scope->toArray());


        // Same again with metadata
        $resource = new Item($bookData, new GenericBookTransformer(), 'book');
        $resource->setMetaValue('foo', 'bar');

        $scope = new Scope($manager, $resource);

        $expected = array(
            'meta' => array(
                'foo' => 'bar',
            ),
            'data' => array(
                'title' => 'Foo',
                'year' => 1991,
                'author' => array(
                    'data' => array(
                        'name' => 'Dave',
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $scope->toArray());
    }

    public function testSerializingCollectionResource()
    {
        $manager = new Manager();
        $manager->parseIncludes('author');
        $manager->setSerializer(new DataArraySerializer());

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

        // Try without metadata
        $resource = new Collection($booksData, new GenericBookTransformer(), 'book');

        $scope = new Scope($manager, $resource);

        $expected = array(
            'data' => array(
                array(
                    'title' => 'Foo',
                    'year' => 1991,
                    'author' => array(
                        'data' => array(
                            'name' => 'Dave',
                        ),
                    ),
                ),
                array(
                    'title' => 'Bar',
                    'year' => 1997,
                    'author' => array(
                        'data' => array(
                            'name' => 'Bob',
                        ),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"data":[{"title":"Foo","year":1991,"author":{"data":{"name":"Dave"}}},{"title":"Bar","year":1997,"author":{"data":{"name":"Bob"}}}]}';
        $this->assertEquals($expectedJson, $scope->toJson());

        // Same again with meta
        $resource = new Collection($booksData, new GenericBookTransformer(), 'book');
        $resource->setMetaValue('foo', 'bar');

        $scope = new Scope($manager, $resource);

        $expected = array(
            'meta' => array(
                'foo' => 'bar',
            ),
            'data' => array(
                array(
                    'title' => 'Foo',
                    'year' => 1991,
                    'author' => array(
                        'data' => array(
                            'name' => 'Dave',
                        ),
                    ),
                ),
                array(
                    'title' => 'Bar',
                    'year' => 1997,
                    'author' => array(
                        'data' => array(
                            'name' => 'Bob',
                        ),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"data":[{"title":"Foo","year":1991,"author":{"data":{"name":"Dave"}}},{"title":"Bar","year":1997,"author":{"data":{"name":"Bob"}}}],"meta":{"foo":"bar"}}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }


    public function tearDown()
    {
        Mockery::close();
    }

}
