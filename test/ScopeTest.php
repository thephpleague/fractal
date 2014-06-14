<?php namespace League\Fractal\Test;

use League\Fractal\Pagination\Cursor;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Manager;
use League\Fractal\Scope;
use League\Fractal\Serializer\ArraySerializer;
use League\Fractal\Test\Stub\Transformer\DefaultIncludeBookTransformer;
use Mockery;

class ScopeTest extends \PHPUnit_Framework_TestCase
{
    protected $simpleItem = array('foo' => 'bar');
    protected $simpleCollection = array(array('foo' => 'bar'));

    public function testEmbedChildScope()
    {
        $manager = new Manager();

        $resource = new Item(array('foo' => 'bar'), function () {
        });

        $scope = new Scope($manager, $resource, 'book');
        $this->assertEquals($scope->getCurrentScope(), 'book');
        $childScope = $scope->embedChildScope('author', $resource);

        $this->assertInstanceOf('League\Fractal\Scope', $childScope);
    }

    public function testGetManager()
    {
        $resource = new Item(array('foo' => 'bar'), function () {
        });

        $scope = new Scope(new Manager(), $resource, 'book');

        $this->assertInstanceOf('League\Fractal\Manager', $scope->getManager());
    }

    /**
     * @covers League\Fractal\Scope::toArray
     */
    public function testToArray()
    {
        $manager = new Manager();

        $resource = new Item(array('foo' => 'bar'), function ($data) {
            return $data;
        });

        $scope = new Scope($manager, $resource);

        $this->assertEquals(array('data' => array('foo' => 'bar')), $scope->toArray());
    }

    public function testGetCurrentScope()
    {
        $manager = new Manager();

        $resource = new Item(array('name' => 'Larry Ullman'), function () {
        });

        $scope = new Scope($manager, $resource, 'book');
        $this->assertEquals('book', $scope->getCurrentScope());

        $childScope = $scope->embedChildScope('author', $resource);
        $this->assertEquals('author', $childScope->getCurrentScope());

        $grandChildScope = $childScope->embedChildScope('profile', $resource);
        $this->assertEquals('profile', $grandChildScope->getCurrentScope());
    }

    public function testGetIdentifier()
    {
        $manager = new Manager();

        $resource = new Item(array('name' => 'Larry Ullman'), function () {
        });

        $scope = new Scope($manager, $resource, 'book');
        $this->assertEquals('book', $scope->getIdentifier());

        $childScope = $scope->embedChildScope('author', $resource);
        $this->assertEquals('book.author', $childScope->getIdentifier());

        $grandChildScope = $childScope->embedChildScope('profile', $resource);
        $this->assertEquals('book.author.profile', $grandChildScope->getIdentifier());
    }

    public function testGetParentScopes()
    {
        $manager = new Manager();

        $resource = new Item(array('name' => 'Larry Ullman'), function () {
        });

        $scope = new Scope($manager, $resource, 'book');

        $childScope = $scope->embedChildScope('author', $resource);
        $this->assertEquals(array('book'), $childScope->getParentScopes());

        $grandChildScope = $childScope->embedChildScope('profile', $resource);
        $this->assertEquals(array('book', 'author'), $grandChildScope->getParentScopes());
    }

    public function testIsRequested()
    {
        $manager = new Manager();
        $manager->parseIncludes(array('foo', 'bar', 'baz.bart'));

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

        $resource = Mockery::mock('League\Fractal\Resource\ResourceAbstract', array(
            array('bar' => 'baz'),
            function() {}
        ))->makePartial();

        $scope = new Scope($manager, $resource);
        $scope->toArray();
    }

    public function testToArrayWithIncludes()
    {
        $manager = new Manager();
        $manager->parseIncludes('book');

        $transformer = Mockery::mock('League\Fractal\TransformerAbstract')->makePartial();
        $transformer->shouldReceive('getAvailableIncludes')->twice()->andReturn(array('book'));
        $transformer->shouldReceive('transform')->once()->andReturnUsing(function (array $data) {
            return $data;
        });
        $transformer->shouldReceive('processIncludedResources')->once()->andReturn(array('book' => array('yin' => 'yang')));

        $resource = new Item(array('bar' => 'baz'), $transformer);

        $scope = new Scope($manager, $resource);

        $this->assertEquals(array('data' => array('bar' => 'baz', 'book' => array('yin' => 'yang'))), $scope->toArray());
    }

    public function testToArrayWithSideloadedIncludes()
    {
        $serializer = Mockery::mock('League\Fractal\Serializer\ArraySerializer[sideloadIncludes,serializeData,serializeIncludedData]');
        $serializer->shouldReceive('sideloadIncludes')->andReturn(true);
        $serializer->shouldReceive('serializeData')->andReturnUsing(function ($key, $data) {
            return array('data' => $data);
        });
        $serializer->shouldReceive('serializeIncludedData')->andReturnUsing(function ($key, $data) {
            return array('sideloaded' => array_pop($data));
        });

        $manager = new Manager();
        $manager->parseIncludes('book');
        $manager->setSerializer($serializer);

        $transformer = Mockery::mock('League\Fractal\TransformerAbstract')->makePartial();
        $transformer->shouldReceive('getAvailableIncludes')->twice()->andReturn(array('book'));
        $transformer->shouldReceive('transform')->once()->andReturnUsing(function (array $data) {
            return $data;
        });
        $transformer->shouldReceive('processIncludedResources')->once()->andReturn(array('book' => array('yin' => 'yang')));

        $resource = new Item(array('bar' => 'baz'), $transformer);

        $scope = new Scope($manager, $resource);

        $expected = array(
            'data' => array('bar' => 'baz'),
            'sideloaded' => array('book' => array('yin' => 'yang')),
        );

        $this->assertEquals($expected, $scope->toArray());
    }


