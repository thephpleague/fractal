<?php namespace League\Fractal\Test\Resource;

use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\NullResource;
use League\Fractal\Scope;
use League\Fractal\Serializer\DataArraySerializer;
use League\Fractal\Test\Dto\Person;
use League\Fractal\Test\Dto\Book;
use League\Fractal\Test\Stub\Transformer\GenericBookTransformer;
use Mockery;
use PHPUnit\Framework\TestCase;

class DataArraySerializerTest extends TestCase
{
    public function testSerializingItemResource()
    {
        $manager = new Manager();
        $manager->parseIncludes('author');
        $manager->setSerializer(new DataArraySerializer());

        $phil = Person::make('Miguel de Cervantes');
        $bookData = Book::make('Don Quixote', '1605', $phil);

        // Try without metadata
        $resource = new Item($bookData, new GenericBookTransformer(), 'book');
        $scope = new Scope($manager, $resource);

        $expected = [
            'data' => [
                'title' => 'Don Quixote',
                'year' => 1605,
                'author' => [
                    'data' => [
                        'name' => 'Miguel de Cervantes',
                    ],
                ],
            ],
        ];

        $this->assertSame($expected, $scope->toArray());

        //Test single field
        $manager->parseFieldsets(['book' => 'title']);
        $expected = [
            'data' => [
                'title' => 'Don Quixote',
            ]
        ];
        $this->assertSame($expected, $scope->toArray());

        //Test multiple field
        $manager->parseFieldsets(['book' => 'title,year']);
        $expected = [
            'data' => [
                'title' => 'Don Quixote',
                'year' => 1605
            ]
        ];
        $this->assertSame($expected, $scope->toArray());

        //Test with relationship field
        $manager->parseFieldsets(['book' => 'title,author', 'author' => 'name']);
        $expected = [
            'data' => [
                'title' => 'Don Quixote',
                'author' => [
                    'data' => [
                        'name' => 'Miguel de Cervantes'
                    ]
                ]
            ]
        ];
        $this->assertSame($expected, $scope->toArray());

        //Clear all sparse fieldsets
        $manager->parseFieldsets([]);

        // Same again with metadata
        $resource = new Item($bookData, new GenericBookTransformer(), 'book');
        $resource->setMetaValue('foo', 'bar');

        $scope = new Scope($manager, $resource);

        $expected = [
            'data' => [
                'title' => 'Don Quixote',
                'year' => 1605,
                'author' => [
                    'data' => [
                        'name' => 'Miguel de Cervantes',
                    ],
                ],
            ],
            'meta' => [
                'foo' => 'bar',
            ],
        ];

        $this->assertSame($expected, $scope->toArray());

        //Test with relationship field
        $manager->parseFieldsets(['book' => 'title,author', 'author' => 'name']);
        $expected = [
            'data' => [
                'title' => 'Don Quixote',
                'author' => [
                    'data' => [
                        'name' => 'Miguel de Cervantes',
                    ]
                ]
            ],
            'meta' => [
                'foo' => 'bar'
            ],
        ];
        $this->assertSame($expected, $scope->toArray());
    }

