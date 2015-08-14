<?php

use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Scope;
use League\Fractal\Serializer\JsonApiSerializer;
use League\Fractal\Test\Stub\Transformer\JsonApiBookTransformer;

class JsonApiSerializerTest extends PHPUnit_Framework_TestCase
{
    private $manager;

    public function setUp()
    {
        $this->manager = new Manager();
        $this->manager->setSerializer(new JsonApiSerializer());
    }

    public function testSerializingItemResourceWithIncludes()
    {
        $this->manager->parseIncludes('author');

        $bookData = array(
            'id' => 1,
            'title' => 'Foo',
            'year' => '1991',
            '_author' => array(
                'id' => 1,
                'name' => 'Dave',
            ),
        );

        $resource = new Item($bookData, new JsonApiBookTransformer(), 'books');

        $scope = new Scope($this->manager, $resource);

        $expected = array(
            'data' => array(
                'type' => 'books',
                'id' => '1',
                'attributes' => array(
                    'title' => 'Foo',
                    'year' => 1991,
                ),
                'relationships' => array(
                    'author' => array(
                        'data' => array(
                            'type' => 'people',
                            'id' => '1',
                        ),
                    ),
                ),
            ),
            'included' => array(
                array(
                    'type' => 'people',
                    'id' => '1',
                    'attributes' => array(
                        'name' => 'Dave',
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"data":{"type":"books","id":"1","attributes":{"title":"Foo","year":1991},"relationships":{"author":{"data":{"type":"people","id":"1"}}}},"included":[{"type":"people","id":"1","attributes":{"name":"Dave"}}]}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

    public function testSerializingItemResourceWithoutIncludes()
    {
        $bookData = array(
            'id' => 1,
            'title' => 'Foo',
            'year' => '1991',
            '_author' => array(
                'id' => 1,
                'name' => 'Dave',
            ),
        );

        $resource = new Item($bookData, new JsonApiBookTransformer(), 'books');

        $scope = new Scope($this->manager, $resource);

        $expected = array(
            'data' => array(
                'type' => 'books',
                'id' => '1',
                'attributes' => array(
                    'title' => 'Foo',
                    'year' => 1991,
                ),
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"data":{"type":"books","id":"1","attributes":{"title":"Foo","year":1991}}}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

    public function testSerializingItemResourceWithMeta()
    {
        $bookData = array(
            'id' => 1,
            'title' => 'Foo',
            'year' => '1991',
            '_author' => array(
                'id' => 1,
                'name' => 'Dave',
            ),
        );

        $resource = new Item($bookData, new JsonApiBookTransformer(), 'books');
        $resource->setMetaValue('foo', 'bar');

        $scope = new Scope($this->manager, $resource);

        $expected = array(
            'data' => array(
                'type' => 'books',
                'id' => '1',
                'attributes' => array(
                    'title' => 'Foo',
                    'year' => 1991,
                ),
            ),
            'meta' => array(
                'foo' => 'bar',
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"data":{"type":"books","id":"1","attributes":{"title":"Foo","year":1991}},"meta":{"foo":"bar"}}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

    public function testSerializingCollectionResourceWithoutIncludes()
    {
        $booksData = array(
            array(
                'id' => 1,
                'title' => 'Foo',
                'year' => '1991',
                '_author' => array(
                    'id' => 1,
                    'name' => 'Dave',
                ),
            ),
            array(
                'id' => 2,
                'title' => 'Bar',
                'year' => '1997',
                '_author' => array(
                    'id' => 2,
                    'name' => 'Bob',
                ),
            ),
        );

        $resource = new Collection($booksData, new JsonApiBookTransformer(), 'books');
        $scope = new Scope($this->manager, $resource);

        $expected = array(
            'data' => array(
                array(
                    'type' => 'books',
                    'id' => '1',
                    'attributes' => array(
                        'title' => 'Foo',
                        'year' => 1991,
                    ),
                ),
                array(
                    'type' => 'books',
                    'id' => '2',
                    'attributes' => array(
                        'title' => 'Bar',
                        'year' => 1997,
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"data":[{"type":"books","id":"1","attributes":{"title":"Foo","year":1991}},{"type":"books","id":"2","attributes":{"title":"Bar","year":1997}}]}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

    public function testSerializingCollectionResourceWithIncludes()
    {
        $this->manager->parseIncludes('author');

        $booksData = array(
            array(
                'id' => 1,
                'title' => 'Foo',
                'year' => '1991',
                '_author' => array(
                    'id' => 1,
                    'name' => 'Dave',
                ),
            ),
            array(
                'id' => 2,
                'title' => 'Bar',
                'year' => '1997',
                '_author' => array(
                    'id' => 2,
                    'name' => 'Bob',
                ),
            ),
        );

        $resource = new Collection($booksData, new JsonApiBookTransformer(), 'books');
        $scope = new Scope($this->manager, $resource);

        $expected = array(
            'data' => array(
                array(
                    'type' => 'books',
                    'id' => '1',
                    'attributes' => array(
                        'title' => 'Foo',
                        'year' => 1991,
                    ),
                    'relationships' => array(
                        'author' => array(
                            'data' => array(
                                'type' => 'people',
                                'id' => '1',
                            ),
                        ),
                    ),
                ),
                array(
                    'type' => 'books',
                    'id' => '2',
                    'attributes' => array(
                        'title' => 'Bar',
                        'year' => 1997,
                    ),
                    'relationships' => array(
                        'author' => array(
                            'data' => array(
                                'type' => 'people',
                                'id' => '2',
                            ),
                        ),
                    ),
                ),
            ),
            'included' => array(
                array(
                    'type' => 'people',
                    'id' => '1',
                    'attributes' => array(
                        'name' => 'Dave',
                    ),
                ),
                array(
                    'type' => 'people',
                    'id' => '2',
                    'attributes' => array(
                        'name' => 'Bob',
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"data":[{"type":"books","id":"1","attributes":{"title":"Foo","year":1991},"relationships":{"author":{"data":{"type":"people","id":"1"}}}},{"type":"books","id":"2","attributes":{"title":"Bar","year":1997},"relationships":{"author":{"data":{"type":"people","id":"2"}}}}],"included":[{"type":"people","id":"1","attributes":{"name":"Dave"}},{"type":"people","id":"2","attributes":{"name":"Bob"}}]}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

    public function testSerializingCollectionResourceWithMeta()
    {
        $booksData = array(
            array(
                'id' => 1,
                'title' => 'Foo',
                'year' => '1991',
                '_author' => array(
                    'name' => 'Dave',
                ),
            ),
            array(
                'id' => 2,
                'title' => 'Bar',
                'year' => '1997',
                '_author' => array(
                    'name' => 'Bob',
                ),
            ),
        );

        $resource = new Collection($booksData, new JsonApiBookTransformer(), 'books');
        $resource->setMetaValue('foo', 'bar');

        $scope = new Scope($this->manager, $resource);

        $expected = array(
            'data' => array(
                array(
                    'type' => 'books',
                    'id' => '1',
                    'attributes' => array(
                        'title' => 'Foo',
                        'year' => 1991,
                    ),
                ),
                array(
                    'type' => 'books',
                    'id' => '2',
                    'attributes' => array(
                        'title' => 'Bar',
                        'year' => 1997,
                    ),
                ),
            ),
            'meta' => array(
                'foo' => 'bar',
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"data":[{"type":"books","id":"1","attributes":{"title":"Foo","year":1991}},{"type":"books","id":"2","attributes":{"title":"Bar","year":1997}}],"meta":{"foo":"bar"}}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

    public function testSerializingCollectionResourceWithDuplicatedIncludeData()
    {
        $this->manager->parseIncludes('author');

        $booksData = array(
            array(
                'id' => 1,
                'title' => 'Foo',
                'year' => '1991',
                '_author' => array(
                    'id' => 1,
                    'name' => 'Dave',
                ),
            ),
            array(
                'id' => 2,
                'title' => 'Bar',
                'year' => '1997',
                '_author' => array(
                    'id' => 1,
                    'name' => 'Dave',
                ),
            ),
        );

        $resource = new Collection($booksData, new JsonApiBookTransformer(), 'books');
        $scope = new Scope($this->manager, $resource);

        $expected = array(
            'data' => array(
                array(
                    'type' => 'books',
                    'id' => '1',
                    'attributes' => array(
                        'title' => 'Foo',
                        'year' => 1991,
                    ),
                    'relationships' => array(
                        'author' => array(
                            'data' => array(
                                'type' => 'people',
                                'id' => '1',
                            ),
                        ),
                    ),
                ),
                array(
                    'type' => 'books',
                    'id' => '2',
                    'attributes' => array(
                        'title' => 'Bar',
                        'year' => 1997,
                    ),
                    'relationships' => array(
                        'author' => array(
                            'data' => array(
                                'type' => 'people',
                                'id' => '1',
                            ),
                        ),
                    ),
                ),
            ),
            'included' => array(
                array(
                    'type' => 'people',
                    'id' => '1',
                    'attributes' => array(
                        'name' => 'Dave',
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"data":[{"type":"books","id":"1","attributes":{"title":"Foo","year":1991},"relationships":{"author":{"data":{"type":"people","id":"1"}}}},{"type":"books","id":"2","attributes":{"title":"Bar","year":1997},"relationships":{"author":{"data":{"type":"people","id":"1"}}}}],"included":[{"type":"people","id":"1","attributes":{"name":"Dave"}}]}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

    public function tearDown()
    {
        Mockery::close();
    }
}
