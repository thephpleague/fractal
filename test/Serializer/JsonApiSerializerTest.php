<?php

use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Scope;
use League\Fractal\Serializer\JsonApiSerializer;
use League\Fractal\Test\Stub\Transformer\JsonApiBookTransformer;
use League\Fractal\Test\Stub\Transformer\JsonApiAuthorTransformer;

class JsonApiSerializerTest extends PHPUnit_Framework_TestCase
{
    private $manager;

    public function setUp()
    {
        $this->manager = new Manager();
        $this->manager->setSerializer(new JsonApiSerializer());
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
            ],
        ];

        $this->assertSame($expected, $scope->toArray());

        $expectedJson = '{"data":{"type":"books","id":"1","attributes":{"title":"Foo","year":1991},"links":{"self":"http:\/\/example.com\/books\/1"}}}';
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
                ],
            ],
        ];

        $this->assertSame($expected, $scope->toArray());

        $expectedJson = '{"data":[{"type":"books","id":"1","attributes":{"title":"Foo","year":1991},"links":{"self":"http:\/\/example.com\/books\/1"}},{"type":"books","id":"2","attributes":{"title":"Bar","year":1997},"links":{"self":"http:\/\/example.com\/books\/2"}}]}';
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
                    'links' => [
                        'self' => 'http://example.com/people/1',
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"data":{"type":"books","id":"1","attributes":{"title":"Foo","year":1991},"links":{"self":"http:\/\/example.com\/books\/1"},"relationships":{"author":{"links":{"self":"http:\/\/example.com\/books\/1\/relationships\/author","related":"http:\/\/example.com\/books\/1\/author"},"data":{"type":"people","id":"1"}}}},"included":[{"type":"people","id":"1","attributes":{"name":"Dave"},"links":{"self":"http:\/\/example.com\/people\/1"}}]}';
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
                ],
            ],
        ];

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"data":{"type":"people","id":"1","attributes":{"name":"Dave"},"links":{"self":"http:\/\/example.com\/people\/1"},"relationships":{"published":{"links":{"self":"http:\/\/example.com\/people\/1\/relationships\/published","related":"http:\/\/example.com\/people\/1\/published"},"data":[{"type":"books","id":"1"},{"type":"books","id":"2"}]}}},"included":[{"type":"books","id":"1","attributes":{"title":"Foo","year":1991},"links":{"self":"http:\/\/example.com\/books\/1"}},{"type":"books","id":"2","attributes":{"title":"Bar","year":2015},"links":{"self":"http:\/\/example.com\/books\/2"}}]}';
        $this->assertSame($expectedJson, $scope->toJson());
    }

    /**
     * @expectedException InvalidArgumentException
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

    public function tearDown()
    {
        Mockery::close();
    }
}