    public function testPushParentScope()
    {
        $manager = new Manager();

        $resource = new Item(array('name' => 'Larry Ullman'), function () {
        });

        $scope = new Scope($manager, $resource);

        $this->assertEquals(1, $scope->pushParentScope('book'));
        $this->assertEquals(2, $scope->pushParentScope('author'));
        $this->assertEquals(3, $scope->pushParentScope('profile'));

        $this->assertEquals(array('book', 'author', 'profile'), $scope->getParentScopes());
    }

    public function testRunAppropriateTransformerWithItem()
    {
        $manager = new Manager();

        $transformer = Mockery::mock('League\Fractal\TransformerAbstract');
        $transformer->shouldReceive('transform')->once()->andReturn($this->simpleItem);
        $transformer->shouldReceive('getAvailableIncludes')->once()->andReturn(array());
        $transformer->shouldReceive('getDefaultIncludes')->once()->andReturn(array());

        $resource = new Item($this->simpleItem, $transformer);
        $scope = $manager->createData($resource);
        $this->assertEquals(array('data' => $this->simpleItem), $scope->toArray());
    }

    public function testRunAppropriateTransformerWithCollection()
    {
        $manager = new Manager();

        $transformer = Mockery::mock('League\Fractal\TransformerAbstract');
        $transformer->shouldReceive('transform')->once()->andReturn(array('foo' => 'bar'));
        $transformer->shouldReceive('getAvailableIncludes')->once()->andReturn(array());
        $transformer->shouldReceive('getDefaultIncludes')->once()->andReturn(array());

        $resource = new Collection(array(array('foo' => 'bar')), $transformer);
        $scope = $manager->createData($resource);

        $this->assertEquals(array('data' => array(array('foo' => 'bar'))), $scope->toArray());
    }

    /**
     * @covers League\Fractal\Scope::executeResourceTransformers
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Argument $resource should be an instance of Resource\Item or Resource\Collection
     */
    public function testCreateDataWithClassFuckKnows()
    {
        $manager = new Manager();

        $transformer = Mockery::mock('League\Fractal\TransformerAbstract')->makePartial();

        $resource = Mockery::mock('League\Fractal\Resource\ResourceAbstract', array($this->simpleItem, $transformer))->makePartial();
        $scope = $manager->createData($resource);
        $scope->toArray();
    }


    public function testPaginatorOutput()
    {
        $manager = new Manager();

        $collection = new Collection(array(array('foo' => 'bar', 'baz' => 'ban')), function (array $data) {
            return $data;
        });

        $paginator = Mockery::mock('League\Fractal\Pagination\IlluminatePaginatorAdapter')->makePartial();


        $total = 100;
        $perPage = $count = 5;
        $currentPage = 2;
        $lastPage = 20;

        $paginator->shouldReceive('getTotal')->once()->andReturn($total);
        $paginator->shouldReceive('count')->once()->andReturn($count);
        $paginator->shouldReceive('getPerPage')->once()->andReturn($perPage);
        $paginator->shouldReceive('getCurrentPage')->once()->andReturn($currentPage);
        $paginator->shouldReceive('getLastPage')->once()->andReturn($lastPage);
        $paginator->shouldReceive('getUrl')->times(2)->andReturnUsing(function ($page) {
            return 'http://example.com/foo?page='.$page;
        });

        $collection->setPaginator($paginator);

        $rootScope = $manager->createData($collection);

        $expectedOutput = array(
            'meta' => array(
                'pagination' => array(
                    'total' => $total,
                    'count' => $count,
                    'per_page' => $perPage,
                    'current_page' => $currentPage,
                    'total_pages' => $lastPage,
                    'links' => array(
                        'previous' => 'http://example.com/foo?page=1',
                        'next' => 'http://example.com/foo?page=3',
                    ),
                ),
            ),
            'data' => array(
                array(
                    'foo' => 'bar',
                    'baz' => 'ban',
                ),
            )
        );

        $this->assertEquals($expectedOutput, $rootScope->toArray());
    }

    public function testCursorOutput()
    {
        $manager = new Manager();

        $inputData = array(
            array(
                'foo' => 'bar',
                'baz' => 'ban',
            )
        );

        $collection = new Collection($inputData, function (array $data) {
            return $data;
        });

        $cursor = new Cursor(0, 'ban', 'ban', 2);

        $collection->setCursor($cursor);

        $rootScope = $manager->createData($collection);

        $expectedOutput = array(
            'meta' => array(
                'cursor' => array(
                    'current' => 0,
                    'prev' => 'ban',
                    'next' => 'ban',
                    'count' => 2,
                ),
            ),
            'data' => $inputData,
        );

        $this->assertEquals($expectedOutput, $rootScope->toArray());
    }

    public function testDefaultIncludeSuccess()
    {
        $manager = new Manager();
        $manager->setSerializer(new ArraySerializer());

        // Send this stub junk, it has a specific format anyhow
        $resource = new Item(array(), new DefaultIncludeBookTransformer());

        // Try without metadata
        $scope = new Scope($manager, $resource);

        $expected = array(
            'a' => 'b',
            'author' => array(
                'c' => 'd',
            ),
        );

        $this->assertEquals($expected, $scope->toArray());
    }

    public function tearDown()
    {
        Mockery::close();
    }
}
