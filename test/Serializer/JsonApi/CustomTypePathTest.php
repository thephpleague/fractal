<?php namespace League\Fractal\Test\Serializer;

use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Scope;
use League\Fractal\Test\Stub\Serializer\JsonApiSerializerWithCustomTypePath;
use League\Fractal\Test\Stub\Transformer\JsonApiBookTransformerWithSingularType;
use Mockery;

class CustomTypePathTest extends \PHPUnit_Framework_TestCase
{
    private $manager;

    public function setUp()
    {
        $baseUrl = 'http://example.com';

        $this->manager = new Manager();
        $this->manager->setSerializer(new JsonApiSerializerWithCustomTypePath($baseUrl));
    }

    public function testSerializingItemResourceWithSelfLink()
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

        $resource = new Item($bookData, new JsonApiBookTransformerWithSingularType(), 'book');

        $scope = new Scope($this->manager, $resource);

        $expected = [
            'data' => [
                'type' => 'book',
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
                        'data' => [
                            'type' => 'person',
                            'id' => '1',
                        ],
                    ],
                ],
            ],
            'included' => [
                [
                    'type' => 'person',
                    'id' => '1',
                    'attributes' => [
                        'name' => 'Dave',
                    ],
                    'links' => [
                        'self' => 'http://example.com/persons/1',
                    ],
                ]
            ],
        ];

        $this->assertSame($expected, $scope->toArray());
        $this->assertSame(json_encode($expected), $scope->toJson());
    }

    public function tearDown()
    {
        Mockery::close();
    }
}
