<?php

use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Scope;
use League\Fractal\Serializer\EmberSerializer;
use League\Fractal\Test\Stub\Transformer\GenericBookTransformer;

class EmberSerializerTest extends PHPUnit_Framework_TestCase
{
    private $manager;

    /**
     * Create a Manager and make it use the EmberSerializer
     */
    public function setUp()
    {
        $this->manager = new Manager();
        $this->manager->setSerializer(new EmberSerializer());
    }

    /**
     * One book
     */
    public function testSerializingItemResource()
    {
        $this->manager->parseIncludes('author');

        $bookData = array(
            'title' => 'Foo',
            'year' => '1991',
        );

        $resource = new Item($bookData, new GenericBookTransformer(), 'books');

        $scope = new Scope($this->manager, $resource);

        $expected = array(
            'books' => array(
                array(
                    'title' => 'Foo',
                    'year' => 1991,
                ),
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"books":[{"title":"Foo","year":1991}]}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

    /**
     * One book with included author
     */
    public function testSerializingItemResourceWithSingleInclude()
    {
        $this->manager->parseIncludes('author');

        $bookData = array(
            'title' => 'Foo',
            'year' => '1991',
            '_author' => array(
                'name' => 'Dave',
            ),
        );

        $resource = new Item($bookData, new GenericBookTransformer(), 'books');

        $scope = new Scope($this->manager, $resource);

        $expected = array(
            'books' => array(
                array(
                    'title' => 'Foo',
                    'year' => 1991,
                ),
            ),
            'author' => array(
                array(
                    'name' => 'Dave',
                ),
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"books":[{"title":"Foo","year":1991}],"author":[{"name":"Dave"}]}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

    /**
     * Two books
     */
    public function testSerializingCollectionResource()
    {
        $this->manager->parseIncludes('author');

        $booksData = array(
            array(
                'title' => 'Foo',
                'year' => '1991',
            ),
            array(
                'title' => 'Bar',
                'year' => '1997',
            ),
        );

        $resource = new Collection($booksData, new GenericBookTransformer(), 'books');
        $scope = new Scope($this->manager, $resource);

        $expected = array(
            'books' => array(
                array(
                    'title' => 'Foo',
                    'year' => 1991,
                ),
                array(
                    'title' => 'Bar',
                    'year' => 1997,
                ),
            )
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"books":[{"title":"Foo","year":1991},{"title":"Bar","year":1997}]}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

    /**
     * Two books with different authors
     */
    public function testSerializingCollectionResourceWithIncludes()
    {
        $this->manager->parseIncludes('author');

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

        $resource = new Collection($booksData, new GenericBookTransformer(), 'books');
        $scope = new Scope($this->manager, $resource);

        $expected = array(
            'books' => array(
                array(
                    'title' => 'Foo',
                    'year' => 1991,
                ),
                array(
                    'title' => 'Bar',
                    'year' => 1997,
                ),
            ),
            'author' => array(
                array('name' => 'Dave'),
                array('name' => 'Bob'),
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"books":[{"title":"Foo","year":1991},{"title":"Bar","year":1997}],"author":[{"name":"Dave"},{"name":"Bob"}]}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

    /**
     * Two books with a shared author
     */
    public function testSerializingCollectionResourceWithSharedIncludeData()
    {
        $this->manager->parseIncludes('author');

        $booksData = array(
            array(
                'title' => 'Foo',
                'year' => '1991',
                '_author' => array(
                    'id' => 1,
                    'name' => 'Dave',
                ),
            ),
            array(
                'title' => 'Bar',
                'year' => '1997',
                '_author' => array(
                    'id' => 1,
                    'name' => 'Dave',
                ),
            ),
        );

        $resource = new Collection($booksData, new GenericBookTransformer(), 'books');
        $scope = new Scope($this->manager, $resource);

        $expected = array(
            'books' => array(
                array(
                    'title' => 'Foo',
                    'year' => 1991,
                ),
                array(
                    'title' => 'Bar',
                    'year' => 1997,
                ),
            ),
            'author' => array(
                array(
                    'id' => 1,
                    'name' => 'Dave',
                ),
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"books":[{"title":"Foo","year":1991},{"title":"Bar","year":1997}],"author":[{"id":1,"name":"Dave"}]}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

    /**
     * Item with meta data on the response
     */
    public function testSerializingItemResourceWithMeta()
    {
        $this->manager->parseIncludes('author');

        $bookData = array(
            'title' => 'Foo',
            'year' => '1991',
            '_author' => array(
                'name' => 'Dave',
            ),
        );

        $resource = new Item($bookData, new GenericBookTransformer(), 'books');
        $resource->setMetaValue('foo', 'bar');

        $scope = new Scope($this->manager, $resource);

        $expected = array(
            'books' => array(
                array(
                    'title' => 'Foo',
                    'year' => 1991,
                ),
            ),
            'author' => array(
                array(
                    'name' => 'Dave',
                ),
            ),
            'meta' => array(
                'foo' => 'bar',
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"books":[{"title":"Foo","year":1991}],"author":[{"name":"Dave"}],"meta":{"foo":"bar"}}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

    /**
     * Collection with meta data on the response
     */
    public function testSerializingCollectionResourceWithMeta()
    {
        $this->manager->parseIncludes('author');

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

        $resource = new Collection($booksData, new GenericBookTransformer(), 'books');
        $resource->setMetaValue('foo', 'bar');

        $scope = new Scope($this->manager, $resource);

        $expected = array(
            'books' => array(
                array(
                    'title' => 'Foo',
                    'year' => 1991,
                ),
                array(
                    'title' => 'Bar',
                    'year' => 1997,
                ),
            ),
            'author' => array(
                array('name' => 'Dave'),
                array('name' => 'Bob'),
            ),
            'meta' => array(
                'foo' => 'bar',
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"books":[{"title":"Foo","year":1991},{"title":"Bar","year":1997}],"author":[{"name":"Dave"},{"name":"Bob"}],"meta":{"foo":"bar"}}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

    /**
     * Not that we're mocking
     */
    public function tearDown()
    {
        Mockery::close();
    }
}
