<?php namespace League\Fractal\Test\Serializer;

use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Scope;
use League\Fractal\Serializer\JsonApiSerializer;
use League\Fractal\Test\Stub\Transformer\JsonApiAuthorTransformer;
use League\Fractal\Test\Stub\Transformer\JsonApiBookTransformer;
use League\Fractal\Test\Stub\Transformer\JsonApiEmptyTransformer;
use Mockery;
use PHPUnit\Framework\TestCase;

class JsonApiSerializerTest extends TestCase
{
    private $manager;

    public function setUp()
    {
        $this->manager = new Manager();
        $this->manager->setSerializer(new JsonApiSerializer());
    }

    public function testSerializeCollectionWithExtraMeta()
    {
        $booksData = [
            [
                'id' => 1,
                'title' => 'Foo',
                'year' => '1991',
                '_author' => [
                    'id' => 1,
                    'name' => 'Dave',
                ],
                'meta' => [
                    'foo' => 'bar'
                ]
            ],
            [
                'id' => 2,
                'title' => 'Bar',
                'year' => '1997',
                '_author' => [
                    'id' => 2,
                    'name' => 'Bob',
                ],
                'meta' => [
                    'bar' => 'baz'
                ]
            ],
        ];

        $resource = new Collection($booksData, new JsonApiBookTransformer(), 'books');
        $scope = new Scope($this->manager, $resource);

        $expected = [
            'data' => [
                [
                    'type' => 'books',
                    'id' => '1',
                    'attributes' => [
                        'title' => 'Foo',
                        'year' => 1991,
                    ],
                    'meta' => [
                        'foo' => 'bar'
                    ]
                ],
                [
                    'type' => 'books',
                    'id' => '2',
                    'attributes' => [
                        'title' => 'Bar',
                        'year' => 1997,
                    ],
                    'meta' => [
                        'bar' => 'baz'
                    ]
                ],
            ],
        ];

        $this->assertSame($expected, $scope->toArray());

        $expectedJson = '{"data":[{"type":"books","id":"1","attributes":{"title":"Foo","year":1991},"meta":{"foo":"bar"}},{"type":"books","id":"2","attributes":{"title":"Bar","year":1997},"meta":{"bar":"baz"}}]}';
        $this->assertSame($expectedJson, $scope->toJson());
    }

    public function testSerializingItemResourceWithHasOneInclude()
    {
        $this->manager->parseIncludes('author');

        $bookData = [
            'id' => 1,
            'title' => 'Foo',
            'year' => '1991',
            '_author' => [
                'id' => 1,
                'name' => 'Dave',
            ],
        ];

        $resource = new Item($bookData, new JsonApiBookTransformer(), 'books');

        $scope = new Scope($this->manager, $resource);

        $expected = [
            'data' => [
                'type' => 'books',
                'id' => '1',
                'attributes' => [
                    'title' => 'Foo',
                    'year' => 1991,
                ],
                'relationships' => [
                    'author' => [
                        'data' => [
                            'type' => 'people',
                            'id' => '1',
                        ],
                    ],
                ],
            ],
            'included' => [
                [
                    'type' => 'people',
                    'id' => '1',
                    'attributes' => [
                        'name' => 'Dave',
                    ],
                ],
            ],
        ];

        $this->assertSame($expected, $scope->toArray());

        $expectedJson = '{"data":{"type":"books","id":"1","attributes":{"title":"Foo","year":1991},"relationships":{"author":{"data":{"type":"people","id":"1"}}}},"included":[{"type":"people","id":"1","attributes":{"name":"Dave"}}]}';
        $this->assertSame($expectedJson, $scope->toJson());
    }

    public function testSerializingItemResourceWithMetaOnRelationship()
    {
        $this->manager->parseIncludes('author-with-meta');

        $bookData = [
            'id' => 1,
            'title' => 'Foo',
            'year' => '1991',
            '_author' => [
                'id' => 1,
                'name' => 'Dave',
            ],
        ];

        $resource = new Item($bookData, new JsonApiBookTransformer(), 'books');

        $scope = new Scope($this->manager, $resource);

        $expected = [
            'data' => [
                'type' => 'books',
                'id' => '1',
                'attributes' => [
                    'title' => 'Foo',
                    'year' => 1991,
                ],
                'relationships' => [
                    'author-with-meta' => [
                        'data' => [
                            'type' => 'people',
                            'id' => '1',
                        ],
                        'meta' => [ 'foo' => 'bar' ],
                    ],
                ],
            ],
            'included' => [
                [
                    'type' => 'people',
                    'id' => '1',
                    'attributes' => [
                        'name' => 'Dave',
                    ],
                ],
            ],
        ];

        $this->assertSame($expected, $scope->toArray());

        $expectedJson = '{"data":{"type":"books","id":"1","attributes":{"title":"Foo","year":1991},"relationships":{"author-with-meta":{"data":{"type":"people","id":"1"},"meta":{"foo":"bar"}}}},"included":[{"type":"people","id":"1","attributes":{"name":"Dave"}}]}';
        $this->assertSame($expectedJson, $scope->toJson());
    }

    public function testSerializingItemResourceWithHasOneDasherizedInclude()
    {
        $this->manager->parseIncludes('co-author');

        $bookData = [
            'id' => 1,
            'title' => 'Foo',
            'year' => '1991',
            '_author' => [
                'id' => 1,
                'name' => 'Dave',
            ],
            '_co_author' => [
                'id' => 2,
                'name' => 'Jim',
            ],
        ];

        $resource = new Item($bookData, new JsonApiBookTransformer(), 'books');

        $scope = new Scope($this->manager, $resource);

        $expected = [
            'data' => [
                'type' => 'books',
                'id' => '1',
                'attributes' => [
                    'title' => 'Foo',
                    'year' => 1991,
                ],
                'relationships' => [
                    'co-author' => [
                        'data' => [
                            'type' => 'people',
                            'id' => '2',
                        ],
                    ],
                ],
            ],
            'included' => [
                [
                    'type' => 'people',
                    'id' => '2',
                    'attributes' => [
                        'name' => 'Jim',
                    ],
                ],
            ],
        ];

        $this->assertSame($expected, $scope->toArray());

        $expectedJson = '{"data":{"type":"books","id":"1","attributes":{"title":"Foo","year":1991},"relationships":{"co-author":{"data":{"type":"people","id":"2"}}}},"included":[{"type":"people","id":"2","attributes":{"name":"Jim"}}]}';
        $this->assertSame($expectedJson, $scope->toJson());
    }

    public function testSerializingItemResourceWithEmptyHasOneInclude()
    {
        $this->manager->parseIncludes('author');

        $bookData = [
            'id' => 1,
            'title' => 'Foo',
            'year' => '1991',
            '_author' => null,
        ];

        $resource = new Item($bookData, new JsonApiBookTransformer(), 'books');

        $scope = new Scope($this->manager, $resource);

        $expected = [
            'data' => [
                'type' => 'books',
                'id' => '1',
                'attributes' => [
                    'title' => 'Foo',
                    'year' => 1991,
                ],
                'relationships' => [
                    'author' => [
                        'data' => null,
                    ],
                ],
            ],
        ];

        $this->assertSame($expected, $scope->toArray());

        $expectedJson = '{"data":{"type":"books","id":"1","attributes":{"title":"Foo","year":1991},"relationships":{"author":{"data":null}}}}';
        $this->assertSame($expectedJson, $scope->toJson());
    }

