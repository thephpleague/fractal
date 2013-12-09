<?php namespace League\Fractal\Test;

use League\Fractal\ItemResource;
use League\Fractal\ResourceManager;
use League\Fractal\Scope;
use Mockery as m;

class ScopeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers League\Fractal\Scope::embedChildScope
     */
    public function testEmbedChildScope()
    {
        $manager = new ResourceManager();
        $scope = new Scope($manager, 'book');
        $this->assertEquals($scope->getCurrentScope(), 'book');
        $resource = new ItemResource(array('name' => 'Larry Ullman'), function() {
        });
        $childScope = $scope->embedChildScope('author', $resource);

        $this->assertInstanceOf('League\Fractal\Scope', $childScope);
    }

    /**
     * @covers League\Fractal\Scope::embedChildScope
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Argument $resource should be an 
     *   instance of ItemResource, CollectionResource or PaginatorResource
     */
    public function testEmbedChildScopeInvalidResource()
    {
        $manager = new ResourceManager();
        $scope = new Scope($manager, 'book');
        $scope->embedChildScope('author', array('not', 'a', 'resource'));
    }

    /**
     * @covers League\Fractal\Scope::setCurrentData
     */
    public function testSetCurrentData()
    {
        $manager = new ResourceManager();
        $scope = new Scope($manager, 'book');
        $this->assertInstanceOf('League\Fractal\Scope', $scope->setCurrentData(array('foo' => 'bar')));
    }

    /**
     * @covers League\Fractal\Scope::getCurrentData
     */
    public function testGetCurrentData()
    {
        $manager = new ResourceManager();
        $scope = new Scope($manager, 'book');
        $scope->setCurrentData(array('foo' => 'bar'));
        $this->assertEquals($scope->getCurrentData(), array('foo' => 'bar'));
    }

    /**
     * @covers League\Fractal\Scope::getCurrentScope
     */
    public function testGetCurrentScope()
    {
        $manager = new ResourceManager();
        $scope = new Scope($manager, 'book');
        $this->assertEquals($scope->getCurrentScope(), 'book');
        $resource = new ItemResource(array('name' => 'Larry Ullman'), function() {
        });
        $childScope = $scope->embedChildScope('author', $resource);
        $this->assertEquals($childScope->getCurrentScope(), 'author');

        $grandChildScope = $childScope->embedChildScope('profile', $resource);
        $this->assertEquals($grandChildScope->getCurrentScope(), 'profile');
    }

    /**
     * @covers League\Fractal\Scope::getParentScopes
     */
    public function testGetParentScopes()
    {
        $manager = new ResourceManager();
        $scope = new Scope($manager, 'book');
        $resource = new ItemResource(array('name' => 'Larry Ullman'), function() {
        });
        
        $childScope = $scope->embedChildScope('author', $resource);
        $this->assertEquals($childScope->getParentScopes(), array('book'));

        $grandChildScope = $childScope->embedChildScope('profile', $resource);
        $this->assertEquals($grandChildScope->getParentScopes(), array('book', 'author'));
    }

    /**
     * @covers League\Fractal\Scope::isRequested
     */
    public function testIsRequested()
    {
        $manager = new ResourceManager();
        $manager->setRequestedScopes(array('foo', 'bar', 'baz.bart'));

        $scope = new Scope($manager, 'book');
        $resource = new ItemResource(array('name' => 'Larry Ullman'), function() {
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

    /**
     * @covers League\Fractal\Scope::pushParentScope
     */
    public function testPushParentScope()
    {
        $manager = new ResourceManager();
        $scope = new Scope($manager);

        $this->assertEquals($scope->pushParentScope('book'), 1);
        $this->assertEquals($scope->pushParentScope('author'), 2);
        $this->assertEquals($scope->pushParentScope('profile'), 3);
        
        $this->assertEquals($scope->getParentScopes(), array('book', 'author', 'profile'));
    }


    public function tearDown()
    {
        m::close();
    }

}
