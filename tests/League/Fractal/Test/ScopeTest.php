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
        $this->assertEquals($scope->getCurrentScope(), 'book');
        $resource = new ItemResource(array('name' => 'Larry Ullman'), function() {
        });
        
        $childScope = $scope->embedChildScope('author', $resource);
        $this->assertEquals($childScope->getParentScopes(), array('book'));

        $grandChildScope = $childScope->embedChildScope('profile', $resource);
        $this->assertEquals($grandChildScope->getParentScopes(), array('book', 'author'));
    }

    public function tearDown()
    {
        m::close();
    }

}
