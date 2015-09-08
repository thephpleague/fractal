<?php namespace League\Fractal\Test;

use League\Fractal\Manager;
use League\Fractal\Pagination\Cursor;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Scope;
use League\Fractal\Serializer\ArraySerializer;
use League\Fractal\Test\Stub\Transformer\DefaultIncludeBookTransformer;
use Mockery;

class ScopeTest extends \PHPUnit_Framework_TestCase
{
    protected $simpleItem = ['foo' => 'bar'];
    protected $simpleCollection = [['foo' => 'bar']];

    public function testEmbedChildScope()
    {
        $manager = new Manager();

        $resource = new Item(['foo' => 'bar'], function () {
        });

        $scope = new Scope($manager, $resource, 'book');
        $this->assertSame($scope->getScopeIdentifier(), 'book');
        $childScope = $scope->embedChildScope('author', $resource);

        $this->assertInstanceOf('League\Fractal\Scope', $childScope);
    }

    public function testGetManager()
    {
        $resource = new Item(['foo' => 'bar'], function () {
        });

        $scope = new Scope(new Manager(), $resource, 'book');

        $this->assertInstanceOf('League\Fractal\Manager', $scope->getManager());
    }

    public function testGetResource()
    {
        $resource = new Item(['foo' => 'bar'], function () {
        });

        $scope = new Scope(new Manager(), $resource, 'book');

        $this->assertInstanceOf('League\Fractal\Resource\ResourceAbstract', $scope->getResource());
        $this->assertInstanceOf('League\Fractal\Resource\Item', $scope->getResource());
    }

    /**
     * @covers League\Fractal\Scope::toArray
     */
    public function testToArray()
    {
        $manager = new Manager();

        $resource = new Item(['foo' => 'bar'], function ($data) {
            return $data;
        });

        $scope = new Scope($manager, $resource);


        $this->assertSame(['data' => ['foo' => 'bar']], $scope->toArray());
    }

    public function testToJson()
    {
        $data = [
            'foo' => 'bar',
        ];

        $manager = new Manager();

        $resource = new Item($data, function ($data) {
            return $data;
        });

        $scope = new Scope($manager, $resource);

        $expected = json_encode([
            'data' => $data,
        ]);

        $this->assertSame($expected, $scope->toJson());
    }

    public function testGetCurrentScope()
    {
        $manager = new Manager();

        $resource = new Item(['name' => 'Larry Ullman'], function () {
        });

        $scope = new Scope($manager, $resource, 'book');
        $this->assertSame('book', $scope->getScopeIdentifier());

        $childScope = $scope->embedChildScope('author', $resource);
        $this->assertSame('author', $childScope->getScopeIdentifier());

        $grandChildScope = $childScope->embedChildScope('profile', $resource);
        $this->assertSame('profile', $grandChildScope->getScopeIdentifier());
    }

    public function testGetIdentifier()
    {
        $manager = new Manager();

        $resource = new Item(['name' => 'Larry Ullman'], function () {
        });

        $scope = new Scope($manager, $resource, 'book');
        $this->assertSame('book', $scope->getIdentifier());

        $childScope = $scope->embedChildScope('author', $resource);
        $this->assertSame('book.author', $childScope->getIdentifier());

        $grandChildScope = $childScope->embedChildScope('profile', $resource);
        $this->assertSame('book.author.profile', $grandChildScope->getIdentifier());
    }

    public function testGetParentScopes()
    {
        $manager = new Manager();

        $resource = new Item(['name' => 'Larry Ullman'], function () {
        });

        $scope = new Scope($manager, $resource, 'book');

        $childScope = $scope->embedChildScope('author', $resource);

        $this->assertSame(['book'], $childScope->getParentScopes());

        $grandChildScope = $childScope->embedChildScope('profile', $resource);
        $this->assertSame(['book', 'author'], $grandChildScope->getParentScopes());
    }

