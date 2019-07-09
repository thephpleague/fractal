<?php namespace League\Fractal\Test\Serializer;

use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\NullResource;
use League\Fractal\Scope;
use League\Fractal\Serializer\ArraySerializer;
use League\Fractal\Test\Dto\Person;
use League\Fractal\Test\Dto\Book;
use League\Fractal\Test\Stub\Transformer\GenericBookTransformer;
use Mockery;
use PHPUnit\Framework\TestCase;

class ArraySerializerTest extends TestCase
{
    private function bookItemInput()
    {
        $author = Person::make('Miguel de Cervantes');

        return Book::make('Don Quixote', '1605', $author);
    }

    private function bookCollectionInput()
    {
        $phil = Person::make('Miguel de Cervantes');
        $taylor = Person::make('J. K. Rowling');

        return [
            Book::make('Don Quixote', '1605', $phil),
            Book::make('Harry Potter', '1997', $taylor)
        ];
    }

    public function testSerializingItemResource()
    {
        $manager = new Manager();
        $manager->parseIncludes('author');
        $manager->setSerializer(new ArraySerializer());

        $resource = new Item($this->bookItemInput(), new GenericBookTransformer(), 'book');

        // Try without metadata
        $scope = new Scope($manager, $resource);

        $expected = [
            'title' => 'Don Quixote',
            'year' => 1605,
            'author' => [
                'name' => 'Miguel de Cervantes',
            ],
        ];

        $this->assertSame($expected, $scope->toArray());

        //Test single field
        $manager->parseFieldsets(['book' => 'title']);
        $expected = ['title' => 'Don Quixote'];
        $this->assertSame($expected, $scope->toArray());

        //Test multiple field
        $manager->parseFieldsets(['book' => 'title,year']);
        $expected = [
            'title' => 'Don Quixote',
            'year' => 1605
        ];
        $this->assertSame($expected, $scope->toArray());

        //Test with relationship field
        $manager->parseFieldsets(['book' => 'title,author', 'author' => 'name']);
        $expected = [
            'title' => 'Don Quixote',
            'author' => [
                'name' => 'Miguel de Cervantes'
            ],
        ];
        $this->assertSame($expected, $scope->toArray());

        //Clear all sparse fieldsets
        $manager->parseFieldsets([]);
        //Same again with meta
        $resource->setMetaValue('foo', 'bar');

        $scope = new Scope($manager, $resource);

        $expected = [
            'title' => 'Don Quixote',
            'year' => 1605,
            'author' => [
                'name' => 'Miguel de Cervantes'
            ],
            'meta' => [
                'foo' => 'bar'
            ]
        ];

        $this->assertSame($expected, $scope->toArray());

        //Test with relationship field
        $manager->parseFieldsets(['book' => 'title,author', 'author' => 'name']);
        $expected = [
            'title' => 'Don Quixote',
            'author' => [
                'name' => 'Miguel de Cervantes',
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

        $resource = new Collection($this->bookCollectionInput(), new GenericBookTransformer(), 'books');

        // Try without metadata
        $scope = new Scope($manager, $resource);

        $expected = [
            'books' => [
                [
                    'title' => 'Don Quixote',
                    'year' => 1605,
                    'author' => [
                        'name' => 'Miguel de Cervantes',
                    ],
                ],
                [
                    'title' => 'Harry Potter',
                    'year' => 1997,
                    'author' => [
                        'name' => 'J. K. Rowling',
                    ],
                ],
            ],
        ];

        $this->assertSame($expected, $scope->toArray());

        // JSON array of JSON objects
        $expectedJson = '{"books":[{"title":"Don Quixote","year":1605,"author":{"name":"Miguel de Cervantes"}},{"title":"Harry Potter","year":1997,"author":{"name":"J. K. Rowling"}}]}';
        $this->assertSame($expectedJson, $scope->toJson());

        //Test single field
        $manager->parseFieldsets(['books' => 'title']);
        $expected = [
            'books' => [
                ['title' => 'Don Quixote'],
                ['title' => 'Harry Potter']
            ]
        ];
        $this->assertSame($expected, $scope->toArray());

        //Test multiple field
        $manager->parseFieldsets(['books' => 'title,year']);
        $expected = [
            'books' => [
                [
                    'title' => 'Don Quixote',
                    'year' => 1605
                ],
                [
                    'title' => 'Harry Potter',
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
                    'title' => 'Don Quixote',
                    'author' => [
                        'name' => 'Miguel de Cervantes'
                    ]
                ],
                [
                    'title' => 'Harry Potter',
                    'author' => [
                        'name' => 'J. K. Rowling'
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
                    'title' => 'Don Quixote',
                    'year' => 1605,
                    'author' => [
                        'name' => 'Miguel de Cervantes',
                    ],
                ],
                [
                    'title' => 'Harry Potter',
                    'year' => 1997,
                    'author' => [
                        'name' => 'J. K. Rowling',
                    ],
                ],
            ],
            'meta' => [
                'foo' => 'bar',
            ],
        ];

        $this->assertSame($expected, $scope->toArray());

        $expectedJson = '{"books":[{"title":"Don Quixote","year":1605,"author":{"name":"Miguel de Cervantes"}},{"title":"Harry Potter","year":1997,"author":{"name":"J. K. Rowling"}}],"meta":{"foo":"bar"}}';
        $this->assertSame($expectedJson, $scope->toJson());

        $manager->parseFieldsets(['books' => 'title,author', 'author' => 'name']);
        $expected = [
            'books' => [
                [
                    'title' => 'Don Quixote',
                    'author' => [
                        'name' => 'Miguel de Cervantes'
                    ]
                ],
                [
                    'title' => 'Harry Potter',
                    'author' => [
                        'name' => 'J. K. Rowling'
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

        $resource = new NullResource($this->bookCollectionInput(), new GenericBookTransformer(), 'books');

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

        $resource = new Collection($this->bookCollectionInput(), new GenericBookTransformer());

        // Try without metadata
        $scope = new Scope($manager, $resource);

        // JSON array of JSON objects
        $expectedJson = '{"data":[{"title":"Don Quixote","year":1605,"author":{"name":"Miguel de Cervantes"}},{"title":"Harry Potter","year":1997,"author":{"name":"J. K. Rowling"}}]}';
        $this->assertSame($expectedJson, $scope->toJson());

        // Same again with metadata
        $resource->setMetaValue('foo', 'bar');

        $scope = new Scope($manager, $resource);

        $expectedJson = '{"data":[{"title":"Don Quixote","year":1605,"author":{"name":"Miguel de Cervantes"}},{"title":"Harry Potter","year":1997,"author":{"name":"J. K. Rowling"}}],"meta":{"foo":"bar"}}';
        $this->assertSame($expectedJson, $scope->toJson());
    }

    public function tearDown()
    {
        Mockery::close();
    }
}
