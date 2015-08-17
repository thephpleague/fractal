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

        $bookData = array(
            'id' => 1,
            'title' => 'Foo',
            'year' => '1991',
            '_author' => array(
                'id' => 1,
                'name' => 'Dave',
            ),
        );

        $resource = new Item($bookData, new JsonApiBookTransformer(), 'books');

        $scope = new Scope($this->manager, $resource);

        $expected = array(
            'data' => array(
                'type' => 'books',
                'id' => '1',
                'attributes' => array(
                    'title' => 'Foo',
                    'year' => 1991,
                ),
                'relationships' => array(
                    'author' => array(
                        'data' => array(
                            'type' => 'people',
                            'id' => '1',
                        ),
                    ),
                ),
            ),
            'included' => array(
                array(
                    'type' => 'people',
                    'id' => '1',
                    'attributes' => array(
                        'name' => 'Dave',
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"data":{"type":"books","id":"1","attributes":{"title":"Foo","year":1991},"relationships":{"author":{"data":{"type":"people","id":"1"}}}},"included":[{"type":"people","id":"1","attributes":{"name":"Dave"}}]}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

    public function testSerializingItemResourceWithHasManyInclude()
    {
        $this->manager->parseIncludes('published');

        $authorData = array(
            'id' => 1,
            'name' => 'Dave',
            '_published' => array(
                array(
                    'id' => 1,
                    'title' => 'Foo',
                    'year' => '1991',
                ),
                array(
                    'id' => 2,
                    'title' => 'Bar',
                    'year' => '2015',
                ),
            ),
        );

        $resource = new Item($authorData, new JsonApiAuthorTransformer(), 'people');

        $scope = new Scope($this->manager, $resource);

        $expected = array(
            'data' => array(
                'type' => 'people',
                'id' => '1',
                'attributes' => array(
                    'name' => 'Dave',
                ),
                'relationships' => array(
                    'published' => array(
                        'data' => array(
                            array(
                                'type' => 'books',
                                'id' => 1,
                            ),
                            array(
                                'type' => 'books',
                                'id' => 2,
                            ),
                        ),
                    ),
                ),
            ),
            'included' => array(
                array(
                    'type' => 'books',
                    'id' => '1',
                    'attributes' => array(
                        'title' => 'Foo',
                        'year' => 1991,
                    ),
                ),
                array(
                    'type' => 'books',
                    'id' => '2',
                    'attributes' => array(
                        'title' => 'Bar',
                        'year' => 2015,
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"data":{"type":"people","id":"1","attributes":{"name":"Dave"},"relationships":{"published":{"data":[{"type":"books","id":"1"},{"type":"books","id":"2"}]}}},"included":[{"type":"books","id":"1","attributes":{"title":"Foo","year":1991}},{"type":"books","id":"2","attributes":{"title":"Bar","year":2015}}]}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

    public function testSerializingItemResourceWithoutIncludes()
    {
        $bookData = array(
            'id' => 1,
            'title' => 'Foo',
            'year' => '1991',
            '_author' => array(
                'id' => 1,
                'name' => 'Dave',
            ),
        );

        $resource = new Item($bookData, new JsonApiBookTransformer(), 'books');

        $scope = new Scope($this->manager, $resource);

        $expected = array(
            'data' => array(
                'type' => 'books',
                'id' => '1',
                'attributes' => array(
                    'title' => 'Foo',
                    'year' => 1991,
                ),
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"data":{"type":"books","id":"1","attributes":{"title":"Foo","year":1991}}}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

    public function testSerializingItemResourceWithMeta()
    {
        $bookData = array(
            'id' => 1,
            'title' => 'Foo',
            'year' => '1991',
            '_author' => array(
                'id' => 1,
                'name' => 'Dave',
            ),
        );

        $resource = new Item($bookData, new JsonApiBookTransformer(), 'books');
        $resource->setMetaValue('foo', 'bar');

        $scope = new Scope($this->manager, $resource);

        $expected = array(
            'data' => array(
                'type' => 'books',
                'id' => '1',
                'attributes' => array(
                    'title' => 'Foo',
                    'year' => 1991,
                ),
            ),
            'meta' => array(
                'foo' => 'bar',
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"data":{"type":"books","id":"1","attributes":{"title":"Foo","year":1991}},"meta":{"foo":"bar"}}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

    public function testSerializingCollectionResourceWithoutIncludes()
    {
        $booksData = array(
            array(
                'id' => 1,
                'title' => 'Foo',
                'year' => '1991',
                '_author' => array(
                    'id' => 1,
                    'name' => 'Dave',
                ),
            ),
            array(
                'id' => 2,
                'title' => 'Bar',
                'year' => '1997',
                '_author' => array(
                    'id' => 2,
                    'name' => 'Bob',
                ),
            ),
        );

        $resource = new Collection($booksData, new JsonApiBookTransformer(), 'books');
        $scope = new Scope($this->manager, $resource);

        $expected = array(
            'data' => array(
                array(
                    'type' => 'books',
                    'id' => '1',
                    'attributes' => array(
                        'title' => 'Foo',
                        'year' => 1991,
                    ),
                ),
                array(
                    'type' => 'books',
                    'id' => '2',
                    'attributes' => array(
                        'title' => 'Bar',
                        'year' => 1997,
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"data":[{"type":"books","id":"1","attributes":{"title":"Foo","year":1991}},{"type":"books","id":"2","attributes":{"title":"Bar","year":1997}}]}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

    public function testSerializingCollectionResourceWithHasOneInclude()
    {
        $this->manager->parseIncludes('author');

        $booksData = array(
            array(
                'id' => 1,
                'title' => 'Foo',
                'year' => '1991',
                '_author' => array(
                    'id' => 1,
                    'name' => 'Dave',
                ),
            ),
            array(
                'id' => 2,
                'title' => 'Bar',
                'year' => '1997',
                '_author' => array(
                    'id' => 2,
                    'name' => 'Bob',
                ),
            ),
        );

        $resource = new Collection($booksData, new JsonApiBookTransformer(), 'books');
        $scope = new Scope($this->manager, $resource);

        $expected = array(
            'data' => array(
                array(
                    'type' => 'books',
                    'id' => '1',
                    'attributes' => array(
                        'title' => 'Foo',
                        'year' => 1991,
                    ),
                    'relationships' => array(
                        'author' => array(
                            'data' => array(
                                'type' => 'people',
                                'id' => '1',
                            ),
                        ),
                    ),
                ),
                array(
                    'type' => 'books',
                    'id' => '2',
                    'attributes' => array(
                        'title' => 'Bar',
                        'year' => 1997,
                    ),
                    'relationships' => array(
                        'author' => array(
                            'data' => array(
                                'type' => 'people',
                                'id' => '2',
                            ),
                        ),
                    ),
                ),
            ),
            'included' => array(
                array(
                    'type' => 'people',
                    'id' => '1',
                    'attributes' => array(
                        'name' => 'Dave',
                    ),
                ),
                array(
                    'type' => 'people',
                    'id' => '2',
                    'attributes' => array(
                        'name' => 'Bob',
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"data":[{"type":"books","id":"1","attributes":{"title":"Foo","year":1991},"relationships":{"author":{"data":{"type":"people","id":"1"}}}},{"type":"books","id":"2","attributes":{"title":"Bar","year":1997},"relationships":{"author":{"data":{"type":"people","id":"2"}}}}],"included":[{"type":"people","id":"1","attributes":{"name":"Dave"}},{"type":"people","id":"2","attributes":{"name":"Bob"}}]}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

    public function testSerializingCollectionResourceWithHasManyInclude()
    {
        $this->manager->parseIncludes('published');

        $authorsData = array(
            array(
                'id' => 1,
                'name' => 'Dave',
                '_published' => array(
                    array(
                        'id' => 1,
                        'title' => 'Foo',
                        'year' => '1991',
                    ),
                    array(
                        'id' => 2,
                        'title' => 'Bar',
                        'year' => '2015',
                    ),
                ),
            ),
            array(
                'id' => 2,
                'name' => 'Bob',
                '_published' => array(
                    array(
                        'id' => 3,
                        'title' => 'Baz',
                        'year' => '1995',
                    ),
                    array(
                        'id' => 4,
                        'title' => 'Quux',
                        'year' => '2000',
                    ),
                ),
            ),
        );

        $resource = new Collection($authorsData, new JsonApiAuthorTransformer(), 'people');
        $scope = new Scope($this->manager, $resource);

        $expected = array(
            'data' => array(
                array(
                    'type' => 'people',
                    'id' => '1',
                    'attributes' => array(
                        'name' => 'Dave',
                    ),
                    'relationships' => array(
                        'published' => array(
                            'data' => array(
                                array(
                                    'type' => 'books',
                                    'id' => 1,
                                ),
                                array(
                                    'type' => 'books',
                                    'id' => 2,
                                ),
                            ),
                        ),
                    ),
                ),
                array(
                    'type' => 'people',
                    'id' => '2',
                    'attributes' => array(
                        'name' => 'Bob',
                    ),
                    'relationships' => array(
                        'published' => array(
                            'data' => array(
                                array(
                                    'type' => 'books',
                                    'id' => 3,
                                ),
                                array(
                                    'type' => 'books',
                                    'id' => 4,
                                ),
                            ),
                        ),
                    ),
                ),
            ),
            'included' => array(
                array(
                    'type' => 'books',
                    'id' => '1',
                    'attributes' => array(
                        'title' => 'Foo',
                        'year' => 1991,
                    ),
                ),
                array(
                    'type' => 'books',
                    'id' => '2',
                    'attributes' => array(
                        'title' => 'Bar',
                        'year' => 2015,
                    ),
                ),
                array(
                    'type' => 'books',
                    'id' => '3',
                    'attributes' => array(
                        'title' => 'Baz',
                        'year' => 1995,
                    ),
                ),
                array(
                    'type' => 'books',
                    'id' => '4',
                    'attributes' => array(
                        'title' => 'Quux',
                        'year' => 2000,
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"data":[{"type":"people","id":"1","attributes":{"name":"Dave"},"relationships":{"published":{"data":[{"type":"books","id":"1"},{"type":"books","id":"2"}]}}},{"type":"people","id":"2","attributes":{"name":"Bob"},"relationships":{"published":{"data":[{"type":"books","id":"3"},{"type":"books","id":"4"}]}}}],"included":[{"type":"books","id":"1","attributes":{"title":"Foo","year":1991}},{"type":"books","id":"2","attributes":{"title":"Bar","year":2015}},{"type":"books","id":"3","attributes":{"title":"Baz","year":1995}},{"type":"books","id":"4","attributes":{"title":"Quux","year":2000}}]}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

    public function testSerializingCollectionResourceWithMeta()
    {
        $booksData = array(
            array(
                'id' => 1,
                'title' => 'Foo',
                'year' => '1991',
                '_author' => array(
                    'name' => 'Dave',
                ),
            ),
            array(
                'id' => 2,
                'title' => 'Bar',
                'year' => '1997',
                '_author' => array(
                    'name' => 'Bob',
                ),
            ),
        );

        $resource = new Collection($booksData, new JsonApiBookTransformer(), 'books');
        $resource->setMetaValue('foo', 'bar');

        $scope = new Scope($this->manager, $resource);

        $expected = array(
            'data' => array(
                array(
                    'type' => 'books',
                    'id' => '1',
                    'attributes' => array(
                        'title' => 'Foo',
                        'year' => 1991,
                    ),
                ),
                array(
                    'type' => 'books',
                    'id' => '2',
                    'attributes' => array(
                        'title' => 'Bar',
                        'year' => 1997,
                    ),
                ),
            ),
            'meta' => array(
                'foo' => 'bar',
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"data":[{"type":"books","id":"1","attributes":{"title":"Foo","year":1991}},{"type":"books","id":"2","attributes":{"title":"Bar","year":1997}}],"meta":{"foo":"bar"}}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

    public function testSerializingCollectionResourceWithDuplicatedIncludeData()
    {
        $this->manager->parseIncludes('author');

        $booksData = array(
            array(
                'id' => 1,
                'title' => 'Foo',
                'year' => '1991',
                '_author' => array(
                    'id' => 1,
                    'name' => 'Dave',
                ),
            ),
            array(
                'id' => 2,
                'title' => 'Bar',
                'year' => '1997',
                '_author' => array(
                    'id' => 1,
                    'name' => 'Dave',
                ),
            ),
        );

        $resource = new Collection($booksData, new JsonApiBookTransformer(), 'books');
        $scope = new Scope($this->manager, $resource);

        $expected = array(
            'data' => array(
                array(
                    'type' => 'books',
                    'id' => '1',
                    'attributes' => array(
                        'title' => 'Foo',
                        'year' => 1991,
                    ),
                    'relationships' => array(
                        'author' => array(
                            'data' => array(
                                'type' => 'people',
                                'id' => '1',
                            ),
                        ),
                    ),
                ),
                array(
                    'type' => 'books',
                    'id' => '2',
                    'attributes' => array(
                        'title' => 'Bar',
                        'year' => 1997,
                    ),
                    'relationships' => array(
                        'author' => array(
                            'data' => array(
                                'type' => 'people',
                                'id' => '1',
                            ),
                        ),
                    ),
                ),
            ),
            'included' => array(
                array(
                    'type' => 'people',
                    'id' => '1',
                    'attributes' => array(
                        'name' => 'Dave',
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"data":[{"type":"books","id":"1","attributes":{"title":"Foo","year":1991},"relationships":{"author":{"data":{"type":"people","id":"1"}}}},{"type":"books","id":"2","attributes":{"title":"Bar","year":1997},"relationships":{"author":{"data":{"type":"people","id":"1"}}}}],"included":[{"type":"people","id":"1","attributes":{"name":"Dave"}}]}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

    public function testSerializingItemResourceWithNestedIncludes()
    {
        $this->manager->parseIncludes(['author', 'author.published']);

        $bookData = array(
            'id' => 1,
            'title' => 'Foo',
            'year' => '1991',
            '_author' => array(
                'id' => 1,
                'name' => 'Dave',
                '_published' => array(
                    array(
                        'id' => 1,
                        'title' => 'Foo',
                        'year' => '1991',
                    ),
                    array(
                        'id' => 2,
                        'title' => 'Bar',
                        'year' => '2015',
                    ),
                ),
            ),
        );

        $resource = new Item($bookData, new JsonApiBookTransformer(), 'books');

        $scope = new Scope($this->manager, $resource);

        $expected = array(
            'data' => array(
                'type' => 'books',
                'id' => '1',
                'attributes' => array(
                    'title' => 'Foo',
                    'year' => 1991,
                ),
                'relationships' => array(
                    'author' => array(
                        'data' => array(
                            'type' => 'people',
                            'id' => '1',
                        ),
                    ),
                ),
            ),
            'included' => array(
                array(
                    'type' => 'books',
                    'id' => '2',
                    'attributes' => array(
                        'title' => 'Bar',
                        'year' => 2015,
                    ),
                ),
                array(
                    'type' => 'people',
                    'id' => '1',
                    'attributes' => array(
                        'name' => 'Dave',
                    ),
                    'relationships' => array(
                        'published' => array(
                            'data' => array(
                                array(
                                    'type' => 'books',
                                    'id' => '1',
                                ),
                                array(
                                    'type' => 'books',
                                    'id' => '2',
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"data":{"type":"books","id":"1","attributes":{"title":"Foo","year":1991},"relationships":{"author":{"data":{"type":"people","id":"1"}}}},"included":[{"type":"books","id":"2","attributes":{"title":"Bar","year":2015}},{"type":"people","id":"1","attributes":{"name":"Dave"},"relationships":{"published":{"data":[{"type":"books","id":"1"},{"type":"books","id":"2"}]}}}]}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

    public function testSerializingItemResourceWithSelfLink()
    {
        $baseUrl = 'http://example.com';
        $this->manager->setSerializer(new JsonApiSerializer($baseUrl));

        $bookData = array(
            'id' => 1,
            'title' => 'Foo',
            'year' => '1991',
            '_author' => array(
                'id' => 1,
                'name' => 'Dave',
            ),
        );

        $resource = new Item($bookData, new JsonApiBookTransformer(), 'books');

        $scope = new Scope($this->manager, $resource);

        $expected = array(
            'data' => array(
                'type' => 'books',
                'id' => '1',
                'attributes' => array(
                    'title' => 'Foo',
                    'year' => 1991,
                ),
                'links' => array(
                    'self' => 'http://example.com/books/1',
                ),
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"data":{"type":"books","id":"1","attributes":{"title":"Foo","year":1991},"links":{"self":"http:\/\/example.com\/books\/1"}}}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

    public function testSerializingCollectionResourceWithSelfLink()
    {
        $baseUrl = 'http://example.com';
        $this->manager->setSerializer(new JsonApiSerializer($baseUrl));

        $booksData = array(
            array(
                'id' => 1,
                'title' => 'Foo',
                'year' => '1991',
                '_author' => array(
                    'id' => 1,
                    'name' => 'Dave',
                ),
            ),
            array(
                'id' => 2,
                'title' => 'Bar',
                'year' => '1997',
                '_author' => array(
                    'id' => 2,
                    'name' => 'Bob',
                ),
            ),
        );

        $resource = new Collection($booksData, new JsonApiBookTransformer(), 'books');
        $scope = new Scope($this->manager, $resource);

        $expected = array(
            'data' => array(
                array(
                    'type' => 'books',
                    'id' => '1',
                    'attributes' => array(
                        'title' => 'Foo',
                        'year' => 1991,
                    ),
                    'links' => array(
                        'self' => 'http://example.com/books/1',
                    ),
                ),
                array(
                    'type' => 'books',
                    'id' => '2',
                    'attributes' => array(
                        'title' => 'Bar',
                        'year' => 1997,
                    ),
                    'links' => array(
                        'self' => 'http://example.com/books/2',
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"data":[{"type":"books","id":"1","attributes":{"title":"Foo","year":1991},"links":{"self":"http:\/\/example.com\/books\/1"}},{"type":"books","id":"2","attributes":{"title":"Bar","year":1997},"links":{"self":"http:\/\/example.com\/books\/2"}}]}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

    public function testSerializingItemResourceWithLinksForHasOneRelationship()
    {
        $baseUrl = 'http://example.com';
        $this->manager->setSerializer(new JsonApiSerializer($baseUrl));
        $this->manager->parseIncludes('author');

        $bookData = array(
            'id' => 1,
            'title' => 'Foo',
            'year' => '1991',
            '_author' => array(
                'id' => 1,
                'name' => 'Dave',
            ),
        );

        $resource = new Item($bookData, new JsonApiBookTransformer(), 'books');

        $scope = new Scope($this->manager, $resource);

        $expected = array(
            'data' => array(
                'type' => 'books',
                'id' => '1',
                'attributes' => array(
                    'title' => 'Foo',
                    'year' => 1991,
                ),
                'relationships' => array(
                    'author' => array(
                        'links' => array(
                            'self' => 'http://example.com/books/1/relationships/author',
                            'related' => 'http://example.com/books/1/author',
                        ),
                        'data' => array(
                            'type' => 'people',
                            'id' => '1',
                        ),
                    ),
                ),
                'links' => array(
                    'self' => 'http://example.com/books/1',
                ),
            ),
            'included' => array(
                array(
                    'type' => 'people',
                    'id' => '1',
                    'attributes' => array(
                        'name' => 'Dave',
                    ),
                    'links' => array(
                        'self' => 'http://example.com/people/1',
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"data":{"type":"books","id":"1","attributes":{"title":"Foo","year":1991},"links":{"self":"http:\/\/example.com\/books\/1"},"relationships":{"author":{"links":{"self":"http:\/\/example.com\/books\/1\/relationships\/author","related":"http:\/\/example.com\/books\/1\/author"},"data":{"type":"people","id":"1"}}}},"included":[{"type":"people","id":"1","attributes":{"name":"Dave"},"links":{"self":"http:\/\/example.com\/people\/1"}}]}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

    public function testSerializingItemResourceWithLinksForHasManyRelationship()
    {
        $baseUrl = 'http://example.com';
        $this->manager->setSerializer(new JsonApiSerializer($baseUrl));
        $this->manager->parseIncludes('published');

        $authorData = array(
            'id' => 1,
            'name' => 'Dave',
            '_published' => array(
                array(
                    'id' => 1,
                    'title' => 'Foo',
                    'year' => '1991',
                ),
                array(
                    'id' => 2,
                    'title' => 'Bar',
                    'year' => '2015',
                ),
            ),
        );

        $resource = new Item($authorData, new JsonApiAuthorTransformer(), 'people');

        $scope = new Scope($this->manager, $resource);

        $expected = array(
            'data' => array(
                'type' => 'people',
                'id' => '1',
                'attributes' => array(
                    'name' => 'Dave',
                ),
                'relationships' => array(
                    'published' => array(
                        'links' => array(
                            'self' => 'http://example.com/people/1/relationships/published',
                            'related' => 'http://example.com/people/1/published',
                        ),
                        'data' => array(
                            array(
                                'type' => 'books',
                                'id' => 1,
                            ),
                            array(
                                'type' => 'books',
                                'id' => 2,
                            ),
                        ),
                    ),
                ),
                'links' => array(
                    'self' => 'http://example.com/people/1',
                ),
            ),
            'included' => array(
                array(
                    'type' => 'books',
                    'id' => '1',
                    'attributes' => array(
                        'title' => 'Foo',
                        'year' => 1991,
                    ),
                    'links' => array(
                        'self' => 'http://example.com/books/1',
                    ),
                ),
                array(
                    'type' => 'books',
                    'id' => '2',
                    'attributes' => array(
                        'title' => 'Bar',
                        'year' => 2015,
                    ),
                    'links' => array(
                        'self' => 'http://example.com/books/2',
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $scope->toArray());

        $expectedJson = '{"data":{"type":"people","id":"1","attributes":{"name":"Dave"},"links":{"self":"http:\/\/example.com\/people\/1"},"relationships":{"published":{"links":{"self":"http:\/\/example.com\/people\/1\/relationships\/published","related":"http:\/\/example.com\/people\/1\/published"},"data":[{"type":"books","id":"1"},{"type":"books","id":"2"}]}}},"included":[{"type":"books","id":"1","attributes":{"title":"Foo","year":1991},"links":{"self":"http:\/\/example.com\/books\/1"}},{"type":"books","id":"2","attributes":{"title":"Bar","year":2015},"links":{"self":"http:\/\/example.com\/books\/2"}}]}';
        $this->assertEquals($expectedJson, $scope->toJson());
    }

    public function tearDown()
    {
        Mockery::close();
    }
}