    public function testSerializingItemResourceWithHasManyInclude()
    {
        $this->manager->parseIncludes('published');

        $authorData = [
            'id' => 1,
            'name' => 'Dave',
            '_published' => [
                [
                    'id' => 1,
                    'title' => 'Foo',
                    'year' => '1991',
                ],
                [
                    'id' => 2,
                    'title' => 'Bar',
                    'year' => '2015',
                ],
            ],
        ];

        $resource = new Item($authorData, new JsonApiAuthorTransformer(), 'people');

        $scope = new Scope($this->manager, $resource);

        $expected = [
            'data' => [
                'type' => 'people',
                'id' => '1',
                'attributes' => [
                    'name' => 'Dave',
                ],
                'relationships' => [
                    'published' => [
                        'data' => [
                            [
                                'type' => 'books',
                                'id' => 1,
                            ],
                            [
                                'type' => 'books',
                                'id' => 2,
                            ],
                        ],
                    ],
                ],
            ],
            'included' => [
                [
                    'type' => 'books',
                    'id' => '1',
                    'attributes' => [
                        'title' => 'Foo',
                        'year' => 1991,
                    ],
                ],
                [
                    'type' => 'books',
                    'id' => '2',
                    'attributes' => [
                        'title' => 'Bar',
                        'year' => 2015,
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"data":{"type":"people","id":"1","attributes":{"name":"Dave"},"relationships":{"published":{"data":[{"type":"books","id":"1"},{"type":"books","id":"2"}]}}},"included":[{"type":"books","id":"1","attributes":{"title":"Foo","year":1991}},{"type":"books","id":"2","attributes":{"title":"Bar","year":2015}}]}';
        $this->assertSame($expectedJson, $scope->toJson());
    }

    public function testSerializingItemResourceWithEmptyHasManyInclude()
    {
        $this->manager->parseIncludes('published');

        $authorData = [
            'id' => 1,
            'name' => 'Dave',
            '_published' => [],
        ];

        $resource = new Item($authorData, new JsonApiAuthorTransformer(), 'people');

        $scope = new Scope($this->manager, $resource);

        $expected = [
            'data' => [
                'type' => 'people',
                'id' => '1',
                'attributes' => [
                    'name' => 'Dave',
                ],
                'relationships' => [
                    'published' => [
                        'data' => [],
                    ],
                ],
            ],
        ];

        $this->assertSame($expected, $scope->toArray());

        $expectedJson = '{"data":{"type":"people","id":"1","attributes":{"name":"Dave"},"relationships":{"published":{"data":[]}}}}';
        $this->assertSame($expectedJson, $scope->toJson());
    }

    public function testSerializingItemResourceWithoutIncludes()
    {
        $bookData = [
            'id' => 1,
            'title' => 'Foo',
            'year' => '1991',
            '_author' => [
                'id' => 1,
                'name' => 'Dave',
            ],
        ];

        $resource = new Item($bookData, new JsonApiBookTransformer(), 'books');

        $scope = new Scope($this->manager, $resource);

        $expected = [
            'data' => [
                'type' => 'books',
                'id' => '1',
                'attributes' => [
                    'title' => 'Foo',
                    'year' => 1991,
                ],
            ],
        ];

        $this->assertSame($expected, $scope->toArray());

        $expectedJson = '{"data":{"type":"books","id":"1","attributes":{"title":"Foo","year":1991}}}';
        $this->assertSame($expectedJson, $scope->toJson());
    }

    public function testSerializingItemResourceWithMeta()
    {
        $bookData = [
            'id' => 1,
            'title' => 'Foo',
            'year' => '1991',
            '_author' => [
                'id' => 1,
                'name' => 'Dave',
            ],
        ];

        $resource = new Item($bookData, new JsonApiBookTransformer(), 'books');
        $resource->setMetaValue('foo', 'bar');

        $scope = new Scope($this->manager, $resource);

        $expected = [
            'data' => [
                'type' => 'books',
                'id' => '1',
                'attributes' => [
                    'title' => 'Foo',
                    'year' => 1991,
                ],
            ],
            'meta' => [
                'foo' => 'bar',
            ],
        ];

        $this->assertSame($expected, $scope->toArray());

        $expectedJson = '{"data":{"type":"books","id":"1","attributes":{"title":"Foo","year":1991}},"meta":{"foo":"bar"}}';
        $this->assertSame($expectedJson, $scope->toJson());
    }

    public function testSerializingItemResourceWithMetaInBody()
    {
        $bookData = [
            'id' => 1,
            'title' => 'Foo',
            'year' => '1991',
            '_author' => [
                'id' => 1,
                'name' => 'Dave',
            ],
            'meta' => [
                'something' => 'something'
            ]
        ];

        $resource = new Item($bookData, new JsonApiBookTransformer(), 'books');
        $resource->setMetaValue('foo', 'bar');

        $scope = new Scope($this->manager, $resource);

        $expected = [
            'data' => [
                'type' => 'books',
                'id' => '1',
                'attributes' => [
                    'title' => 'Foo',
                    'year' => 1991,
                ],
                'meta' => [
                    'something' => 'something'
                ]
            ],
            'meta' => [
                'foo' => 'bar'
            ],
        ];

        $this->assertSame($expected, $scope->toArray());

        $expectedJson = '{"data":{"type":"books","id":"1","attributes":{"title":"Foo","year":1991},"meta":{"something":"something"}},"meta":{"foo":"bar"}}';
        $this->assertSame($expectedJson, $scope->toJson());
    }

    public function testSerializingCollectionResourceWithoutIncludes()
    {
        $booksData = [
            [
                'id' => 1,
                'title' => 'Foo',
                'year' => '1991',
                '_author' => [
                    'id' => 1,
                    'name' => 'Dave',
                ],
            ],
            [
                'id' => 2,
                'title' => 'Bar',
                'year' => '1997',
                '_author' => [
                    'id' => 2,
                    'name' => 'Bob',
                ],
            ],
        ];

        $resource = new Collection($booksData, new JsonApiBookTransformer(), 'books');
        $scope = new Scope($this->manager, $resource);

        $expected = [
            'data' => [
                [
                    'type' => 'books',
                    'id' => '1',
                    'attributes' => [
                        'title' => 'Foo',
                        'year' => 1991,
                    ],
                ],
                [
                    'type' => 'books',
                    'id' => '2',
                    'attributes' => [
                        'title' => 'Bar',
                        'year' => 1997,
                    ],
                ],
            ],
        ];

        $this->assertSame($expected, $scope->toArray());

        $expectedJson = '{"data":[{"type":"books","id":"1","attributes":{"title":"Foo","year":1991}},{"type":"books","id":"2","attributes":{"title":"Bar","year":1997}}]}';
        $this->assertSame($expectedJson, $scope->toJson());
    }

    public function testSerializingCollectionResourceWithHasOneInclude()
    {
        $this->manager->parseIncludes('author');

        $booksData = [
            [
                'id' => 1,
                'title' => 'Foo',
                'year' => '1991',
                '_author' => [
                    'id' => 1,
                    'name' => 'Dave',
                ],
            ],
            [
                'id' => 2,
                'title' => 'Bar',
                'year' => '1997',
                '_author' => [
                    'id' => 2,
                    'name' => 'Bob',
                ],
            ],
        ];

        $resource = new Collection($booksData, new JsonApiBookTransformer(), 'books');
        $scope = new Scope($this->manager, $resource);

        $expected = [
            'data' => [
                [
                    'type' => 'books',
                    'id' => '1',
                    'attributes' => [
                        'title' => 'Foo',
                        'year' => 1991,
                    ],
                    'relationships' => [
                        'author' => [
                            'data' => [
                                'type' => 'people',
                                'id' => '1',
                            ],
                        ],
                    ],
                ],
                [
                    'type' => 'books',
                    'id' => '2',
                    'attributes' => [
                        'title' => 'Bar',
                        'year' => 1997,
                    ],
                    'relationships' => [
                        'author' => [
                            'data' => [
                                'type' => 'people',
                                'id' => '2',
                            ],
                        ],
                    ],
                ],
            ],
            'included' => [
                [
                    'type' => 'people',
                    'id' => '1',
                    'attributes' => [
                        'name' => 'Dave',
                    ],
                ],
                [
                    'type' => 'people',
                    'id' => '2',
                    'attributes' => [
                        'name' => 'Bob',
                    ],
                ],
            ],
        ];

        $this->assertSame($expected, $scope->toArray());

        $expectedJson = '{"data":[{"type":"books","id":"1","attributes":{"title":"Foo","year":1991},"relationships":{"author":{"data":{"type":"people","id":"1"}}}},{"type":"books","id":"2","attributes":{"title":"Bar","year":1997},"relationships":{"author":{"data":{"type":"people","id":"2"}}}}],"included":[{"type":"people","id":"1","attributes":{"name":"Dave"}},{"type":"people","id":"2","attributes":{"name":"Bob"}}]}';
        $this->assertSame($expectedJson, $scope->toJson());
    }

    public function testSerializingCollectionResourceWithEmptyHasOneInclude()
    {
        $this->manager->parseIncludes('author');

        $booksData = [
            [
                'id' => 1,
                'title' => 'Foo',
                'year' => '1991',
                '_author' => null,
            ],
            [
                'id' => 2,
                'title' => 'Bar',
                'year' => '1997',
                '_author' => [
                    'id' => 2,
                    'name' => 'Bob',
                ],
            ],
        ];

        $resource = new Collection($booksData, new JsonApiBookTransformer(), 'books');
        $scope = new Scope($this->manager, $resource);

        $expected = [
            'data' => [
                [
                    'type' => 'books',
                    'id' => '1',
                    'attributes' => [
                        'title' => 'Foo',
                        'year' => 1991,
                    ],
                    'relationships' => [
                        'author' => [
                            'data' => null,
                        ],
                    ],
                ],
                [
                    'type' => 'books',
                    'id' => '2',
                    'attributes' => [
                        'title' => 'Bar',
                        'year' => 1997,
                    ],
                    'relationships' => [
                        'author' => [
                            'data' => [
                                'type' => 'people',
                                'id' => '2',
                            ],
                        ],
                    ],
                ],
            ],
            'included' => [
                [
                    'type' => 'people',
                    'id' => '2',
                    'attributes' => [
                        'name' => 'Bob',
                    ],
                ],
            ],
        ];

        $this->assertSame($expected, $scope->toArray());

        $expectedJson = '{"data":[{"type":"books","id":"1","attributes":{"title":"Foo","year":1991},"relationships":{"author":{"data":null}}},{"type":"books","id":"2","attributes":{"title":"Bar","year":1997},"relationships":{"author":{"data":{"type":"people","id":"2"}}}}],"included":[{"type":"people","id":"2","attributes":{"name":"Bob"}}]}';
        $this->assertSame($expectedJson, $scope->toJson());
    }

    public function testSerializingCollectionResourceWithHasManyInclude()
    {
        $this->manager->parseIncludes('published');

        $authorsData = [
            [
                'id' => 1,
                'name' => 'Dave',
                '_published' => [
                    [
                        'id' => 1,
                        'title' => 'Foo',
                        'year' => '1991',
                    ],
                    [
                        'id' => 2,
                        'title' => 'Bar',
                        'year' => '2015',
                    ],
                ],
            ],
            [
                'id' => 2,
                'name' => 'Bob',
                '_published' => [
                    [
                        'id' => 3,
                        'title' => 'Baz',
                        'year' => '1995',
                    ],
                    [
                        'id' => 4,
                        'title' => 'Quux',
                        'year' => '2000',
                    ],
                ],
            ],
        ];

        $resource = new Collection($authorsData, new JsonApiAuthorTransformer(), 'people');
        $scope = new Scope($this->manager, $resource);

        $expected = [
            'data' => [
                [
                    'type' => 'people',
                    'id' => '1',
                    'attributes' => [
                        'name' => 'Dave',
                    ],
                    'relationships' => [
                        'published' => [
                            'data' => [
                                [
                                    'type' => 'books',
                                    'id' => 1,
                                ],
                                [
                                    'type' => 'books',
                                    'id' => 2,
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'type' => 'people',
                    'id' => '2',
                    'attributes' => [
                        'name' => 'Bob',
                    ],
                    'relationships' => [
                        'published' => [
                            'data' => [
                                [
                                    'type' => 'books',
                                    'id' => 3,
                                ],
                                [
                                    'type' => 'books',
                                    'id' => 4,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'included' => [
                [
                    'type' => 'books',
                    'id' => '1',
                    'attributes' => [
                        'title' => 'Foo',
                        'year' => 1991,
                    ],
                ],
                [
                    'type' => 'books',
                    'id' => '2',
                    'attributes' => [
                        'title' => 'Bar',
                        'year' => 2015,
                    ],
                ],
                [
                    'type' => 'books',
                    'id' => '3',
                    'attributes' => [
                        'title' => 'Baz',
                        'year' => 1995,
                    ],
                ],
                [
                    'type' => 'books',
                    'id' => '4',
                    'attributes' => [
                        'title' => 'Quux',
                        'year' => 2000,
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"data":[{"type":"people","id":"1","attributes":{"name":"Dave"},"relationships":{"published":{"data":[{"type":"books","id":"1"},{"type":"books","id":"2"}]}}},{"type":"people","id":"2","attributes":{"name":"Bob"},"relationships":{"published":{"data":[{"type":"books","id":"3"},{"type":"books","id":"4"}]}}}],"included":[{"type":"books","id":"1","attributes":{"title":"Foo","year":1991}},{"type":"books","id":"2","attributes":{"title":"Bar","year":2015}},{"type":"books","id":"3","attributes":{"title":"Baz","year":1995}},{"type":"books","id":"4","attributes":{"title":"Quux","year":2000}}]}';
        $this->assertSame($expectedJson, $scope->toJson());
    }

    public function testSerializingCollectionResourceWithEmptyHasManyInclude()
    {
        $this->manager->parseIncludes('published');

        $authorsData = [
            [
                'id' => 1,
                'name' => 'Dave',
                '_published' => [],
            ],
            [
                'id' => 2,
                'name' => 'Bob',
                '_published' => [
                    [
                        'id' => 3,
                        'title' => 'Baz',
                        'year' => '1995',
                    ],
                ],
            ],
        ];

        $resource = new Collection($authorsData, new JsonApiAuthorTransformer(), 'people');
        $scope = new Scope($this->manager, $resource);

        $expected = [
            'data' => [
                [
                    'type' => 'people',
                    'id' => '1',
                    'attributes' => [
                        'name' => 'Dave',
                    ],
                    'relationships' => [
                        'published' => [
                            'data' => [],
                        ],
                    ],
                ],
                [
                    'type' => 'people',
                    'id' => '2',
                    'attributes' => [
                        'name' => 'Bob',
                    ],
                    'relationships' => [
                        'published' => [
                            'data' => [
                                [
                                    'type' => 'books',
                                    'id' => 3,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'included' => [
                [
                    'type' => 'books',
                    'id' => '3',
                    'attributes' => [
                        'title' => 'Baz',
                        'year' => 1995,
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"data":[{"type":"people","id":"1","attributes":{"name":"Dave"},"relationships":{"published":{"data":[]}}},{"type":"people","id":"2","attributes":{"name":"Bob"},"relationships":{"published":{"data":[{"type":"books","id":"3"}]}}}],"included":[{"type":"books","id":"3","attributes":{"title":"Baz","year":1995}}]}';
        $this->assertSame($expectedJson, $scope->toJson());
    }

    public function testSerializingCollectionResourceWithMeta()
    {
        $booksData = [
            [
                'id' => 1,
                'title' => 'Foo',
                'year' => '1991',
                '_author' => [
                    'name' => 'Dave',
                ],
            ],
            [
                'id' => 2,
                'title' => 'Bar',
                'year' => '1997',
                '_author' => [
                    'name' => 'Bob',
                ],
            ],
        ];

        $resource = new Collection($booksData, new JsonApiBookTransformer(), 'books');
        $resource->setMetaValue('foo', 'bar');

        $scope = new Scope($this->manager, $resource);

        $expected = [
            'data' => [
                [
                    'type' => 'books',
                    'id' => '1',
                    'attributes' => [
                        'title' => 'Foo',
                        'year' => 1991,
                    ],
                ],
                [
                    'type' => 'books',
                    'id' => '2',
                    'attributes' => [
                        'title' => 'Bar',
                        'year' => 1997,
                    ],
                ],
            ],
            'meta' => [
                'foo' => 'bar',
            ],
        ];

        $this->assertSame($expected, $scope->toArray());

        $expectedJson = '{"data":[{"type":"books","id":"1","attributes":{"title":"Foo","year":1991}},{"type":"books","id":"2","attributes":{"title":"Bar","year":1997}}],"meta":{"foo":"bar"}}';
        $this->assertSame($expectedJson, $scope->toJson());
    }

    public function testSerializingCollectionResourceWithDuplicatedIncludeData()
    {
        $this->manager->parseIncludes('author');

        $booksData = [
            [
                'id' => 1,
                'title' => 'Foo',
                'year' => '1991',
                '_author' => [
                    'id' => 1,
                    'name' => 'Dave',
                ],
            ],
            [
                'id' => 2,
                'title' => 'Bar',
                'year' => '1997',
                '_author' => [
                    'id' => 1,
                    'name' => 'Dave',
                ],
            ],
        ];

        $resource = new Collection($booksData, new JsonApiBookTransformer(), 'books');
        $scope = new Scope($this->manager, $resource);

        $expected = [
            'data' => [
                [
                    'type' => 'books',
                    'id' => '1',
                    'attributes' => [
                        'title' => 'Foo',
                        'year' => 1991,
                    ],
                    'relationships' => [
                        'author' => [
                            'data' => [
                                'type' => 'people',
                                'id' => '1',
                            ],
                        ],
                    ],
                ],
                [
                    'type' => 'books',
                    'id' => '2',
                    'attributes' => [
                        'title' => 'Bar',
                        'year' => 1997,
                    ],
                    'relationships' => [
                        'author' => [
                            'data' => [
                                'type' => 'people',
                                'id' => '1',
                            ],
                        ],
                    ],
                ],
            ],
            'included' => [
                [
                    'type' => 'people',
                    'id' => '1',
                    'attributes' => [
                        'name' => 'Dave',
                    ],
                ],
            ],
        ];

        $this->assertSame($expected, $scope->toArray());

        $expectedJson = '{"data":[{"type":"books","id":"1","attributes":{"title":"Foo","year":1991},"relationships":{"author":{"data":{"type":"people","id":"1"}}}},{"type":"books","id":"2","attributes":{"title":"Bar","year":1997},"relationships":{"author":{"data":{"type":"people","id":"1"}}}}],"included":[{"type":"people","id":"1","attributes":{"name":"Dave"}}]}';
        $this->assertSame($expectedJson, $scope->toJson());
    }

    public function testSerializingItemResourceWithNestedIncludes()
    {
        $this->manager->parseIncludes(['author', 'author.published']);

        $bookData = [
            'id' => 1,
            'title' => 'Foo',
            'year' => '1991',
            '_author' => [
                'id' => 1,
                'name' => 'Dave',
                '_published' => [
                    [
                        'id' => 1,
                        'title' => 'Foo',
                        'year' => '1991',
                    ],
                    [
                        'id' => 2,
                        'title' => 'Bar',
                        'year' => '2015',
                    ],
                ],
            ],
        ];

        $resource = new Item($bookData, new JsonApiBookTransformer(), 'books');

        $scope = new Scope($this->manager, $resource);

        $expected = [
            'data' => [
                'type' => 'books',
                'id' => '1',
                'attributes' => [
                    'title' => 'Foo',
                    'year' => 1991,
                ],
                'relationships' => [
                    'author' => [
                        'data' => [
                            'type' => 'people',
                            'id' => '1',
                        ],
                    ],
                ],
            ],
            'included' => [
                [
                    'type' => 'books',
                    'id' => '2',
                    'attributes' => [
                        'title' => 'Bar',
                        'year' => 2015,
                    ],
                ],
                [
                    'type' => 'people',
                    'id' => '1',
                    'attributes' => [
                        'name' => 'Dave',
                    ],
                    'relationships' => [
                        'published' => [
                            'data' => [
                                [
                                    'type' => 'books',
                                    'id' => '1',
                                ],
                                [
                                    'type' => 'books',
                                    'id' => '2',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertSame($expected, $scope->toArray());

        $expectedJson = '{"data":{"type":"books","id":"1","attributes":{"title":"Foo","year":1991},"relationships":{"author":{"data":{"type":"people","id":"1"}}}},"included":[{"type":"books","id":"2","attributes":{"title":"Bar","year":2015}},{"type":"people","id":"1","attributes":{"name":"Dave"},"relationships":{"published":{"data":[{"type":"books","id":"1"},{"type":"books","id":"2"}]}}}]}';
        $this->assertSame($expectedJson, $scope->toJson());
    }

    public function testSerializingItemResourceWithSelfLink()
    {
        $baseUrl = 'http://example.com';
        $this->manager->setSerializer(new JsonApiSerializer($baseUrl));

        $bookData = [
            'id' => 1,
            'title' => 'Foo',
            'year' => '1991',
            '_author' => [
                'id' => 1,
                'name' => 'Dave',
            ],
        ];

        $resource = new Item($bookData, new JsonApiBookTransformer(), 'books');

        $scope = new Scope($this->manager, $resource);

        $expected = [
            'data' => [
                'type' => 'books',
                'id' => '1',
                'attributes' => [
                    'title' => 'Foo',
                    'year' => 1991,
                ],
                'links' => [
                    'self' => 'http://example.com/books/1',
                ],
                'relationships' => [
                    'author' => [
                        'links' => [
                            'self' => 'http://example.com/books/1/relationships/author',
                            'related' => 'http://example.com/books/1/author',
                        ],
                    ],
                    'co-author' => [
                        'links' => [
                            'self' => 'http://example.com/books/1/relationships/co-author',
                            'related' => 'http://example.com/books/1/co-author',
                        ],
                    ],
                    'author-with-meta' => [
                        'links' => [
                            'self' => 'http://example.com/books/1/relationships/author-with-meta',
                            'related' => 'http://example.com/books/1/author-with-meta',
                        ],
                    ],
                ],
            ],
        ];

        $this->assertSame($expected, $scope->toArray());

        $expectedJson = '{"data":{"type":"books","id":"1","attributes":{"title":"Foo","year":1991},"links":{"self":"http:\/\/example.com\/books\/1"},"relationships":{"author":{"links":{"self":"http:\/\/example.com\/books\/1\/relationships\/author","related":"http:\/\/example.com\/books\/1\/author"}},"co-author":{"links":{"self":"http:\/\/example.com\/books\/1\/relationships\/co-author","related":"http:\/\/example.com\/books\/1\/co-author"}},"author-with-meta":{"links":{"self":"http:\/\/example.com\/books\/1\/relationships\/author-with-meta","related":"http:\/\/example.com\/books\/1\/author-with-meta"}}}}}';
        $this->assertSame($expectedJson, $scope->toJson());
    }

    public function testSerializingCollectionResourceWithSelfLink()
    {
        $baseUrl = 'http://example.com';
        $this->manager->setSerializer(new JsonApiSerializer($baseUrl));

        $booksData = [
            [
                'id' => 1,
                'title' => 'Foo',
                'year' => '1991',
                '_author' => [
                    'id' => 1,
                    'name' => 'Dave',
                ],
            ],
            [
                'id' => 2,
                'title' => 'Bar',
                'year' => '1997',
                '_author' => [
                    'id' => 2,
                    'name' => 'Bob',
                ],
            ],
        ];

        $resource = new Collection($booksData, new JsonApiBookTransformer(), 'books');
        $scope = new Scope($this->manager, $resource);

        $expected = [
            'data' => [
                [
                    'type' => 'books',
                    'id' => '1',
                    'attributes' => [
                        'title' => 'Foo',
                        'year' => 1991,
                    ],
                    'links' => [
                        'self' => 'http://example.com/books/1',
                    ],
                    'relationships' => [
                        'author' => [
                            'links' => [
                                'self' => 'http://example.com/books/1/relationships/author',
                                'related' => 'http://example.com/books/1/author',
                            ],
                        ],
                        'co-author' => [
                            'links' => [
                                'self' => 'http://example.com/books/1/relationships/co-author',
                                'related' => 'http://example.com/books/1/co-author',
                            ],
                        ],
                        'author-with-meta' => [
                            'links' => [
                                'self' => 'http://example.com/books/1/relationships/author-with-meta',
                                'related' => 'http://example.com/books/1/author-with-meta',
                            ],
                        ],
                    ],
                ],
                [
                    'type' => 'books',
                    'id' => '2',
                    'attributes' => [
                        'title' => 'Bar',
                        'year' => 1997,
                    ],
                    'links' => [
                        'self' => 'http://example.com/books/2',
                    ],
                    'relationships' => [
                        'author' => [
                            'links' => [
                                'self' => 'http://example.com/books/2/relationships/author',
                                'related' => 'http://example.com/books/2/author'
                            ],
                        ],
                        'co-author' => [
                            'links' => [
                                'self' => 'http://example.com/books/2/relationships/co-author',
                                'related' => 'http://example.com/books/2/co-author'
                            ],
                        ],
                        'author-with-meta' => [
                            'links' => [
                                'self' => 'http://example.com/books/2/relationships/author-with-meta',
                                'related' => 'http://example.com/books/2/author-with-meta',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertSame($expected, $scope->toArray());

        $expectedJson = '{"data":[{"type":"books","id":"1","attributes":{"title":"Foo","year":1991},"links":{"self":"http:\/\/example.com\/books\/1"},"relationships":{"author":{"links":{"self":"http:\/\/example.com\/books\/1\/relationships\/author","related":"http:\/\/example.com\/books\/1\/author"}},"co-author":{"links":{"self":"http:\/\/example.com\/books\/1\/relationships\/co-author","related":"http:\/\/example.com\/books\/1\/co-author"}},"author-with-meta":{"links":{"self":"http:\/\/example.com\/books\/1\/relationships\/author-with-meta","related":"http:\/\/example.com\/books\/1\/author-with-meta"}}}},{"type":"books","id":"2","attributes":{"title":"Bar","year":1997},"links":{"self":"http:\/\/example.com\/books\/2"},"relationships":{"author":{"links":{"self":"http:\/\/example.com\/books\/2\/relationships\/author","related":"http:\/\/example.com\/books\/2\/author"}},"co-author":{"links":{"self":"http:\/\/example.com\/books\/2\/relationships\/co-author","related":"http:\/\/example.com\/books\/2\/co-author"}},"author-with-meta":{"links":{"self":"http:\/\/example.com\/books\/2\/relationships\/author-with-meta","related":"http:\/\/example.com\/books\/2\/author-with-meta"}}}}]}';
        $this->assertSame($expectedJson, $scope->toJson());
    }

    public function testSerializingItemResourceWithLinksForHasOneRelationship()
    {
        $baseUrl = 'http://example.com';
        $this->manager->setSerializer(new JsonApiSerializer($baseUrl));
        $this->manager->parseIncludes('author');

        $bookData = [
            'id' => 1,
            'title' => 'Foo',
            'year' => '1991',
            '_author' => [
                'id' => 1,
                'name' => 'Dave',
            ],
        ];

        $resource = new Item($bookData, new JsonApiBookTransformer(), 'books');

        $scope = new Scope($this->manager, $resource);

        $expected = [
            'data' => [
                'type' => 'books',
                'id' => '1',
                'attributes' => [
                    'title' => 'Foo',
                    'year' => 1991,
                ],
                'relationships' => [
                    'author' => [
                        'links' => [
                            'self' => 'http://example.com/books/1/relationships/author',
                            'related' => 'http://example.com/books/1/author',
                        ],
                        'data' => [
                            'type' => 'people',
                            'id' => '1',
                        ],
                    ],
                    'co-author' => [
                        'links' => [
                            'self' => 'http://example.com/books/1/relationships/co-author',
                            'related' => 'http://example.com/books/1/co-author'
                        ],
                    ],
                    'author-with-meta' => [
                        'links' => [
                            'self' => 'http://example.com/books/1/relationships/author-with-meta',
                            'related' => 'http://example.com/books/1/author-with-meta'
                        ],
                    ],
                ],
                'links' => [
                    'self' => 'http://example.com/books/1',
                ],
            ],
            'included' => [
                [
                    'type' => 'people',
                    'id' => '1',
                    'attributes' => [
                        'name' => 'Dave',
                    ],
                    'relationships' => [
                        'published' => [
                            'links' => [
                                'self' => 'http://example.com/people/1/relationships/published',
                                'related' => 'http://example.com/people/1/published',
                            ],
                        ],
                    ],
                    'links' => [
                        'self' => 'http://example.com/people/1',
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"data":{"type":"books","id":"1","attributes":{"title":"Foo","year":1991},"links":{"self":"http:\/\/example.com\/books\/1"},"relationships":{"author":{"links":{"self":"http:\/\/example.com\/books\/1\/relationships\/author","related":"http:\/\/example.com\/books\/1\/author"},"data":{"type":"people","id":"1"}},"co-author":{"links":{"self":"http:\/\/example.com\/books\/1\/relationships\/co-author","related":"http:\/\/example.com\/books\/1\/co-author"}},"author-with-meta":{"links":{"self":"http:\/\/example.com\/books\/1\/relationships\/author-with-meta","related":"http:\/\/example.com\/books\/1\/author-with-meta"}}}},"included":[{"type":"people","id":"1","attributes":{"name":"Dave"},"links":{"self":"http:\/\/example.com\/people\/1"},"relationships":{"published":{"links":{"self":"http:\/\/example.com\/people\/1\/relationships\/published","related":"http:\/\/example.com\/people\/1\/published"}}}}]}';
        $this->assertSame($expectedJson, $scope->toJson());
    }

    public function testSerializingItemResourceWithLinksForHasManyRelationship()
    {
        $baseUrl = 'http://example.com';
        $this->manager->setSerializer(new JsonApiSerializer($baseUrl));
        $this->manager->parseIncludes('published');

        $authorData = [
            'id' => 1,
            'name' => 'Dave',
            '_published' => [
                [
                    'id' => 1,
                    'title' => 'Foo',
                    'year' => '1991',
                ],
                [
                    'id' => 2,
                    'title' => 'Bar',
                    'year' => '2015',
                ],
            ],
        ];

        $resource = new Item($authorData, new JsonApiAuthorTransformer(), 'people');

        $scope = new Scope($this->manager, $resource);

        $expected = [
            'data' => [
                'type' => 'people',
                'id' => '1',
                'attributes' => [
                    'name' => 'Dave',
                ],
                'relationships' => [
                    'published' => [
                        'links' => [
                            'self' => 'http://example.com/people/1/relationships/published',
                            'related' => 'http://example.com/people/1/published',
                        ],
                        'data' => [
                            [
                                'type' => 'books',
                                'id' => 1,
                            ],
                            [
                                'type' => 'books',
                                'id' => 2,
                            ],
                        ],
                    ],
                ],
                'links' => [
                    'self' => 'http://example.com/people/1',
                ],
            ],
            'included' => [
                [
                    'type' => 'books',
                    'id' => '1',
                    'attributes' => [
                        'title' => 'Foo',
                        'year' => 1991,
                    ],
                    'links' => [
                        'self' => 'http://example.com/books/1',
                    ],
                    'relationships' => [
                        'author' => [
                            'links' => [
                                'self' => 'http://example.com/books/1/relationships/author',
                                'related' => 'http://example.com/books/1/author'
                            ],
                        ],
                        'co-author' => [
                            'links' => [
                                'self' => 'http://example.com/books/1/relationships/co-author',
                                'related' => 'http://example.com/books/1/co-author'
                            ],
                        ],
                        'author-with-meta' => [
                            'links' => [
                                'self' => 'http://example.com/books/1/relationships/author-with-meta',
                                'related' => 'http://example.com/books/1/author-with-meta'
                            ],
                        ],
                    ],
                ],
                [
                    'type' => 'books',
                    'id' => '2',
                    'attributes' => [
                        'title' => 'Bar',
                        'year' => 2015,
                    ],
                    'links' => [
                        'self' => 'http://example.com/books/2',
                    ],
                    'relationships' => [
                        'author' => [
                            'links' => [
                                'self' => 'http://example.com/books/2/relationships/author',
                                'related' => 'http://example.com/books/2/author'
                            ],
                        ],
                        'co-author' => [
                            'links' => [
                                'self' => 'http://example.com/books/2/relationships/co-author',
                                'related' => 'http://example.com/books/2/co-author'
                            ],
                        ],
                        'author-with-meta' => [
                            'links' => [
                                'self' => 'http://example.com/books/2/relationships/author-with-meta',
                                'related' => 'http://example.com/books/2/author-with-meta'
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"data":{"type":"people","id":"1","attributes":{"name":"Dave"},"links":{"self":"http:\/\/example.com\/people\/1"},"relationships":{"published":{"links":{"self":"http:\/\/example.com\/people\/1\/relationships\/published","related":"http:\/\/example.com\/people\/1\/published"},"data":[{"type":"books","id":"1"},{"type":"books","id":"2"}]}}},"included":[{"type":"books","id":"1","attributes":{"title":"Foo","year":1991},"links":{"self":"http:\/\/example.com\/books\/1"},"relationships":{"author":{"links":{"self":"http:\/\/example.com\/books\/1\/relationships\/author","related":"http:\/\/example.com\/books\/1\/author"}},"co-author":{"links":{"self":"http:\/\/example.com\/books\/1\/relationships\/co-author","related":"http:\/\/example.com\/books\/1\/co-author"}},"author-with-meta":{"links":{"self":"http:\/\/example.com\/books\/1\/relationships\/author-with-meta","related":"http:\/\/example.com\/books\/1\/author-with-meta"}}}},{"type":"books","id":"2","attributes":{"title":"Bar","year":2015},"links":{"self":"http:\/\/example.com\/books\/2"},"relationships":{"author":{"links":{"self":"http:\/\/example.com\/books\/2\/relationships\/author","related":"http:\/\/example.com\/books\/2\/author"}},"co-author":{"links":{"self":"http:\/\/example.com\/books\/2\/relationships\/co-author","related":"http:\/\/example.com\/books\/2\/co-author"}},"author-with-meta":{"links":{"self":"http:\/\/example.com\/books\/2\/relationships\/author-with-meta","related":"http:\/\/example.com\/books\/2\/author-with-meta"}}}}]}';
        $this->assertSame($expectedJson, $scope->toJson());
    }

    public function testSerializingCollectionResourceWithLinksForHasOneRelationship()
    {
        $baseUrl = 'http://example.com';
        $this->manager->setSerializer(new JsonApiSerializer($baseUrl));
        $this->manager->parseIncludes('author');

        $bookData = [
            [
                'id' => 1,
                'title' => 'Foo',
                'year' => '1991',
                '_author' => [
                    'id' => 1,
                    'name' => 'Dave',
                ],
            ],
            [
                'id' => 2,
                'title' => 'Bar',
                'year' => '1991',
                '_author' => [
                    'id' => 1,
                    'name' => 'Dave',
                ],
            ],
        ];

        $resource = new Collection($bookData, new JsonApiBookTransformer(), 'books');

        $scope = new Scope($this->manager, $resource);

        $expected = [
            'data' => [
                [
                    'type' => 'books',
                    'id' => '1',
                    'attributes' => [
                        'title' => 'Foo',
                        'year' => 1991,
                    ],
                    'relationships' => [
                        'author' => [
                            'links' => [
                                'self' => 'http://example.com/books/1/relationships/author',
                                'related' => 'http://example.com/books/1/author',
                            ],
                            'data' => [
                                'type' => 'people',
                                'id' => '1',
                            ],
                        ],
                        'co-author' => [
                            'links' => [
                                'self' => 'http://example.com/books/1/relationships/co-author',
                                'related' => 'http://example.com/books/1/co-author'
                            ],
                        ],
                        'author-with-meta' => [
                            'links' => [
                                'self' => 'http://example.com/books/1/relationships/author-with-meta',
                                'related' => 'http://example.com/books/1/author-with-meta'
                            ],
                        ],
                    ],
                    'links' => [
                        'self' => 'http://example.com/books/1',
                    ],
                ],
                [
                    'type' => 'books',
                    'id' => '2',
                    'attributes' => [
                        'title' => 'Bar',
                        'year' => 1991,
                    ],
                    'relationships' => [
                        'author' => [
                            'links' => [
                                'self' => 'http://example.com/books/2/relationships/author',
                                'related' => 'http://example.com/books/2/author',
                            ],
                            'data' => [
                                'type' => 'people',
                                'id' => '1',
                            ],
                        ],
                        'co-author' => [
                            'links' => [
                                'self' => 'http://example.com/books/2/relationships/co-author',
                                'related' => 'http://example.com/books/2/co-author'
                            ],
                        ],
                        'author-with-meta' => [
                            'links' => [
                                'self' => 'http://example.com/books/2/relationships/author-with-meta',
                                'related' => 'http://example.com/books/2/author-with-meta'
                            ],
                        ],
                    ],
                    'links' => [
                        'self' => 'http://example.com/books/2',
                    ],
                ],
            ],
            'included' => [
                [
                    'type' => 'people',
                    'id' => '1',
                    'attributes' => [
                        'name' => 'Dave',
                    ],
                    'links' => [
                        'self' => 'http://example.com/people/1',
                    ],
                    'relationships' => [
                        'published' => [
                            'links' => [
                                'self' => 'http://example.com/people/1/relationships/published',
                                'related' => 'http://example.com/people/1/published',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"data":[{"type":"books","id":"1","attributes":{"title":"Foo","year":1991},"links":{"self":"http:\/\/example.com\/books\/1"},"relationships":{"author":{"links":{"self":"http:\/\/example.com\/books\/1\/relationships\/author","related":"http:\/\/example.com\/books\/1\/author"},"data":{"type":"people","id":"1"}},"co-author":{"links":{"self":"http:\/\/example.com\/books\/1\/relationships\/co-author","related":"http:\/\/example.com\/books\/1\/co-author"}},"author-with-meta":{"links":{"self":"http:\/\/example.com\/books\/1\/relationships\/author-with-meta","related":"http:\/\/example.com\/books\/1\/author-with-meta"}}}},{"type":"books","id":"2","attributes":{"title":"Bar","year":1991},"links":{"self":"http:\/\/example.com\/books\/2"},"relationships":{"author":{"links":{"self":"http:\/\/example.com\/books\/2\/relationships\/author","related":"http:\/\/example.com\/books\/2\/author"},"data":{"type":"people","id":"1"}},"co-author":{"links":{"self":"http:\/\/example.com\/books\/2\/relationships\/co-author","related":"http:\/\/example.com\/books\/2\/co-author"}},"author-with-meta":{"links":{"self":"http:\/\/example.com\/books\/2\/relationships\/author-with-meta","related":"http:\/\/example.com\/books\/2\/author-with-meta"}}}}],"included":[{"type":"people","id":"1","attributes":{"name":"Dave"},"links":{"self":"http:\/\/example.com\/people\/1"},"relationships":{"published":{"links":{"self":"http:\/\/example.com\/people\/1\/relationships\/published","related":"http:\/\/example.com\/people\/1\/published"}}}}]}';
        $this->assertSame($expectedJson, $scope->toJson());
    }

    public function testSerializingCollectionResourceWithLinksForHasManyRelationship()
    {
        $baseUrl = 'http://example.com';
        $this->manager->setSerializer(new JsonApiSerializer($baseUrl));
        $this->manager->parseIncludes('published');

        $authorData = [
            [
                'id' => 1,
                'name' => 'Dave',
                '_published' => [
                    [
                        'id' => 1,
                        'title' => 'Foo',
                        'year' => '1991',
                    ],
                    [
                        'id' => 2,
                        'title' => 'Bar',
                        'year' => '2015',
                    ],
                ],
            ],
            [
                'id' => 2,
                'name' => 'Bill',
                '_published' => [
                    [
                        'id' => 1,
                        'title' => 'Foo',
                        'year' => '1991',
                    ],
                    [
                        'id' => 2,
                        'title' => 'Bar',
                        'year' => '2015',
                    ],
                ],
            ],
        ];

        $resource = new Collection($authorData, new JsonApiAuthorTransformer(), 'people');

        $scope = new Scope($this->manager, $resource);

        $expected = [
            'data' => [
                [
                    'type' => 'people',
                    'id' => '1',
                    'attributes' => [
                        'name' => 'Dave',
                    ],
                    'relationships' => [
                        'published' => [
                            'links' => [
                                'self' => 'http://example.com/people/1/relationships/published',
                                'related' => 'http://example.com/people/1/published',
                            ],
                            'data' => [
                                [
                                    'type' => 'books',
                                    'id' => 1,
                                ],
                                [
                                    'type' => 'books',
                                    'id' => 2,
                                ],
                            ],
                        ],
                    ],
                    'links' => [
                        'self' => 'http://example.com/people/1',
                    ],
                ],
                [
                    'type' => 'people',
                    'id' => '2',
                    'attributes' => [
                        'name' => 'Bill',
                    ],
                    'relationships' => [
                        'published' => [
                            'links' => [
                                'self' => 'http://example.com/people/2/relationships/published',
                                'related' => 'http://example.com/people/2/published',
                            ],
                            'data' => [
                                [
                                    'type' => 'books',
                                    'id' => 1,
                                ],
                                [
                                    'type' => 'books',
                                    'id' => 2,
                                ],
                            ],
                        ],
                    ],
                    'links' => [
                        'self' => 'http://example.com/people/2',
                    ],
                ],
            ],
            'included' => [
                [
                    'type' => 'books',
                    'id' => '1',
                    'attributes' => [
                        'title' => 'Foo',
                        'year' => 1991,
                    ],
                    'links' => [
                        'self' => 'http://example.com/books/1',
                    ],
                    'relationships' => [
                        'author' => [
                            'links' => [
                                'self' => 'http://example.com/books/1/relationships/author',
                                'related' => 'http://example.com/books/1/author',
                            ],
                        ],
                        'co-author' => [
                            'links' => [
                                'self' => 'http://example.com/books/1/relationships/co-author',
                                'related' => 'http://example.com/books/1/co-author'
                            ],
                        ],
                        'author-with-meta' => [
                            'links' => [
                                'self' => 'http://example.com/books/1/relationships/author-with-meta',
                                'related' => 'http://example.com/books/1/author-with-meta'
                            ],
                        ],
                    ],
                ],
                [
                    'type' => 'books',
                    'id' => '2',
                    'attributes' => [
                        'title' => 'Bar',
                        'year' => 2015,
                    ],
                    'links' => [
                        'self' => 'http://example.com/books/2',
                    ],
                    'relationships' => [
                        'author' => [
                            'links' => [
                                'self' => 'http://example.com/books/2/relationships/author',
                                'related' => 'http://example.com/books/2/author',
                            ],
                        ],
                        'co-author' => [
                            'links' => [
                                'self' => 'http://example.com/books/2/relationships/co-author',
                                'related' => 'http://example.com/books/2/co-author'
                            ],
                        ],
                        'author-with-meta' => [
                            'links' => [
                                'self' => 'http://example.com/books/2/relationships/author-with-meta',
                                'related' => 'http://example.com/books/2/author-with-meta'
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"data":[{"type":"people","id":"1","attributes":{"name":"Dave"},"links":{"self":"http:\/\/example.com\/people\/1"},"relationships":{"published":{"links":{"self":"http:\/\/example.com\/people\/1\/relationships\/published","related":"http:\/\/example.com\/people\/1\/published"},"data":[{"type":"books","id":"1"},{"type":"books","id":"2"}]}}},{"type":"people","id":"2","attributes":{"name":"Bill"},"links":{"self":"http:\/\/example.com\/people\/2"},"relationships":{"published":{"links":{"self":"http:\/\/example.com\/people\/2\/relationships\/published","related":"http:\/\/example.com\/people\/2\/published"},"data":[{"type":"books","id":"1"},{"type":"books","id":"2"}]}}}],"included":[{"type":"books","id":"1","attributes":{"title":"Foo","year":1991},"links":{"self":"http:\/\/example.com\/books\/1"},"relationships":{"author":{"links":{"self":"http:\/\/example.com\/books\/1\/relationships\/author","related":"http:\/\/example.com\/books\/1\/author"}},"co-author":{"links":{"self":"http:\/\/example.com\/books\/1\/relationships\/co-author","related":"http:\/\/example.com\/books\/1\/co-author"}},"author-with-meta":{"links":{"self":"http:\/\/example.com\/books\/1\/relationships\/author-with-meta","related":"http:\/\/example.com\/books\/1\/author-with-meta"}}}},{"type":"books","id":"2","attributes":{"title":"Bar","year":2015},"links":{"self":"http:\/\/example.com\/books\/2"},"relationships":{"author":{"links":{"self":"http:\/\/example.com\/books\/2\/relationships\/author","related":"http:\/\/example.com\/books\/2\/author"}},"co-author":{"links":{"self":"http:\/\/example.com\/books\/2\/relationships\/co-author","related":"http:\/\/example.com\/books\/2\/co-author"}},"author-with-meta":{"links":{"self":"http:\/\/example.com\/books\/2\/relationships\/author-with-meta","related":"http:\/\/example.com\/books\/2\/author-with-meta"}}}}]}';
        $this->assertSame($expectedJson, $scope->toJson());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage JSON API resource objects MUST have a valid id
     */
    public function testExceptionThrownIfResourceHasNoId()
    {
        $bookData = [
            'title' => 'Foo',
            'year' => '1991',
        ];

        $resource = new Item($bookData, new JsonApiBookTransformer(), 'books');

        $scope = new Scope($this->manager, $resource);
        $scope->toArray();
    }

    public function testSerializingItemWithReferenceToRootObject()
    {
        $this->manager->parseIncludes('published.author');

        $authorData = [
            'id' => 1,
            'name' => 'Dave',
            '_published' => [
                [
                    'id' => 1,
                    'title' => 'Foo',
                    'year' => 1991,
                    '_author' => ['id' => 1]
                ],
                [
                    'id' => 2,
                    'title' => 'Bar',
                    'year' => 2015,
                    '_author' => ['id' => 1]
                ],
            ],
        ];

        $resource = new Item($authorData, new JsonApiAuthorTransformer(), 'people');

        $scope = new Scope($this->manager, $resource);

        $expected = [
            'data' => [
                'type' => 'people',
                'id' => '1',
                'attributes' => [
                    'name' => 'Dave',
                ],
                'relationships' => [
                    'published' => [
                        'data' => [
                            ['type' => 'books', 'id' => '1'],
                            ['type' => 'books', 'id' => '2'],
                        ],
                    ],
                ],
            ],
            'included' => [
                [
                    'type' => 'books',
                    'id' => '1',
                    'attributes' => [
                        'title' => 'Foo',
                        'year' => 1991,
                    ],
                    'relationships' => [
                        'author' => [
                            'data' => ['type' => 'people', 'id' => '1'],
                        ],
                    ],
                ],
                [
                    'type' => 'books',
                    'id' => '2',
                    'attributes' => [
                        'title' => 'Bar',
                        'year' => 2015,
                    ],
                    'relationships' => [
                        'author' => [
                            'data' => ['type' => 'people', 'id' => '1'],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertSame($expected, $scope->toArray());

        $expectedJson = '{"data":{"type":"people","id":"1","attributes":{"name":"Dave"},"relationships":{"published":{"data":[{"type":"books","id":"1"},{"type":"books","id":"2"}]}}},"included":[{"type":"books","id":"1","attributes":{"title":"Foo","year":1991},"relationships":{"author":{"data":{"type":"people","id":"1"}}}},{"type":"books","id":"2","attributes":{"title":"Bar","year":2015},"relationships":{"author":{"data":{"type":"people","id":"1"}}}}]}';
        $this->assertSame($expectedJson, $scope->toJson());
    }

    public function testSerializingCollectionWithReferenceToRootObjects()
    {
        $this->manager->parseIncludes('author.published');

        $booksData = [
            [
                'id' => 1,
                'title' => 'Foo',
                'year' => 1991,
                '_author' => [
                    'id' => 1,
                    'name' => 'Dave',
                    '_published' => [
                        [
                            'id' => 1,
                            'title' => 'Foo',
                            'year' => 1991,
                        ],
                        [
                            'id' => 2,
                            'title' => 'Bar',
                            'year' => 2015,
                        ],
                    ],
                ],
            ],
            [
                'id' => 2,
                'title' => 'Bar',
                'year' => 2015,
                '_author' => [
                    'id' => 1,
                    '_published' => [
                        [
                            'id' => 1,
                            'title' => 'Foo',
                            'year' => 1991,
                        ],
                        [
                            'id' => 2,
                            'title' => 'Bar',
                            'year' => 2015,
                        ],
                    ],
                ],
            ],
        ];

        $resource = new Collection($booksData, new JsonApiBookTransformer(), 'books');

        $scope = new Scope($this->manager, $resource);

        $expected = [
            'data' => [
                [
                    'type' => 'books',
                    'id' => '1',
                    'attributes' => [
                        'title' => 'Foo',
                        'year' => 1991,
                    ],
                    'relationships' => [
                        'author' => [
                            'data' => [
                                'type' => 'people',
                                'id' => '1',
                            ],
                        ],
                    ],
                ],
                [
                    'type' => 'books',
                    'id' => '2',
                    'attributes' => [
                        'title' => 'Bar',
                        'year' => 2015,
                    ],
                    'relationships' => [
                        'author' => [
                            'data' => [
                                'type' => 'people',
                                'id' => '1',
                            ],
                        ],
                    ],
                ],
            ],
            'included' => [
                [
                    'type' => 'people',
                    'id' => '1',
                    'attributes' => [
                        'name' => 'Dave',
                    ],
                    'relationships' => [
                        'published' => [
                            'data' => [
                                ['type' => 'books', 'id' => '1'],
                                ['type' => 'books', 'id' => '2'],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertSame($expected, $scope->toArray());

        $expectedJson = '{"data":[{"type":"books","id":"1","attributes":{"title":"Foo","year":1991},"relationships":{"author":{"data":{"type":"people","id":"1"}}}},{"type":"books","id":"2","attributes":{"title":"Bar","year":2015},"relationships":{"author":{"data":{"type":"people","id":"1"}}}}],"included":[{"type":"people","id":"1","attributes":{"name":"Dave"},"relationships":{"published":{"data":[{"type":"books","id":"1"},{"type":"books","id":"2"}]}}}]}';
        $this->assertSame($expectedJson, $scope->toJson());
    }

    public function testSerializingCollectionResourceWithPaginator()
    {
        $baseUrl = 'http://example.com';

        $this->manager->setSerializer(new JsonApiSerializer($baseUrl));

        $total = 10;
        $count = 2;
        $perPage = 2;
        $currentPage = 2;
        $lastPage = 5;
        $previousUrl = 'http://example.com/books/?page=1';
        $currentUrl = 'http://example.com/books/?page=2';
        $nextUrl = 'http://example.com/books/?page=3';
        $lastUrl = 'http://example.com/books/?page=5';

        $paginator = Mockery::mock('League\Fractal\Pagination\PaginatorInterface');
        $paginator->shouldReceive('getCurrentPage')->andReturn($currentPage);
        $paginator->shouldReceive('getLastPage')->andReturn($lastPage);
        $paginator->shouldReceive('getTotal')->andReturn($total);
        $paginator->shouldReceive('getCount')->andReturn($count);
        $paginator->shouldReceive('getPerPage')->andReturn($perPage);
        $paginator->shouldReceive('getUrl')->with(1)->andReturn($previousUrl);
        $paginator->shouldReceive('getUrl')->with(2)->andReturn($currentUrl);
        $paginator->shouldReceive('getUrl')->with(3)->andReturn($nextUrl);
        $paginator->shouldReceive('getUrl')->with(5)->andReturn($lastUrl);

        $booksData = [
            [
                'id' => 1,
                'title' => 'Foo',
                'year' => '1991',
                '_author' => [
                    'id' => 1,
                    'name' => 'Dave',
                ],
            ],
            [
                'id' => 2,
                'title' => 'Bar',
                'year' => '1997',
                '_author' => [
                    'id' => 2,
                    'name' => 'Bob',
                ],
            ],
        ];

        $resource = new Collection($booksData, new JsonApiBookTransformer(), 'books');
        $resource->setPaginator($paginator);
        $scope = new Scope($this->manager, $resource);

        $expected = [
            'data' => [
                [
                    'type' => 'books',
                    'id' => '1',
                    'attributes' => [
                        'title' => 'Foo',
                        'year' => 1991,
                    ],
                    'links' => [
                        'self' => 'http://example.com/books/1',
                    ],
                    'relationships' => [
                        'author' => [
                            'links' => [
                                'self' => 'http://example.com/books/1/relationships/author',
                                'related' => 'http://example.com/books/1/author',
                            ],
                        ],
                        'co-author' => [
                            'links' => [
                                'self' => 'http://example.com/books/1/relationships/co-author',
                                'related' => 'http://example.com/books/1/co-author'
                            ],
                        ],
                        'author-with-meta' => [
                            'links' => [
                                'self' => 'http://example.com/books/1/relationships/author-with-meta',
                                'related' => 'http://example.com/books/1/author-with-meta'
                            ],
                        ],
                    ],
                ],
                [
                    'type' => 'books',
                    'id' => '2',
                    'attributes' => [
                        'title' => 'Bar',
                        'year' => 1997,
                    ],
                    'links' => [
                        'self' => 'http://example.com/books/2',
                    ],
                    'relationships' => [
                        'author' => [
                            'links' => [
                                'self' => 'http://example.com/books/2/relationships/author',
                                'related' => 'http://example.com/books/2/author',
                            ],
                        ],
                        'co-author' => [
                            'links' => [
                                'self' => 'http://example.com/books/2/relationships/co-author',
                                'related' => 'http://example.com/books/2/co-author'
                            ],
                        ],
                        'author-with-meta' => [
                            'links' => [
                                'self' => 'http://example.com/books/2/relationships/author-with-meta',
                                'related' => 'http://example.com/books/2/author-with-meta'
                            ],
                        ],
                    ],
                ],
            ],
            'meta' => [
                'pagination' => [
                    'total' =>  10,
                    'count' => 2,
                    'per_page' => 2,
                    'current_page' => 2,
                    'total_pages' => 5
                ]
            ],
            'links' => [
                'self' => 'http://example.com/books/?page=2',
                'first' => 'http://example.com/books/?page=1',
                'prev' => 'http://example.com/books/?page=1',
                'next' => 'http://example.com/books/?page=3',
                'last' => 'http://example.com/books/?page=5'
            ]
        ];

        $this->assertSame($expected, $scope->toArray());

        $expectedJson = '{"data":[{"type":"books","id":"1","attributes":{"title":"Foo","year":1991},"links":{"self":"http:\/\/example.com\/books\/1"},"relationships":{"author":{"links":{"self":"http:\/\/example.com\/books\/1\/relationships\/author","related":"http:\/\/example.com\/books\/1\/author"}},"co-author":{"links":{"self":"http:\/\/example.com\/books\/1\/relationships\/co-author","related":"http:\/\/example.com\/books\/1\/co-author"}},"author-with-meta":{"links":{"self":"http:\/\/example.com\/books\/1\/relationships\/author-with-meta","related":"http:\/\/example.com\/books\/1\/author-with-meta"}}}},{"type":"books","id":"2","attributes":{"title":"Bar","year":1997},"links":{"self":"http:\/\/example.com\/books\/2"},"relationships":{"author":{"links":{"self":"http:\/\/example.com\/books\/2\/relationships\/author","related":"http:\/\/example.com\/books\/2\/author"}},"co-author":{"links":{"self":"http:\/\/example.com\/books\/2\/relationships\/co-author","related":"http:\/\/example.com\/books\/2\/co-author"}},"author-with-meta":{"links":{"self":"http:\/\/example.com\/books\/2\/relationships\/author-with-meta","related":"http:\/\/example.com\/books\/2\/author-with-meta"}}}}],"meta":{"pagination":{"total":10,"count":2,"per_page":2,"current_page":2,"total_pages":5}},"links":{"self":"http:\/\/example.com\/books\/?page=2","first":"http:\/\/example.com\/books\/?page=1","prev":"http:\/\/example.com\/books\/?page=1","next":"http:\/\/example.com\/books\/?page=3","last":"http:\/\/example.com\/books\/?page=5"}}';
        $this->assertSame($expectedJson, $scope->toJson());
    }

    public function testSerializingCollectionResourceWithPaginatorWithOmittedUnavailablePreviousLink()
    {
        $baseUrl = 'http://example.com';

        $this->manager->setSerializer(new JsonApiSerializer($baseUrl));

        $total = 10;
        $count = 2;
        $perPage = 2;
        $currentPage = 1;
        $lastPage = 5;
        $currentUrl = 'http://example.com/books/?page=1';
        $nextUrl = 'http://example.com/books/?page=2';
        $lastUrl = 'http://example.com/books/?page=5';

        $paginator = Mockery::mock('League\Fractal\Pagination\PaginatorInterface');
        $paginator->shouldReceive('getCurrentPage')->andReturn($currentPage);
        $paginator->shouldReceive('getLastPage')->andReturn($lastPage);
        $paginator->shouldReceive('getTotal')->andReturn($total);
        $paginator->shouldReceive('getCount')->andReturn($count);
        $paginator->shouldReceive('getPerPage')->andReturn($perPage);
        $paginator->shouldReceive('getUrl')->with(1)->andReturn($currentUrl);
        $paginator->shouldReceive('getUrl')->with(2)->andReturn($nextUrl);
        $paginator->shouldReceive('getUrl')->with(5)->andReturn($lastUrl);

        $booksData = [
            [
                'id' => 1,
                'title' => 'Foo',
                'year' => '1991',
                '_author' => [
                    'id' => 1,
                    'name' => 'Dave',
                ],
            ],
            [
                'id' => 2,
                'title' => 'Bar',
                'year' => '1997',
                '_author' => [
                    'id' => 2,
                    'name' => 'Bob',
                ],
            ],
        ];

        $resource = new Collection($booksData, new JsonApiBookTransformer(), 'books');
        $resource->setPaginator($paginator);
        $scope = new Scope($this->manager, $resource);

        $expected = [
            'data' => [
                [
                    'type' => 'books',
                    'id' => '1',
                    'attributes' => [
                        'title' => 'Foo',
                        'year' => 1991,
                    ],
                    'links' => [
                        'self' => 'http://example.com/books/1',
                    ],
                    'relationships' => [
                        'author' => [
                            'links' => [
                                'self' => 'http://example.com/books/1/relationships/author',
                                'related' => 'http://example.com/books/1/author',
                            ],
                        ],
                        'co-author' => [
                            'links' => [
                                'self' => 'http://example.com/books/1/relationships/co-author',
                                'related' => 'http://example.com/books/1/co-author'
                            ],
                        ],
                        'author-with-meta' => [
                            'links' => [
                                'self' => 'http://example.com/books/1/relationships/author-with-meta',
                                'related' => 'http://example.com/books/1/author-with-meta'
                            ],
                        ],
                    ],
                ],
                [
                    'type' => 'books',
                    'id' => '2',
                    'attributes' => [
                        'title' => 'Bar',
                        'year' => 1997,
                    ],
                    'links' => [
                        'self' => 'http://example.com/books/2',
                    ],
                    'relationships' => [
                        'author' => [
                            'links' => [
                                'self' => 'http://example.com/books/2/relationships/author',
                                'related' => 'http://example.com/books/2/author',
                            ],
                        ],
                        'co-author' => [
                            'links' => [
                                'self' => 'http://example.com/books/2/relationships/co-author',
                                'related' => 'http://example.com/books/2/co-author'
                            ],
                        ],
                        'author-with-meta' => [
                            'links' => [
                                'self' => 'http://example.com/books/2/relationships/author-with-meta',
                                'related' => 'http://example.com/books/2/author-with-meta'
                            ],
                        ],
                    ],
                ],
            ],
            'meta' => [
                'pagination' => [
                    'total' =>  10,
                    'count' => 2,
                    'per_page' => 2,
                    'current_page' => 1,
                    'total_pages' => 5
                ]
            ],
            'links' => [
                'self' => 'http://example.com/books/?page=1',
                'first' => 'http://example.com/books/?page=1',
                'next' => 'http://example.com/books/?page=2',
                'last' => 'http://example.com/books/?page=5'
            ]
        ];

        $this->assertSame($expected, $scope->toArray());

        $expectedJson = '{"data":[{"type":"books","id":"1","attributes":{"title":"Foo","year":1991},"links":{"self":"http:\/\/example.com\/books\/1"},"relationships":{"author":{"links":{"self":"http:\/\/example.com\/books\/1\/relationships\/author","related":"http:\/\/example.com\/books\/1\/author"}},"co-author":{"links":{"self":"http:\/\/example.com\/books\/1\/relationships\/co-author","related":"http:\/\/example.com\/books\/1\/co-author"}},"author-with-meta":{"links":{"self":"http:\/\/example.com\/books\/1\/relationships\/author-with-meta","related":"http:\/\/example.com\/books\/1\/author-with-meta"}}}},{"type":"books","id":"2","attributes":{"title":"Bar","year":1997},"links":{"self":"http:\/\/example.com\/books\/2"},"relationships":{"author":{"links":{"self":"http:\/\/example.com\/books\/2\/relationships\/author","related":"http:\/\/example.com\/books\/2\/author"}},"co-author":{"links":{"self":"http:\/\/example.com\/books\/2\/relationships\/co-author","related":"http:\/\/example.com\/books\/2\/co-author"}},"author-with-meta":{"links":{"self":"http:\/\/example.com\/books\/2\/relationships\/author-with-meta","related":"http:\/\/example.com\/books\/2\/author-with-meta"}}}}],"meta":{"pagination":{"total":10,"count":2,"per_page":2,"current_page":1,"total_pages":5}},"links":{"self":"http:\/\/example.com\/books\/?page=1","first":"http:\/\/example.com\/books\/?page=1","next":"http:\/\/example.com\/books\/?page=2","last":"http:\/\/example.com\/books\/?page=5"}}';
        $this->assertSame($expectedJson, $scope->toJson());
    }

    public function testSerializingCollectionResourceWithPaginatorWithOmittedUnavailableNextLink()
    {
        $baseUrl = 'http://example.com';

        $this->manager->setSerializer(new JsonApiSerializer($baseUrl));

        $total = 10;
        $count = 2;
        $perPage = 2;
        $currentPage = 5;
        $lastPage = 5;
        $firstUrl = 'http://example.com/books/?page=1';
        $previousUrl = 'http://example.com/books/?page=4';
        $lastUrl = 'http://example.com/books/?page=5';

        $paginator = Mockery::mock('League\Fractal\Pagination\PaginatorInterface');
        $paginator->shouldReceive('getCurrentPage')->andReturn($currentPage);
        $paginator->shouldReceive('getLastPage')->andReturn($lastPage);
        $paginator->shouldReceive('getTotal')->andReturn($total);
        $paginator->shouldReceive('getCount')->andReturn($count);
        $paginator->shouldReceive('getPerPage')->andReturn($perPage);
        $paginator->shouldReceive('getUrl')->with(1)->andReturn($firstUrl);
        $paginator->shouldReceive('getUrl')->with(4)->andReturn($previousUrl);
        $paginator->shouldReceive('getUrl')->with(5)->andReturn($lastUrl);

        $booksData = [
            [
                'id' => 1,
                'title' => 'Foo',
                'year' => '1991',
                '_author' => [
                    'id' => 1,
                    'name' => 'Dave',
                ],
            ],
            [
                'id' => 2,
                'title' => 'Bar',
                'year' => '1997',
                '_author' => [
                    'id' => 2,
                    'name' => 'Bob',
                ],
            ],
        ];

        $resource = new Collection($booksData, new JsonApiBookTransformer(), 'books');
        $resource->setPaginator($paginator);
        $scope = new Scope($this->manager, $resource);

        $expected = [
            'data' => [
                [
                    'type' => 'books',
                    'id' => '1',
                    'attributes' => [
                        'title' => 'Foo',
                        'year' => 1991,
                    ],
                    'links' => [
                        'self' => 'http://example.com/books/1',
                    ],
                    'relationships' => [
                        'author' => [
                            'links' => [
                                'self' => 'http://example.com/books/1/relationships/author',
                                'related' => 'http://example.com/books/1/author',
                            ],
                        ],
                        'co-author' => [
                            'links' => [
                                'self' => 'http://example.com/books/1/relationships/co-author',
                                'related' => 'http://example.com/books/1/co-author'
                            ],
                        ],
                        'author-with-meta' => [
                            'links' => [
                                'self' => 'http://example.com/books/1/relationships/author-with-meta',
                                'related' => 'http://example.com/books/1/author-with-meta'
                            ],
                        ],
                    ],
                ],
                [
                    'type' => 'books',
                    'id' => '2',
                    'attributes' => [
                        'title' => 'Bar',
                        'year' => 1997,
                    ],
                    'links' => [
                        'self' => 'http://example.com/books/2',
                    ],
                    'relationships' => [
                        'author' => [
                            'links' => [
                                'self' => 'http://example.com/books/2/relationships/author',
                                'related' => 'http://example.com/books/2/author',
                            ],
                        ],
                        'co-author' => [
                            'links' => [
                                'self' => 'http://example.com/books/2/relationships/co-author',
                                'related' => 'http://example.com/books/2/co-author'
                            ],
                        ],
                        'author-with-meta' => [
                            'links' => [
                                'self' => 'http://example.com/books/2/relationships/author-with-meta',
                                'related' => 'http://example.com/books/2/author-with-meta'
                            ],
                        ],
                    ],
                ],
            ],
            'meta' => [
                'pagination' => [
                    'total' =>  10,
                    'count' => 2,
                    'per_page' => 2,
                    'current_page' => 5,
                    'total_pages' => 5
                ]
            ],
            'links' => [
                'self' => 'http://example.com/books/?page=5',
                'first' => 'http://example.com/books/?page=1',
                'prev' => 'http://example.com/books/?page=4',
                'last' => 'http://example.com/books/?page=5'
            ]
        ];

        $this->assertSame($expected, $scope->toArray());

        $expectedJson = '{"data":[{"type":"books","id":"1","attributes":{"title":"Foo","year":1991},"links":{"self":"http:\/\/example.com\/books\/1"},"relationships":{"author":{"links":{"self":"http:\/\/example.com\/books\/1\/relationships\/author","related":"http:\/\/example.com\/books\/1\/author"}},"co-author":{"links":{"self":"http:\/\/example.com\/books\/1\/relationships\/co-author","related":"http:\/\/example.com\/books\/1\/co-author"}},"author-with-meta":{"links":{"self":"http:\/\/example.com\/books\/1\/relationships\/author-with-meta","related":"http:\/\/example.com\/books\/1\/author-with-meta"}}}},{"type":"books","id":"2","attributes":{"title":"Bar","year":1997},"links":{"self":"http:\/\/example.com\/books\/2"},"relationships":{"author":{"links":{"self":"http:\/\/example.com\/books\/2\/relationships\/author","related":"http:\/\/example.com\/books\/2\/author"}},"co-author":{"links":{"self":"http:\/\/example.com\/books\/2\/relationships\/co-author","related":"http:\/\/example.com\/books\/2\/co-author"}},"author-with-meta":{"links":{"self":"http:\/\/example.com\/books\/2\/relationships\/author-with-meta","related":"http:\/\/example.com\/books\/2\/author-with-meta"}}}}],"meta":{"pagination":{"total":10,"count":2,"per_page":2,"current_page":5,"total_pages":5}},"links":{"self":"http:\/\/example.com\/books\/?page=5","first":"http:\/\/example.com\/books\/?page=1","prev":"http:\/\/example.com\/books\/?page=4","last":"http:\/\/example.com\/books\/?page=5"}}';
        $this->assertSame($expectedJson, $scope->toJson());
    }

    public function testCustomLinkMerge()
    {
        $manager = new Manager();
        $manager->setSerializer(new JsonApiSerializer('http://test.de'));

        $bookData = [
            'id' => 1,
            'title' => 'Foo',
            'year' => '1991',
            '_author' => [
                'id' => 1,
                'name' => 'Dave',
            ],
            'links' => [
                'custom_link' => '/custom/link',
            ],
        ];

        $resource = new Item($bookData, new JsonApiBookTransformer('test.de'), 'books');

        $scope = new Scope($manager, $resource);

        $expected = [
            'data' => [
                'type' => 'books',
                'id' => '1',
                'attributes' => [
                    'title' => 'Foo',
                    'year' => 1991,
                ],
                'links' => [
                    'self' => 'http://test.de/books/1',
                    'custom_link' => '/custom/link',
                ],
                'relationships' => [
                    'author' => [
                        'links' => [
                            'self' => 'http://test.de/books/1/relationships/author',
                            'related' => 'http://test.de/books/1/author',
                        ],
                    ],
                    'co-author' => [
                        'links' => [
                            'self' => 'http://test.de/books/1/relationships/co-author',
                            'related' => 'http://test.de/books/1/co-author'
                        ],
                    ],
                    'author-with-meta' => [
                        'links' => [
                            'self' => 'http://test.de/books/1/relationships/author-with-meta',
                            'related' => 'http://test.de/books/1/author-with-meta'
                        ],
                    ],
                ],
            ],
        ];

        $this->assertSame(json_encode($expected), $scope->toJson());
    }

    public function testCustomLinkMergeNoLink()
    {
        $manager = new Manager();
        $manager->setSerializer(new JsonApiSerializer('http://test.de'));

        $bookData = [
            'id' => 1,
            'title' => 'Foo',
            'year' => '1991',
            '_author' => [
                'id' => 1,
                'name' => 'Dave',
            ],
        ];

        $resource = new Item($bookData, new JsonApiBookTransformer(), 'books');

        $scope = new Scope($manager, $resource);

        $expected = [
            'data' => [
                'type' => 'books',
                'id' => '1',
                'attributes' => [
                    'title' => 'Foo',
                    'year' => 1991,
                ],
                'links' => [
                    'self' => 'http://test.de/books/1',
                ],
                'relationships' => [
                    'author' => [
                        'links' => [
                            'self' => 'http://test.de/books/1/relationships/author',
                            'related' => 'http://test.de/books/1/author',
                        ],
                    ],
                    'co-author' => [
                        'links' => [
                            'self' => 'http://test.de/books/1/relationships/co-author',
                            'related' => 'http://test.de/books/1/co-author'
                        ],
                    ],
                    'author-with-meta' => [
                        'links' => [
                            'self' => 'http://test.de/books/1/relationships/author-with-meta',
                            'related' => 'http://test.de/books/1/author-with-meta'
                        ],
                    ],
                ],
            ],
        ];

        $this->assertSame(json_encode($expected), $scope->toJson());
    }

    public function testCustomSelfLinkMerge()
    {
        $manager = new Manager();
        $manager->setSerializer(new JsonApiSerializer('http://test.de'));

        $bookData = [
            'id' => 1,
            'title' => 'Foo',
            'year' => '1991',
            '_author' => [
                'id' => 1,
                'name' => 'Dave',
            ],
            'links' => [
                'self' => '/custom/link',
            ],
        ];

        $resource = new Item($bookData, new JsonApiBookTransformer('test.de'), 'books');

        $scope = new Scope($manager, $resource);

        $expected = [
            'data' => [
                'type' => 'books',
                'id' => '1',
                'attributes' => [
                    'title' => 'Foo',
                    'year' => 1991,
                ],
                'links' => [
                    'self' => '/custom/link',
                ],
                'relationships' => [
                    'author' => [
                        'links' => [
                            'self' => 'http://test.de/books/1/relationships/author',
                            'related' => 'http://test.de/books/1/author',
                        ],
                    ],
                    'co-author' => [
                        'links' => [
                            'self' => 'http://test.de/books/1/relationships/co-author',
                            'related' => 'http://test.de/books/1/co-author'
                        ],
                    ],
                    'author-with-meta' => [
                        'links' => [
                            'self' => 'http://test.de/books/1/relationships/author-with-meta',
                            'related' => 'http://test.de/books/1/author-with-meta'
                        ],
                    ],
                ],
            ],
        ];

        $this->assertSame(json_encode($expected), $scope->toJson());
    }

    public function testEmptyAttributesIsObject()
    {
        $manager = new Manager();
        $manager->setSerializer(new JsonApiSerializer());

        $data = ['id' => 1];

        $resource = new Item($data, new JsonApiEmptyTransformer(), 'resources');

        $scope = new Scope($manager, $resource);

        $expectedJson = '{"data":{"type":"resources","id":"1","attributes":{}}}';

        $this->assertSame($expectedJson, $scope->toJson());
    }

    /**
     * @dataProvider serializingWithFieldsetsProvider
     */
    public function testSerializingWithFieldsets($fieldsetsToParse, $expected)
    {
        $this->manager->parseIncludes(['author', 'author.published']);

        $bookData = [
            'id' => 1,
            'title' => 'Foo',
            'year' => '1991',
            '_author' => [
                'id' => 1,
                'name' => 'Dave',
                '_published' => [
                    [
                        'id' => 1,
                        'title' => 'Foo',
                        'year' => '1991',
                    ],
                    [
                        'id' => 2,
                        'title' => 'Bar',
                        'year' => '2015',
                    ],
                ],
            ],
        ];

        $resource = new Item($bookData, new JsonApiBookTransformer(), 'books');

        $scope = new Scope($this->manager, $resource);

        $this->manager->parseFieldsets($fieldsetsToParse);
        $this->assertSame($expected, $scope->toArray());
    }

    public function serializingWithFieldsetsProvider()
    {
        return [
            [
                //Single field
                ['books' => 'title'],
                [
                    'data' => [
                        'type' => 'books',
                        'id' => '1',
                        'attributes' => [
                            'title' => 'Foo'
                        ]
                    ]
                ]
            ],
            [
                //Multiple fields
                ['books' => 'title,year'],
                [
                    'data' => [
                        'type' => 'books',
                        'id' => '1',
                        'attributes' => [
                            'title' => 'Foo',
                            'year' => 1991
                        ]
                    ]
                ]
            ],
            [
                //Include 1st level relationship
                ['books' => 'title,author', 'people' => 'name'],
                [
                    'data' => [
                        'type' => 'books',
                        'id' => '1',
                        'attributes' => [
                            'title' => 'Foo'
                        ],
                        'relationships' => [
                            'author' => [
                                'data' => [
                                    'type' => 'people',
                                    'id' => '1'
                                ]
                            ]
                        ]
                    ],
                    'included' => [
                        [
                            'type' => 'people',
                            'id' => '1',
                            'attributes' => [
                                'name' => 'Dave'
                            ]
                        ]
                    ]
                ]
            ],
            [
                //Include 2nd level relationship
                ['books' => 'title,author', 'people' => 'name,published'],
                [
                    'data' => [
                        'type' => 'books',
                        'id' => '1',
                        'attributes' => [
                            'title' => 'Foo'
                        ],
                        'relationships' => [
                            'author' => [
                                'data' => [
                                    'type' => 'people',
                                    'id' => '1'
                                ]
                            ]
                        ]
                    ],
                    'included' => [
                        [
                            'type' => 'books',
                            'id' => '2',
                            'attributes' => [
                                'title' => 'Bar'
                            ]
                        ],
                        [
                            'type' => 'people',
                            'id' => '1',
                            'attributes' => [
                                'name' => 'Dave'
                            ],
                            'relationships' => [
                                'published' => [
                                    'data' => [
                                        [
                                            'type' => 'books',
                                            'id' => '1'
                                        ],
                                        [
                                            'type' => 'books',
                                            'id' => '2'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    public function tearDown()
    {
        Mockery::close();
    }
}
