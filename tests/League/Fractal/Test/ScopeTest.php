<?php namespace League\Fractal\Test;

use League\Fractal\Resource\Item;
use League\Fractal\Manager;
use League\Fractal\Scope;
use Mockery as m;

class ScopeTest extends \PHPUnit_Framework_TestCase
{
    public function testEmbedChildScope()
    {
        $manager = new Manager();
        $scope = new Scope($manager, 'book');
        $this->assertEquals($scope->getCurrentScope(), 'book');
        $resource = new Item(array('name' => 'Larry Ullman'), function () {
        });
        $childScope = $scope->embedChildScope('author', $resource);

        $this->assertInstanceOf('League\Fractal\Scope', $childScope);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Argument $resource should be an 
     *   instance of Item, CollectionResource or PaginatorResource
     */
    public function testEmbedChildScopeInvalidResource()
    {
        $manager = new Manager();
        $scope = new Scope($manager, 'book');
        $scope->embedChildScope('author', array('not', 'a', 'resource'));
    }

    /**
     * @covers League\Fractal\Scope::setCurrentData
     */
    public function testSetCurrentData()
    {
        $manager = new Manager();
        $scope = new Scope($manager, 'book');
        $this->assertInstanceOf('League\Fractal\Scope', $scope->setCurrentData(array('foo' => 'bar')));
    }

    /**
     * @covers League\Fractal\Scope::getCurrentData
     */
    public function testGetCurrentData()
    {
        $manager = new Manager();
        $scope = new Scope($manager, 'book');
        $scope->setCurrentData(array('foo' => 'bar'));
        $this->assertEquals(array('foo' => 'bar'), $scope->getCurrentData());
    }

    /**
     * @covers League\Fractal\Scope::toArray
     */
    public function testToArray()
    {
        $manager = new Manager();
        $scope = new Scope($manager, 'book');
        $scope->setCurrentData(array('foo' => 'bar'));
        $this->assertEquals(array('data' => array('foo' => 'bar')), $scope->toArray());
    }

    public function testGetCurrentScope()
    {
        $manager = new Manager();
        $scope = new Scope($manager, 'book');
        $this->assertEquals($scope->getCurrentScope(), 'book');
        $resource = new Item(array('name' => 'Larry Ullman'), function () {
        });
        $childScope = $scope->embedChildScope('author', $resource);
        $this->assertEquals('author', $childScope->getCurrentScope());

        $grandChildScope = $childScope->embedChildScope('profile', $resource);
        $this->assertEquals('profile', $grandChildScope->getCurrentScope());
    }

    public function testGetParentScopes()
    {
        $manager = new Manager();
        $scope = new Scope($manager, 'book');
        $resource = new Item(array('name' => 'Larry Ullman'), function () {
        });
        
        $childScope = $scope->embedChildScope('author', $resource);
        $this->assertEquals(array('book'), $childScope->getParentScopes());

        $grandChildScope = $childScope->embedChildScope('profile', $resource);
        $this->assertEquals(array('book', 'author'), $grandChildScope->getParentScopes());
    }

    public function testIsRequested()
    {
        $manager = new Manager();
        $manager->setRequestedScopes(array('foo', 'bar', 'baz.bart'));

        $scope = new Scope($manager, 'book');
        $resource = new Item(array('name' => 'Larry Ullman'), function () {
        });
        
        $this->assertTrue($scope->isRequested('foo'));
        $this->assertTrue($scope->isRequested('bar'));
        $this->assertTrue($scope->isRequested('baz.bart'));
        $this->assertFalse($scope->isRequested('nope'));

        $childScope = $scope->embedChildScope('baz', $resource);
        $this->assertTrue($childScope->isRequested('bart'));
        $this->assertFalse($childScope->isRequested('foo'));
        $this->assertFalse($childScope->isRequested('bar'));
        $this->assertFalse($childScope->isRequested('baz'));
    }

    public function testPushParentScope()
    {
        $manager = new Manager();
        $scope = new Scope($manager);

        $this->assertEquals(1, $scope->pushParentScope('book'));
        $this->assertEquals(2, $scope->pushParentScope('author'));
        $this->assertEquals(3, $scope->pushParentScope('profile'));
        
        $this->assertEquals(array('book', 'author', 'profile'), $scope->getParentScopes());
    }


    public function tearDown()
    {
        m::close();
    }

}
