<?php

use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Scope;
use League\Fractal\Serializer\JsonApiSerializer;
use League\Fractal\Test\Stub\Transformer\GenericBookTransformer;

class JsonApiSerializerTest extends PHPUnit_Framework_TestCase
{
    private $manager;

    public function setUp()
    {
        $this->manager = new Manager();
        $this->manager->setSerializer(new JsonApiSerializer());
    }

    public function testSerializingEmptyIncludes()
    {
        $this->manager->parseIncludes('author');

        $bookData = array(
            'title' => 'Foo',
            'year' => '1991',
            'links' => array(
                'author' => 1,
            ),
            '_author' => array(
                'id' => 1,
                'name' => 'Dave',
            ),
        );

        $resource = new Item($bookData, new GenericBookTransformer(), 'book');

        $scope = new Scope($this->manager, $resource);

        $expected = array(
            'book' => array(
                array(
                    'title' => 'Foo',
                    'year' => 1991,
                    'links' => array(
                        'author' => 1,
                    ),
                ),
            ),
            'linked' => array(
                'author' => array(
                    array(
                        'id' => 1,
                        'name' => 'Dave',
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"book":[{"title":"Foo","year":1991,"links":{"author":1}}],"linked":{"author":[{"id":1,"name":"Dave"}]}}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

    public function testSerializingItemResource()
    {
        $this->manager->parseIncludes('author');

        $bookData = array(
            'title' => 'Foo',
            'year' => '1991',
            'links' => array(
                'author' => 1,
            ),
            '_author' => array(
                'id' => 1,
                'name' => 'Dave',
            ),
        );

        $resource = new Item($bookData, new GenericBookTransformer(), 'book');

        $scope = new Scope($this->manager, $resource);

        $expected = array(
            'book' => array(
                array(
                    'title' => 'Foo',
                    'year' => 1991,
                    'links' => array(
                        'author' => 1,
                    ),
                ),
            ),
            'linked' => array(
                'author' => array(
                    array(
                        'id' => 1,
                        'name' => 'Dave',
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"book":[{"title":"Foo","year":1991,"links":{"author":1}}],"linked":{"author":[{"id":1,"name":"Dave"}]}}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

    public function testSerializingCollectionResource()
    {
        $this->manager->parseIncludes('author');

        $booksData = array(
            array(
                'title' => 'Foo',
                'year' => '1991',
                'links' => array(
                    'author' => 1,
                ),
                '_author' => array(
                    'id' => 1,
                    'name' => 'Dave',
                ),
            ),
            array(
                'title' => 'Bar',
                'year' => '1997',
                'links' => array(
                    'author' => 2,
                ),
                '_author' => array(
                    'id' => 2,
                    'name' => 'Bob',
                ),
            ),
        );

        $resource = new Collection($booksData, new GenericBookTransformer(), 'book');
        $scope = new Scope($this->manager, $resource);

        $expected = array(
            'book' => array(
                array(
                    'title' => 'Foo',
                    'year' => 1991,
                    'links' => array(
                        'author' => 1,
                    ),
                ),
                array(
                    'title' => 'Bar',
                    'year' => 1997,
                    'links' => array(
                        'author' => 2,
                    ),
                ),
            ),
            'linked' => array(
                'author' => array(
                    array(
                        'id' => 1,
                        'name' => 'Dave',
                    ),
                    array(
                        'id' => 2,
                        'name' => 'Bob',
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"book":[{"title":"Foo","year":1991,"links":{"author":1}},{"title":"Bar","year":1997,"links":{"author":2}}],"linked":{"author":[{"id":1,"name":"Dave"},{"id":2,"name":"Bob"}]}}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

    public function testSerializingItemResourceWithMeta()
    {
        $this->manager->parseIncludes('author');

        $bookData = array(
            'title' => 'Foo',
            'year' => '1991',
            'links' => array(
                'author' => 1,
            ),
            '_author' => array(
                'id' => 1,
                'name' => 'Dave',
            ),
        );

        $resource = new Item($bookData, new GenericBookTransformer(), 'book');
        $resource->setMetaValue('foo', 'bar');

        $scope = new Scope($this->manager, $resource);

        $expected = array(
            'book' => array(
                array(
                    'title' => 'Foo',
                    'year' => 1991,
                    'links' => array(
                        'author' => 1,
                    ),
                ),
            ),
            'linked' => array(
                'author' => array(
                    array(
                        'id' => 1,
                        'name' => 'Dave',
                    ),
                ),
            ),
            'meta' => array(
                'foo' => 'bar',
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"book":[{"title":"Foo","year":1991,"links":{"author":1}}],"linked":{"author":[{"id":1,"name":"Dave"}]},"meta":{"foo":"bar"}}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

    public function testSerializingCollectionResourceWithMeta()
    {
        $this->manager->parseIncludes('author');

        $booksData = array(
            array(
                'title' => 'Foo',
                'year' => '1991',
                'links' => array(
                    'author' => 1,
                ),
                '_author' => array(
                    'id' => 1,
                    'name' => 'Dave',
                ),
            ),
            array(
                'title' => 'Bar',
                'year' => '1997',
                'links' => array(
                    'author' => 2,
                ),
                '_author' => array(
                    'id' => 2,
                    'name' => 'Bob',
                ),
            ),
        );

        $resource = new Collection($booksData, new GenericBookTransformer(), 'book');
        $resource->setMetaValue('foo', 'bar');

        $scope = new Scope($this->manager, $resource);

        $expected = array(
            'book' => array(
                array(
                    'title' => 'Foo',
                    'year' => 1991,
                    'links' => array(
                        'author' => 1,
                    ),
                ),
                array(
                    'title' => 'Bar',
                    'year' => 1997,
                    'links' => array(
                        'author' => 2,
                    ),
                ),
            ),
            'linked' => array(
                'author' => array(
                    array(
                        'id' => 1,
                        'name' => 'Dave',
                    ),
                    array(
                        'id' => 2,
                        'name' => 'Bob',
                    ),
                ),
            ),
            'meta' => array(
                'foo' => 'bar',
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"book":[{"title":"Foo","year":1991,"links":{"author":1}},{"title":"Bar","year":1997,"links":{"author":2}}],"linked":{"author":[{"id":1,"name":"Dave"},{"id":2,"name":"Bob"}]},"meta":{"foo":"bar"}}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

    public function testSerializingCollectionResourceWithDuplicatedIncludeData()
    {
        $this->manager->parseIncludes('author');

        $booksData = array(
            array(
                'title' => 'Foo',
                'year' => '1991',
                'links' => array(
                    'author' => 1,
                ),
                '_author' => array(
                    'id' => 1,
                    'name' => 'Dave',
                ),
            ),
            array(
                'title' => 'Bar',
                'year' => '1997',
                'links' => array(
                    'author' => 1,
                ),
                '_author' => array(
                    'id' => 1,
                    'name' => 'Dave',
                ),
            ),
        );

        $resource = new Collection($booksData, new GenericBookTransformer(), 'book');
        $scope = new Scope($this->manager, $resource);

        $expected = array(
            'book' => array(
                array(
                    'title' => 'Foo',
                    'year' => 1991,
                    'links' => array(
                        'author' => 1,
                    ),
                ),
                array(
                    'title' => 'Bar',
                    'year' => 1997,
                    'links' => array(
                        'author' => 1,
                    ),
                ),
            ),
            'linked' => array(
                'author' => array(
                    array(
                        'id' => 1,
                        'name' => 'Dave',
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"book":[{"title":"Foo","year":1991,"links":{"author":1}},{"title":"Bar","year":1997,"links":{"author":1}}],"linked":{"author":[{"id":1,"name":"Dave"}]}}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

    public function testSerializingCollectionResourceDifferentParentsWithDuplicatedIncludeData()
    {
        $this->manager->parseIncludes('author,reviews,reviews.author');

        $booksData = array(
            array(
                'title' => 'Foo',
                'year' => '1991',
                'links' => array(
                    'author' => 1,
                    'reviews' => array(1),
                ),
                '_author' => array(
                    'id' => 1,
                    'name' => 'Dave',
                ),
                '_reviews' => array(
                    array(
                        'id' => 1,
                        'comment' => 'Foo review',
                        'links' => array(
                            'author' => 2,
                        ),
                        '_author' => array(
                            'id' => 2,
                            'name' => 'Bob',
                        ),
                    ),
                ),
            ),
            array(
                'title' => 'Bar',
                'year' => '1997',
                'links' => array(
                    'author' => 1,
                    'reviews' => array(2),
                ),
                '_author' => array(
                    'id' => 1,
                    'name' => 'Dave',
                ),
                '_reviews' => array(
                    array(
                        'id' => 2,
                        'comment' => 'Bar review',
                        'links' => array(
                            'author' => 2,
                        ),
                        '_author' => array(
                            'id' => 2,
                            'name' => 'Bob',
                        ),
                    ),
                ),
            ),
        );

        $resource = new Collection($booksData, new GenericBookTransformer(), 'book');
        $scope = new Scope($this->manager, $resource);

        $expected = array(
            'book' => array(
                array(
                    'title' => 'Foo',
                    'year' => 1991,
                    'links' => array(
                        'author' => 1,
                        'reviews' => array(1),
                    ),
                ),
                array(
                    'title' => 'Bar',
                    'year' => 1997,
                    'links' => array(
                        'author' => 1,
                        'reviews' => array(2),
                    ),
                ),
            ),
            'linked' => array(
                'author' => array(
                    array(
                        'id' => 1,
                        'name' => 'Dave',
                    ),
                    array(
                        'id' => 2,
                        'name' => 'Bob',
                    ),
                ),
                'reviews' => array(
                    array(
                        'id' => 1,
                        'comment' => 'Foo review',
                        'links' => array(
                            'author' => 2,
                        ),
                    ),
                    array(
                        'id' => 2,
                        'comment' => 'Bar review',
                        'links' => array(
                            'author' => 2,
                        ),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"book":[{"title":"Foo","year":1991,"links":{"author":1,"reviews":[1]}},{"title":"Bar","year":1997,"links":{"author":1,"reviews":[2]}}],"linked":{"author":[{"id":1,"name":"Dave"},{"id":2,"name":"Bob"}],"reviews":[{"id":1,"comment":"Foo review","links":{"author":2}},{"id":2,"comment":"Bar review","links":{"author":2}}]}}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

    public function testResourceKeyMissing()
    {
        $this->manager->setSerializer(new JsonApiSerializer());

        $bookData = array(
            'title' => 'Foo',
            'year' => '1991',
        );

        $resource = new Item($bookData, new GenericBookTransformer());
        $scope = new Scope($this->manager, $resource);

        $expected = array(
            'data' => array(
                array(
                    'title' => 'Foo',
                    'year' => 1991,
                ),
            ),
        );

        $this->assertEquals($expected, $scope->toArray());
    }

    public function tearDown()
    {
        Mockery::close();
    }
}
