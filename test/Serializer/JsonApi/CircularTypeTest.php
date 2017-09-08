<?php namespace League\Fractal\Test\Serializer;

use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Scope;
use League\Fractal\Serializer\JsonApiSerializer;
use League\Fractal\Test\Stub\Transformer\JsonApiCircularTypeAuthorTransformer;
use League\Fractal\Test\Stub\Transformer\JsonApiCircularTypeBookTransformer;
use Mockery;

class CircularTypeTest extends \PHPUnit_Framework_TestCase
{
    private $manager;

    public function setUp()
    {
        $this->manager = new Manager();
        $this->manager->setSerializer(new JsonApiSerializer());
    }

    public function testSerializingWithSingleCircularTypeReference()
    {
        $this->manager->parseIncludes('prequel');

        $bookData = [
            [
                'id' => 1,
                'title' => 'Foo',
            ],
            [
                'id' => 2,
                'title' => 'Bar',
                '_prequel' => [
                    'id' => 1,
                    'title' => 'Foo',
                ],
            ],
            [
                'id' => 3,
                'title' => 'Baz',
                '_prequel' => [
                    'id' => 2,
                    'title' => 'Bar',
                ],
            ]
        ];

        $resources = new Collection($bookData, new JsonApiCircularTypeBookTransformer(), 'book');
        $scope = new Scope($this->manager, $resources);

        $expected = [
            'data' => [
                [
                    'type' => 'book',
                    'id' => '1',
                    'attributes' => ['title' => 'Foo'],
                ],
                [
                    'type' => 'book',
                    'id' => '2',
                    'attributes' => ['title' => 'Bar'],
                    'relationships' => [
                        'prequel' => [
                            'data' => ['type' => 'book', 'id' => '1'],
                        ],
                    ],
                ],
                [
                    'type' => 'book',
                    'id' => '3',
                    'attributes' => ['title' => 'Baz'],
                    'relationships' => [
                        'prequel' => [
                            'data' => ['type' => 'book', 'id' => '2'],
                        ],
                    ],
                ],
            ],
            'included' => []
        ];

        $this->assertSame($expected, $scope->toArray());
        $this->assertSame(json_encode($expected), $scope->toJson());
    }

    public function testSerializingWithMultipleCircularTypeReferences()
    {
        $this->manager->parseIncludes('prequel');

        $bookData = [
            [
                'id' => 1,
                'title' => 'Foo',
            ],
            [
                'id' => 2,
                'title' => 'Bar',
                '_prequel' => [
                    'id' => 1,
                    'title' => 'Foo',
                ],
            ],
            [
                'id' => 3,
                'title' => 'Baz',
                '_prequel' => [
                    'id' => 2,
                    'title' => 'Bar',
                ],
            ],
            [
                'id' => 4,
                'title' => 'Baq',
                '_prequel' => [
                    'id' => 2,
                    'title' => 'Bar',
                ],
            ]
        ];

        $resources = new Collection($bookData, new JsonApiCircularTypeBookTransformer(), 'book');
        $scope = new Scope($this->manager, $resources);

        $expected = [
            'data' => [
                [
                    'type' => 'book',
                    'id' => '1',
                    'attributes' => ['title' => 'Foo'],
                ],
                [
                    'type' => 'book',
                    'id' => '2',
                    'attributes' => ['title' => 'Bar'],
                    'relationships' => [
                        'prequel' => [
                            'data' => ['type' => 'book', 'id' => '1'],
                        ],
                    ],
                ],
                [
                    'type' => 'book',
                    'id' => '3',
                    'attributes' => ['title' => 'Baz'],
                    'relationships' => [
                        'prequel' => [
                            'data' => ['type' => 'book', 'id' => '2'],
                        ],
                    ],
                ],
                [
                    'type' => 'book',
                    'id' => '4',
                    'attributes' => ['title' => 'Baq'],
                    'relationships' => [
                        'prequel' => [
                            'data' => ['type' => 'book', 'id' => '2'],
                        ],
                    ],
                ],
            ],
            'included' => []
        ];

        $this->assertSame($expected, $scope->toArray());
        $this->assertSame(json_encode($expected), $scope->toJson());
    }

    public function testSerializingWithNestedSingleCircularTypeReference()
    {
        $this->manager->parseIncludes('books,books.prequel');

        $data = [
            [
                'id' => 1,
                'name' => 'Author 1',
                '_books' => [
                    [
                        'id' => 1,
                        'title' => 'Book 1',
                    ],
                    [
                        'id' => 2,
                        'title' => 'Book 2',
                        '_prequel' => [
                            'id' => 1,
                            'title' => 'Book 1',
                        ],
                    ],
                ]
            ],
            [
                'id' => 2,
                'name' => 'Author 2',
                '_books' => [
                    [
                        'id' => 3,
                        'title' => 'Book 3',
                        '_prequel' => [
                            'id' => 2,
                            'title' => 'Book 2',
                        ],
                    ],
                    [
                        'id' => 4,
                        'title' => 'Book 4',
                        '_prequel' => [
                            'id' => 3,
                            'title' => 'Book 3',
                        ],
                    ],
                ]
            ],
        ];

        $resources = new Collection($data, new JsonApiCircularTypeAuthorTransformer(), 'author');
        $scope = new Scope($this->manager, $resources);

        $expected = [
            'data' => [
                [
                    'type' => 'author',
                    'id' => '1',
                    'attributes' => ['name' => 'Author 1'],
                    'relationships' => [
                        'books' => [
                            'data' => [
                                ['type' => 'book', 'id' => '1'],
                                ['type' => 'book', 'id' => '2'],
                            ]
                        ],
                    ],
                ],
                [
                    'type' => 'author',
                    'id' => '2',
                    'attributes' => ['name' => 'Author 2'],
                    'relationships' => [
                        'books' => [
                            'data' => [
                                ['type' => 'book', 'id' => '3'],
                                ['type' => 'book', 'id' => '4'],
                            ]
                        ],
                    ],
                ],
            ],
            'included' => [
                [
                    'type' => 'book',
                    'id' => '1',
                    'attributes' => ['title' => 'Book 1'],
                ],
                [
                    'type' => 'book',
                    'id' => '2',
                    'attributes' => ['title' => 'Book 2'],
                    'relationships' => [
                        'prequel' => [
                            'data' => ['type' => 'book', 'id' => '1'],
                        ],
                    ],
                ],
                [
                    'type' => 'book',
                    'id' => '3',
                    'attributes' => ['title' => 'Book 3'],
                    'relationships' => [
                        'prequel' => [
                            'data' => ['type' => 'book', 'id' => '2'],
                        ],
                    ],
                ],
                [
                    'type' => 'book',
                    'id' => '4',
                    'attributes' => ['title' => 'Book 4'],
                    'relationships' => [
                        'prequel' => [
                            'data' => ['type' => 'book', 'id' => '3'],
                        ],
                    ],
                ],
            ]
        ];

        $this->assertSame(json_encode($expected, JSON_PRETTY_PRINT), $scope->toJson(JSON_PRETTY_PRINT));
        $this->assertSame($expected, $scope->toArray());
    }


    public function tearDown()
    {
        Mockery::close();
    }
}