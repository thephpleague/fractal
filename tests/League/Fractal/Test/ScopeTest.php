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

        $resource = new ItemResource(array('name' => 'Larry Ullman'), function (array $data) {
            return $data;
        });

        $childScope = $scope->embedChildScope('author', $resource);
        $this->assertEquals($childScope->getCurrentScope(), 'author');
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
     * @covers League\Fractal\Scope::getCurrentData
     */
    // public function testGetCurrentScope()
    // {
    //     $manager = new ResourceManager();
    //     $scope = new Scope($manager, 'book');


    //     $scope->embedChildScope($scope);

    //     $this->assertEquals($scope->getCurrentScope(), 'book');
    // }
}