    public function testSerializingCollectionResource()
    {
        $manager = new Manager();
        $manager->parseIncludes('author');
        $manager->setSerializer(new DataArraySerializer());

        $phil = Person::make('Miguel de Cervantes');
        $bookFoo = Book::make('Don Quixote', '1605', $phil);

        $taylor = Person::make('J. K. Rowling');
        $bookBar = Book::make('Harry Potter', '1997', $taylor);

        $booksData = [
            $bookFoo,
            $bookBar,
        ];

        // Try without metadata
        $resource = new Collection($booksData, new GenericBookTransformer(), 'books');

        $scope = new Scope($manager, $resource);

        $expected = [
            'data' => [
                [
                    'title' => 'Don Quixote',
                    'year' => 1605,
                    'author' => [
                        'data' => [
                            'name' => 'Miguel de Cervantes',
                        ],
                    ],
                ],
                [
                    'title' => 'Harry Potter',
                    'year' => 1997,
                    'author' => [
                        'data' => [
                            'name' => 'J. K. Rowling',
                        ],
                    ],
                ],
            ],
        ];

        $this->assertSame($expected, $scope->toArray());

        $expectedJson = '{"data":[{"title":"Don Quixote","year":1605,"author":{"data":{"name":"Miguel de Cervantes"}}},{"title":"Harry Potter","year":1997,"author":{"data":{"name":"J. K. Rowling"}}}]}';
        $this->assertSame($expectedJson, $scope->toJson());

        //Test single field
        $manager->parseFieldsets(['books' => 'title']);
        $expected = [
            'data' => [
                ['title' => 'Don Quixote'],
                ['title' => 'Harry Potter'],
            ],
        ];
        $this->assertSame($expected, $scope->toArray());

        //Test multiple field
        $manager->parseFieldsets(['books' => 'title,year']);
        $expected = [
            'data' => [
                [
                    'title' => 'Don Quixote',
                    'year' => 1605
                ],
                [
                    'title' => 'Harry Potter',
                    'year' => 1997
                ]
            ],
        ];
        $this->assertSame($expected, $scope->toArray());

        //Test with relationship field
        $manager->parseFieldsets(['books' => 'title,author', 'author' => 'name']);
        $expected = [
            'data' => [
                [
                    'title' => 'Don Quixote',
                    'author' => [
                        'data' => [
                            'name' => 'Miguel de Cervantes',
                        ]
                    ]
                ],
                [
                    'title' => 'Harry Potter',
                    'author' => [
                        'data' => [
                            'name' => 'J. K. Rowling',
                        ]
                    ]
                ]
            ]
        ];
        $this->assertSame($expected, $scope->toArray());

        //Clear all sparse fieldsets
        $manager->parseFieldsets([]);

        // Same again with meta
        $resource = new Collection($booksData, new GenericBookTransformer(), 'books');
        $resource->setMetaValue('foo', 'bar');

        $scope = new Scope($manager, $resource);


        $expected = [
            'data' => [
                [
                    'title' => 'Don Quixote',
                    'year' => 1605,
                    'author' => [
                        'data' => [
                            'name' => 'Miguel de Cervantes',
                        ],
                    ],
                ],
                [
                    'title' => 'Harry Potter',
                    'year' => 1997,
                    'author' => [
                        'data' => [
                            'name' => 'J. K. Rowling',
                        ],
                    ],
                ],
            ],
            'meta' => [
                'foo' => 'bar',
            ],
        ];

        $this->assertSame($expected, $scope->toArray());

        $expectedJson = '{"data":[{"title":"Don Quixote","year":1605,"author":{"data":{"name":"Miguel de Cervantes"}}},{"title":"Harry Potter","year":1997,"author":{"data":{"name":"J. K. Rowling"}}}],"meta":{"foo":"bar"}}';
        $this->assertSame($expectedJson, $scope->toJson());

        $manager->parseFieldsets(['books' => 'title,author', 'author' => 'name']);
        $expected = [
            'data' => [
                [
                    'title' => 'Don Quixote',
                    'author' => [
                        'data' => [
                            'name' => 'Miguel de Cervantes',
                        ]
                    ]
                ],
                [
                    'title' => 'Harry Potter',
                    'author' => [
                        'data' => [
                            'name' => 'J. K. Rowling',
                        ]
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
        $manager->setSerializer(new DataArraySerializer());

        $phil = Person::make('Miguel de Cervantes');
        $bookData = Book::make('Don Quixote', '1605', $phil);

        // Try without metadata
        $resource = new NullResource($bookData, new GenericBookTransformer(), 'book');
        $scope = new Scope($manager, $resource);

        $expected = [
            'data' => [],
        ];
        $this->assertSame($expected, $scope->toArray());

        //Test single field
        $manager->parseFieldsets(['book' => 'title']);
        $this->assertSame($expected, $scope->toArray());

        //Test multiple fields
        $manager->parseFieldsets(['book' => 'title,year']);
        $this->assertSame($expected, $scope->toArray());

        //Test with relationship
        $manager->parseFieldsets(['book' => 'title,author', 'author' => 'name']);
        $this->assertSame($expected, $scope->toArray());

        //Clear all sparse fieldsets
        $manager->parseFieldsets([]);

        // Same again with metadata
        $resource = new NullResource($bookData, new GenericBookTransformer(), 'book');
        $resource->setMetaValue('foo', 'bar');

        $scope = new Scope($manager, $resource);

        $expected = [
            'data' => [],
            'meta' => [
                'foo' => 'bar',
            ],
        ];
        $this->assertSame($expected, $scope->toArray());

        //Test with relationship
        $manager->parseFieldsets(['book' => 'title,author', 'author' => 'name']);
        $this->assertSame($expected, $scope->toArray());
    }

    public function tearDown()
    {
        Mockery::close();
    }
}
