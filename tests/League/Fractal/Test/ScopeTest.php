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

        $scope = new Scope($manager, m::mock('League\Fractal\Resource\ResourceInterface'));
        
        $this->assertTrue($scope->isRequested('foo'));
        $this->assertTrue($scope->isRequested('bar'));
        $this->assertTrue($scope->isRequested('baz.bart'));
        $this->assertFalse($scope->isRequested('nope'));

        $childScope = $scope->embedChildScope('baz', m::mock('League\Fractal\Resource\ResourceInterface'));
        $this->assertTrue($childScope->isRequested('bart'));
        $this->assertFalse($childScope->isRequested('foo'));
        $this->assertFalse($childScope->isRequested('bar'));
        $this->assertFalse($childScope->isRequested('baz'));
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


    public function tearDown()
    {
        m::close();
    }

}