    public function testIsRequested()
    {
        $manager = new Manager();
        $manager->parseIncludes(['foo', 'bar', 'baz.bart']);

        $scope = new Scope($manager, Mockery::mock('League\Fractal\Resource\ResourceAbstract'));

        $this->assertTrue($scope->isRequested('foo'));
        $this->assertTrue($scope->isRequested('bar'));
        $this->assertTrue($scope->isRequested('baz'));
        $this->assertTrue($scope->isRequested('baz.bart'));
        $this->assertFalse($scope->isRequested('nope'));

        $childScope = $scope->embedChildScope('baz', Mockery::mock('League\Fractal\Resource\ResourceAbstract'));
        $this->assertTrue($childScope->isRequested('bart'));
        $this->assertFalse($childScope->isRequested('foo'));
        $this->assertFalse($childScope->isRequested('bar'));
        $this->assertFalse($childScope->isRequested('baz'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testScopeRequiresConcreteImplementation()
    {
        $manager = new Manager();
        $manager->parseIncludes('book');

        $resource = Mockery::mock('League\Fractal\Resource\ResourceAbstract', [
            ['bar' => 'baz'],
            function () {},
        ])->makePartial();

        $scope = new Scope($manager, $resource);
        $scope->toArray();
    }

    public function testToArrayWithIncludes()
    {
        $manager = new Manager();
        $manager->parseIncludes('book');

        $transformer = Mockery::mock('League\Fractal\TransformerAbstract')->makePartial();
        $transformer->shouldReceive('getAvailableIncludes')->twice()->andReturn(['book']);
        $transformer->shouldReceive('transform')->once()->andReturnUsing(function (array $data) {
            return $data;
        });
        $transformer->shouldReceive('processIncludedResources')->once()->andReturn(['book' => ['yin' => 'yang']]);

        $resource = new Item(['bar' => 'baz'], $transformer);

        $scope = new Scope($manager, $resource);


        $this->assertSame(['data' => ['bar' => 'baz', 'book' => ['yin' => 'yang']]], $scope->toArray());
    }

    public function testToArrayWithSideloadedIncludes()
    {
        $serializer = Mockery::mock('League\Fractal\Serializer\ArraySerializer')->makePartial();
        $serializer->shouldReceive('sideloadIncludes')->andReturn(true);
        $serializer->shouldReceive('item')->andReturnUsing(function ($key, $data) {
            return ['data' => $data];
        });
        $serializer->shouldReceive('includedData')->andReturnUsing(function ($key, $data) {
            return ['sideloaded' => array_pop($data)];
        });

        $manager = new Manager();
        $manager->parseIncludes('book');
        $manager->setSerializer($serializer);

        $transformer = Mockery::mock('League\Fractal\TransformerAbstract')->makePartial();
        $transformer->shouldReceive('getAvailableIncludes')->twice()->andReturn(['book']);
        $transformer->shouldReceive('transform')->once()->andReturnUsing(function (array $data) {
            return $data;
        });
        $transformer->shouldReceive('processIncludedResources')->once()->andReturn(['book' => ['yin' => 'yang']]);

        $resource = new Item(['bar' => 'baz'], $transformer);

        $scope = new Scope($manager, $resource);

        $expected = [
            'data' => ['bar' => 'baz'],
            'sideloaded' => ['book' => ['yin' => 'yang']],
        ];

        $this->assertSame($expected, $scope->toArray());
    }

    public function testPushParentScope()
    {
        $manager = new Manager();

        $resource = new Item(['name' => 'Larry Ullman'], function () {
        });

        $scope = new Scope($manager, $resource);

        $this->assertSame(1, $scope->pushParentScope('book'));
        $this->assertSame(2, $scope->pushParentScope('author'));
        $this->assertSame(3, $scope->pushParentScope('profile'));


        $this->assertSame(['book', 'author', 'profile'], $scope->getParentScopes());
    }

    public function testRunAppropriateTransformerWithItem()
    {
        $manager = new Manager();

        $transformer = Mockery::mock('League\Fractal\TransformerAbstract');
        $transformer->shouldReceive('transform')->once()->andReturn($this->simpleItem);
        $transformer->shouldReceive('getAvailableIncludes')->once()->andReturn([]);
        $transformer->shouldReceive('getDefaultIncludes')->once()->andReturn([]);

        $resource = new Item($this->simpleItem, $transformer);
        $scope = $manager->createData($resource);

        $this->assertSame(['data' => $this->simpleItem], $scope->toArray());
    }

    public function testRunAppropriateTransformerWithCollection()
    {
        $manager = new Manager();

        $transformer = Mockery::mock('League\Fractal\TransformerAbstract');
        $transformer->shouldReceive('transform')->once()->andReturn(['foo' => 'bar']);
        $transformer->shouldReceive('getAvailableIncludes')->once()->andReturn([]);
        $transformer->shouldReceive('getDefaultIncludes')->once()->andReturn([]);

        $resource = new Collection([['foo' => 'bar']], $transformer);
        $scope = $manager->createData($resource);

        $this->assertSame(['data' => [['foo' => 'bar']]], $scope->toArray());

    }

    /**
     * @covers League\Fractal\Scope::executeResourceTransformers
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Argument $resource should be an instance of League\Fractal\Resource\Item or League\Fractal\Resource\Collection
     */
    public function testCreateDataWithClassFuckKnows()
    {
        $manager = new Manager();

        $transformer = Mockery::mock('League\Fractal\TransformerAbstract')->makePartial();

        $resource = Mockery::mock('League\Fractal\Resource\ResourceAbstract', [$this->simpleItem, $transformer])->makePartial();
        $scope = $manager->createData($resource);
        $scope->toArray();
    }

    public function testPaginatorOutput()
    {
        $manager = new Manager();

        $collection = new Collection([['foo' => 'bar', 'baz' => 'ban']], function (array $data) {
            return $data;
        });

        $paginator = Mockery::mock('League\Fractal\Pagination\IlluminatePaginatorAdapter')->makePartial();

        $total = 100;
        $perPage = $count = 5;
        $currentPage = 2;
        $lastPage = 20;

        $paginator->shouldReceive('getTotal')->once()->andReturn($total);
        $paginator->shouldReceive('getCount')->once()->andReturn($count);
        $paginator->shouldReceive('getPerPage')->once()->andReturn($perPage);
        $paginator->shouldReceive('getCurrentPage')->once()->andReturn($currentPage);
        $paginator->shouldReceive('getLastPage')->once()->andReturn($lastPage);
        $paginator->shouldReceive('getUrl')->times(2)->andReturnUsing(function ($page) {
            return 'http://example.com/foo?page='.$page;
        });

        $collection->setPaginator($paginator);

        $rootScope = $manager->createData($collection);


        $expectedOutput = [
            'data' => [
                [
                    'foo' => 'bar',
                    'baz' => 'ban',
                ],
            ],
            'meta' => [
                'pagination' => [
                    'total' => $total,
                    'count' => $count,
                    'per_page' => $perPage,
                    'current_page' => $currentPage,
                    'total_pages' => $lastPage,
                    'links' => [
                        'previous' => 'http://example.com/foo?page=1',
                        'next' => 'http://example.com/foo?page=3',

                    ],
                ],
            ],
        ];

        $this->assertSame($expectedOutput, $rootScope->toArray());
    }

    public function testCursorOutput()
    {
        $manager = new Manager();

        $inputData = [
            [
                'foo' => 'bar',
                'baz' => 'ban',
            ],
        ];

        $collection = new Collection($inputData, function (array $data) {
            return $data;
        });

        $cursor = new Cursor(0, 'ban', 'ban', 2);

        $collection->setCursor($cursor);

        $rootScope = $manager->createData($collection);


        $expectedOutput = [
            'data' => $inputData,
            'meta' => [
                'cursor' => [
                    'current' => 0,
                    'prev' => 'ban',
                    'next' => 'ban',
                    'count' => 2,

                ],
            ],
        ];

        $this->assertSame($expectedOutput, $rootScope->toArray());
    }

    public function testDefaultIncludeSuccess()
    {
        $manager = new Manager();
        $manager->setSerializer(new ArraySerializer());

        // Send this stub junk, it has a specific format anyhow
        $resource = new Item([], new DefaultIncludeBookTransformer());

        // Try without metadata
        $scope = new Scope($manager, $resource);

        $expected = [
            'a' => 'b',
            'author' => [
                'c' => 'd',
            ],
        ];

        $this->assertSame($expected, $scope->toArray());
    }

    public function tearDown()
    {
        Mockery::close();
    }
}
