<?php

use League\Fractal\Serializer\JsonApiSerializer;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Manager;
use League\Fractal\Scope;
use League\Fractal\Test\Stub\Transformer\GenericBookTransformer;

class JsonApiSerializerTest extends PHPUnit_Framework_TestCase {

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
            '_author' => array(
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
                )
            ),
            'linked' => array(
                'author' => array(
                    array(
                        'name' => 'Dave'
                    )
                )
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"book":[{"title":"Foo","year":1991}],"linked":{"author":[{"name":"Dave"}]}}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

    public function testSerializingItemResource()
    {
        $this->manager->parseIncludes('author');

        $bookData = array(
            'title' => 'Foo',
            'year' => '1991',
            '_author' => array(
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
                )
            ),
            'linked' => array(
                'author' => array(
                    array(
                        'name' => 'Dave'
                    )
                )
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"book":[{"title":"Foo","year":1991}],"linked":{"author":[{"name":"Dave"}]}}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

    public function testSerializingCollectionResource()
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

        $resource = new Collection($booksData, new GenericBookTransformer(), 'book');
        $scope = new Scope($this->manager, $resource);

        $expected = array(
            'book' => array(
                array(
                    'title' => 'Foo',
                    'year' => 1991,
                ),
                array(
                    'title' => 'Bar',
                    'year' => 1997,
                ),
            ),
            'linked' => array(
                'author' => array(
                    array('name' => 'Dave'),
                    array('name' => 'Bob'),
                ),
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"book":[{"title":"Foo","year":1991},{"title":"Bar","year":1997}],"linked":{"author":[{"name":"Dave"},{"name":"Bob"}]}}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }


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

        $resource = new Item($bookData, new GenericBookTransformer(), 'book');
        $resource->setMetaValue('foo', 'bar');

        $scope = new Scope($this->manager, $resource);

        $expected = array(
            'book' => array(
                array(
                    'title' => 'Foo',
                    'year' => 1991,
                )
            ),
            'linked' => array(
                'author' => array(
                    array(
                        'name' => 'Dave'
                    )
                )
            ),
            'meta' => array(
                'foo' => 'bar',
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"book":[{"title":"Foo","year":1991}],"linked":{"author":[{"name":"Dave"}]},"meta":{"foo":"bar"}}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

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

        $resource = new Collection($booksData, new GenericBookTransformer(), 'book');
        $resource->setMetaValue('foo', 'bar');

        $scope = new Scope($this->manager, $resource);

        $expected = array(
            'book' => array(
                array(
                    'title' => 'Foo',
                    'year' => 1991,
                ),
                array(
                    'title' => 'Bar',
                    'year' => 1997,
                ),
            ),
            'linked' => array(
                'author' => array(
                    array('name' => 'Dave'),
                    array('name' => 'Bob'),
                ),
            ),
            'meta' => array(
                'foo' => 'bar',
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"book":[{"title":"Foo","year":1991},{"title":"Bar","year":1997}],"linked":{"author":[{"name":"Dave"},{"name":"Bob"}]},"meta":{"foo":"bar"}}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage The $resourceKey parameter must be provided when using League\Fractal\Serializer\JsonApiSerializer
     **/
    public function testResourceKeyMissing()
    {
        $this->manager->setSerializer(new JsonApiSerializer());

        $bookData = array(
            'title' => 'Foo',
            'year' => '1991',
        );

        $resource = new Item($bookData, new GenericBookTransformer());
        $scope = new Scope($this->manager, $resource);

        $scope->toArray();
    }



    public function tearDown()
    {
        Mockery::close();
    }

}
