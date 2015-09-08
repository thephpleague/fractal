<?php

use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Scope;
use League\Fractal\Serializer\ArraySerializer;
use League\Fractal\Test\Stub\Transformer\GenericBookTransformer;

class ArraySerializerTest extends PHPUnit_Framework_TestCase
{
    private $bookItemInput = [
        'title' => 'Foo',
        'year' => '1991',
        '_author' => [
            'name' => 'Dave',
        ],
    ];

    private $bookCollectionInput = [
        [
            'title' => 'Foo',
            'year' => '1991',
            '_author' => [
                'name' => 'Dave',
            ],
        ],
        [
            'title' => 'Bar',
            'year' => '1997',
            '_author' => [
                'name' => 'Bob',
            ],
        ],
    ];

    public function testSerializingItemResource()
    {
        $manager = new Manager();
        $manager->parseIncludes('author');
        $manager->setSerializer(new ArraySerializer());

        $resource = new Item($this->bookItemInput, new GenericBookTransformer(), 'book');

        // Try without metadata
        $scope = new Scope($manager, $resource);

        $expected = [
            'title' => 'Foo',
            'year' => 1991,
            'author' => [
                'name' => 'Dave',
            ],
        ];

        $this->assertSame($expected, $scope->toArray());

        // Same again with meta
        $resource->setMetaValue('foo', 'bar');

        $scope = new Scope($manager, $resource);

        $expected = [
            'title' => 'Foo',
            'year' => 1991,
            'author' => [
                'name' => 'Dave',
            ],
            'meta' => [
                'foo' => 'bar',
            ],
        ];

        $this->assertSame($expected, $scope->toArray());
    }

    public function testSerializingCollectionResource()
    {
        $manager = new Manager();
        $manager->parseIncludes('author');
        $manager->setSerializer(new ArraySerializer());

        $resource = new Collection($this->bookCollectionInput, new GenericBookTransformer(), 'books');

        // Try without metadata
        $scope = new Scope($manager, $resource);

        $expected = [
            'books' => [
                [
                    'title' => 'Foo',
                    'year' => 1991,
                    'author' => [
                        'name' => 'Dave',
                    ],
                ],
                [
                    'title' => 'Bar',
                    'year' => 1997,
                    'author' => [
                        'name' => 'Bob',
                    ],
                ],
            ],
        ];

        $this->assertSame($expected, $scope->toArray());

        // JSON array of JSON objects
        $expectedJson = '{"books":[{"title":"Foo","year":1991,"author":{"name":"Dave"}},{"title":"Bar","year":1997,"author":{"name":"Bob"}}]}';
        $this->assertSame($expectedJson, $scope->toJson());

        // Same again with metadata
        $resource->setMetaValue('foo', 'bar');

        $scope = new Scope($manager, $resource);

        $expected = [
            'books' => [
                [
                    'title' => 'Foo',
                    'year' => 1991,
                    'author' => [
                        'name' => 'Dave',
                    ],
                ],
                [
                    'title' => 'Bar',
                    'year' => 1997,
                    'author' => [
                        'name' => 'Bob',
                    ],
                ],
            ],
            'meta' => [
                'foo' => 'bar',
            ],
        ];

        $this->assertSame($expected, $scope->toArray());

        $expectedJson = '{"books":[{"title":"Foo","year":1991,"author":{"name":"Dave"}},{"title":"Bar","year":1997,"author":{"name":"Bob"}}],"meta":{"foo":"bar"}}';
        $this->assertSame($expectedJson, $scope->toJson());
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
        $this->assertSame($expectedJson, $scope->toJson());

        // Same again with metadata
        $resource->setMetaValue('foo', 'bar');

        $scope = new Scope($manager, $resource);

        $expectedJson = '{"data":[{"title":"Foo","year":1991,"author":{"name":"Dave"}},{"title":"Bar","year":1997,"author":{"name":"Bob"}}],"meta":{"foo":"bar"}}';
        $this->assertSame($expectedJson, $scope->toJson());
    }

    public function tearDown()
    {
        Mockery::close();
    }
}
