<?php namespace League\Fractal\Test\Serializer;

use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\NullResource;
use League\Fractal\Scope;
use League\Fractal\Serializer\ArraySerializer;
use League\Fractal\Test\Stub\Transformer\GenericBookTransformer;
use Mockery;
use PHPUnit\Framework\TestCase;

class ArraySerializerTest extends TestCase
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

        //Test single field
        $manager->parseFieldsets(['book' => 'title']);
        $expected = ['title' => 'Foo'];
        $this->assertSame($expected, $scope->toArray());

        //Test multiple field
        $manager->parseFieldsets(['book' => 'title,year']);
        $expected = [
            'title' => 'Foo',
            'year' => 1991
        ];
        $this->assertSame($expected, $scope->toArray());

        //Test with relationship field
        $manager->parseFieldsets(['book' => 'title,author', 'author' => 'name']);
        $expected = [
            'title' => 'Foo',
            'author' => [
                'name' => 'Dave'
            ],
        ];
        $this->assertSame($expected, $scope->toArray());

        //Clear all sparse fieldsets
        $manager->parseFieldsets([]);
        //Same again with meta
        $resource->setMetaValue('foo', 'bar');

        $scope = new Scope($manager, $resource);

        $expected = [
            'title' => 'Foo',
            'year' => 1991,
            'author' => [
                'name' => 'Dave'
            ],
            'meta' => [
                'foo' => 'bar'
            ]
        ];

        $this->assertSame($expected, $scope->toArray());

        //Test with relationship field
        $manager->parseFieldsets(['book' => 'title,author', 'author' => 'name']);
        $expected = [
            'title' => 'Foo',
            'author' => [
                'name' => 'Dave',
            ],
            'meta' => [
                'foo' => 'bar',
            ]
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

        //Test single field
        $manager->parseFieldsets(['books' => 'title']);
        $expected = [
            'books' => [
                ['title' => 'Foo'],
                ['title' => 'Bar']
            ]
        ];
        $this->assertSame($expected, $scope->toArray());

        //Test multiple field
        $manager->parseFieldsets(['books' => 'title,year']);
        $expected = [
            'books' => [
                [
                    'title' => 'Foo',
                    'year' => 1991
                ],
                [
                    'title' => 'Bar',
                    'year' => 1997
                ]
            ]
        ];
        $this->assertSame($expected, $scope->toArray());

        //Test with relationship field
        $manager->parseFieldsets(['books' => 'title,author', 'author' => 'name']);
        $expected = [
            'books' => [
                [
                    'title' => 'Foo',
                    'author' => [
                        'name' => 'Dave'
                    ]
                ],
                [
                    'title' => 'Bar',
                    'author' => [
                        'name' => 'Bob'
                    ]
                ]
            ]
        ];
        $this->assertSame($expected, $scope->toArray());

        //Clear all sparse fieldsets
        $manager->parseFieldsets([]);

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

        $manager->parseFieldsets(['books' => 'title,author', 'author' => 'name']);
        $expected = [
            'books' => [
                [
                    'title' => 'Foo',
                    'author' => [
                        'name' => 'Dave'
                    ]
                ],
                [
                    'title' => 'Bar',
                    'author' => [
                        'name' => 'Bob'
                    ]
                ]
            ],
            'meta' => [
                'foo' => 'bar',
            ]
        ];
        $this->assertSame($expected, $scope->toArray());
    }

    public function testSerializingNullResource()
    {
        $manager = new Manager();
        $manager->parseIncludes('author');
        $manager->setSerializer(new ArraySerializer());

        $resource = new NullResource($this->bookCollectionInput, new GenericBookTransformer(), 'books');

        // Try without metadata
        $scope = new Scope($manager, $resource);

        $expected = [];
        $this->assertSame($expected, $scope->toArray());

        // JSON array of JSON objects
        $expectedJson = '[]';
        $this->assertSame($expectedJson, $scope->toJson());

        //Test single field
        $manager->parseFieldsets(['books' => 'title']);
        $this->assertSame($expected, $scope->toArray());

        //Test multiple fields
        $manager->parseFieldsets(['books' => 'title,year']);
        $this->assertSame($expected, $scope->toArray());

        //Test with relationship
        $manager->parseFieldsets(['books' => 'title,author', 'author' => 'name']);
        $this->assertSame($expected, $scope->toArray());

        //Clear all sparse fieldsets
        $manager->parseFieldsets([]);

        // Same again with metadata
        $resource->setMetaValue('foo', 'bar');
        $scope = new Scope($manager, $resource);

        $expected = [
            'meta' => [
                'foo' => 'bar',
            ],
        ];
        $this->assertSame($expected, $scope->toArray());

        $expectedJson = '{"meta":{"foo":"bar"}}';
        $this->assertSame($expectedJson, $scope->toJson());

        //Test with relationship
        $manager->parseFieldsets(['books' => 'title,author', 'author' => 'name']);
        $this->assertSame($expected, $scope->toArray());
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
