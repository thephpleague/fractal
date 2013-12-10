<?php namespace League\Fractal\Test;

use League\Fractal\CollectionResource;
use League\Fractal\ItemResource;
use League\Fractal\PaginatorResource;
use League\Fractal\ResourceManager;
use Mockery as m;

class ResourceManagerTest extends \PHPUnit_Framework_TestCase
{
    protected $simpleItem = array('foo' => 'bar');
    protected $simpleCollection = array(array('foo' => 'bar'));

    public function testSetRequestedScopes()
    {
        $manager = new ResourceManager();
        $this->assertInstanceOf('League\Fractal\ResourceManager', $manager->setRequestedScopes(array('foo')));
    }

    public function testGetRequestedScopes()
    {
        $manager = new ResourceManager();
        $manager->setRequestedScopes(array('foo', 'bar', 'baz.bart'));
        $this->assertEquals($manager->getRequestedScopes(), array('foo', 'bar', 'baz.bart'));
    }

    public function testCreateDataWithCallback()
    {
        $manager = new ResourceManager();

        $resource = new ItemResource(array('foo' => 'bar'), function (array $data) {
            return $data;
        });

        $rootScope = $manager->createData($resource);
        
        $this->assertInstanceOf('League\Fractal\Scope', $rootScope);
        
        $this->assertEquals($rootScope->toArray(), array('foo' => 'bar'));
        $this->assertEquals($rootScope->toJson(), '{"foo":"bar"}');

    }



    public function testCreateDataWithClassItem()
    {
        $manager = new ResourceManager();

        $transformer = m::mock('League\Fractal\TransformerAbstract');
        $transformer->shouldReceive('transform')->once()->andReturn($this->simpleItem);
        $transformer->shouldReceive('processEmbededResources')->once()->andReturn(array());

        $resource = new ItemResource($this->simpleItem, $transformer);
        $scope = $manager->createData($resource);
        $this->assertEquals($scope->getCurrentData(), $this->simpleItem);
    }

    public function testCreateDataWithClassCollection()
    {
        $manager = new ResourceManager();

        $transformer = m::mock('League\Fractal\TransformerAbstract');
        $transformer->shouldReceive('transform')->once()->andReturn(array('foo' => 'bar'));
        $transformer->shouldReceive('processEmbededResources')->once()->andReturn(array());

        $resource = new CollectionResource(array(array('foo' => 'bar')), $transformer);
        $scope = $manager->createData($resource);
        $this->assertEquals($scope->getCurrentData(), array(array('foo' => 'bar')));
    }

    public function testCreateDataWithClassPagination()
    {
        $manager = new ResourceManager();

        $transformer = m::mock('League\Fractal\TransformerAbstract');
        $transformer->shouldReceive('transform')->once()->andReturn(array('foo' => 'bar'));
        $transformer->shouldReceive('processEmbededResources')->once()->andReturn(array());

        $paginator = m::mock('Illuminate\Pagination\Paginator');
        $paginator->shouldReceive('getCollection')->once()->andReturn(array(array('foo' => 'bar')));

        $resource = new PaginatorResource($paginator, $transformer);
        $scope = $manager->createData($resource);
        $this->assertEquals($scope->getCurrentData(), array(array('foo' => 'bar')));
    }

    public function tearDown()
    {
        m::close();
    }
}
