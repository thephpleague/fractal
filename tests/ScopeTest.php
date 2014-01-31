<?php namespace League\Fractal\Test;

use League\Fractal\Cursor\Cursor;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Manager;
use League\Fractal\Scope;
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
        $this->assertEquals($scope->getCurrentScope(), 'book');

        $childScope = $scope->embedChildScope('author', $resource);
        $this->assertEquals('author', $childScope->getCurrentScope());

        $grandChildScope = $childScope->embedChildScope('profile', $resource);
        $this->assertEquals('profile', $grandChildScope->getCurrentScope());
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
        $manager->setRequestedScopes(array('foo', 'bar', 'baz.bart'));

        $scope = new Scope($manager, Mockery::mock('League\Fractal\Resource\ResourceInterface'));

        $this->assertTrue($scope->isRequested('foo'));
        $this->assertTrue($scope->isRequested('bar'));
        $this->assertTrue($scope->isRequested('baz'));
        $this->assertTrue($scope->isRequested('baz.bart'));
        $this->assertFalse($scope->isRequested('nope'));

        $childScope = $scope->embedChildScope('baz', Mockery::mock('League\Fractal\Resource\ResourceInterface'));
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
        $manager->setRequestedScopes(array('book'));

        $resource = Mockery::mock('League\Fractal\Resource\ResourceInterface', array(
            array('bar' => 'baz'),
            function() {}
        ))->makePartial();

        $scope = new Scope($manager, $resource);
        $scope->toArray();
    }

    public function testToArrayWithEmbeds()
    {
        $manager = new Manager();
        $manager->setRequestedScopes(array('book'));

        $transformer = Mockery::mock('League\Fractal\TransformerAbstract[getAvailableEmbeds,transform]');
        $transformer->shouldReceive('getAvailableEmbeds')->once()->andReturn(array('book'));
        $transformer->shouldReceive('transform')->once()->andReturnUsing(function(array $data) {
            return $data;
        });

        $resource = new Item(array('bar' => 'baz'), $transformer);

        $scope = new Scope($manager, $resource);

        $this->assertEquals(array('data' => array('bar' => 'baz'), 'embeds' => array('book')), $scope->toArray());
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
        $transformer->shouldReceive('processEmbededResources')->once()->andReturn(array());
        $transformer->shouldReceive('getAvailableEmbeds')->once()->andReturn(null);

        $resource = new Item($this->simpleItem, $transformer);
        $scope = $manager->createData($resource);
        $this->assertEquals(array('data' => $this->simpleItem), $scope->toArray());
    }

    public function testRunAppropriateTransformerWithCollection()
    {
        $manager = new Manager();

        $transformer = Mockery::mock('League\Fractal\TransformerAbstract');
        $transformer->shouldReceive('transform')->once()->andReturn(array('foo' => 'bar'));
        $transformer->shouldReceive('processEmbededResources')->once()->andReturn(array());
        $transformer->shouldReceive('getAvailableEmbeds')->once()->andReturn(null);

        $resource = new Collection(array(array('foo' => 'bar')), $transformer);
        $scope = $manager->createData($resource);

        $this->assertEquals(array('data' => array(array('foo' => 'bar'))), $scope->toArray());
    }

    /**
     * @covers League\Fractal\Scope::runAppropriateTransformer
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Argument $resource should be an instance of Resource\Item or Resource\Collection
     */
    public function testCreateDataWithClassFuckKnows()
    {
        $manager = new Manager();

        $transformer = Mockery::mock('League\Fractal\TransformerAbstract')->makePartial();

        $resource = Mockery::mock('League\Fractal\Resource\ResourceInterface', array($this->simpleItem, $transformer))->makePartial();
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
            'data' => array(
                array(
                    'foo' => 'bar',
                    'baz' => 'ban',
                ),
            ),
        );

        $this->assertEquals($expectedOutput, $rootScope->toArray());
    }

    public function testCursorOutput()
    {
        $manager = new Manager();

        $collection = new Collection(array(array('foo' => 'bar', 'baz' => 'ban')), function (array $data) {
            return $data;
        });

        $cursor = new Cursor(0, 'ban', 2);

        $collection->setCursor($cursor);

        $rootScope = $manager->createData($collection);

        $expectedOutput = array(
            'cursor' => array(
                'current' => 0,
                'next' => 'ban',
                'count' => 2,
            ),
            'data' => array(
                array(
                    'foo' => 'bar',
                    'baz' => 'ban',
                ),
            ),
        );

        $this->assertEquals($expectedOutput, $rootScope->toArray());
    }

    public function tearDown()
    {
        Mockery::close();
    }

}
