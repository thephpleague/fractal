<?php namespace League\Fractal\Test;

use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\PaginatedCollection;
use League\Fractal\Manager;
use Mockery as m;

class ManagerTest extends \PHPUnit_Framework_TestCase
{
    protected $simpleItem = array('foo' => 'bar');
    protected $simpleCollection = array(array('foo' => 'bar'));

    public function testSetRequestedScopes()
    {
        $manager = new Manager();
        $this->assertInstanceOf('League\Fractal\Manager', $manager->setRequestedScopes(array('foo')));
    }

    public function testGetRequestedScopes()
    {
        $manager = new Manager();
        $manager->setRequestedScopes(array('foo', 'bar', 'baz.bart'));
        $this->assertEquals(array('foo', 'bar', 'baz.bart'), $manager->getRequestedScopes());
    }

    public function testCreateDataWithCallback()
    {
        $manager = new Manager();

        $resource = new Item(array('foo' => 'bar'), function (array $data) {
            return $data;
        });

        $rootScope = $manager->createData($resource);
        
        $this->assertInstanceOf('League\Fractal\Scope', $rootScope);

        $this->assertEquals(array('data' => array('foo' => 'bar')), $rootScope->toArray());
        $this->assertEquals('{"data":{"foo":"bar"}}', $rootScope->toJson());

    }

    public function testCreateDataWithClassItem()
    {
        $manager = new Manager();

        $transformer = m::mock('League\Fractal\TransformerAbstract');
        $transformer->shouldReceive('transform')->once()->andReturn($this->simpleItem);
        $transformer->shouldReceive('processEmbededResources')->once()->andReturn(array());

        $resource = new Item($this->simpleItem, $transformer);
        $scope = $manager->createData($resource);
        $this->assertEquals($this->simpleItem, $scope->getCurrentData());
    }

    public function testCreateDataWithClassCollection()
    {
        $manager = new Manager();

        $transformer = m::mock('League\Fractal\TransformerAbstract');
        $transformer->shouldReceive('transform')->once()->andReturn(array('foo' => 'bar'));
        $transformer->shouldReceive('processEmbededResources')->once()->andReturn(array());

        $resource = new Collection(array(array('foo' => 'bar')), $transformer);
        $scope = $manager->createData($resource);
        $this->assertEquals(array(array('foo' => 'bar')), $scope->getCurrentData());
    }

    public function testCreateDataWithClassPagination()
    {
        $manager = new Manager();

        $transformer = m::mock('League\Fractal\TransformerAbstract');
        $transformer->shouldReceive('transform')->once()->andReturn(array('foo' => 'bar'));
        $transformer->shouldReceive('processEmbededResources')->once()->andReturn(array());

        $paginator = m::mock('Illuminate\Pagination\Paginator');
        $paginator->shouldReceive('getCollection')->once()->andReturn(array(array('foo' => 'bar')));

        $resource = new PaginatedCollection($paginator, $transformer);
        $scope = $manager->createData($resource);
        $this->assertEquals(array(array('foo' => 'bar')), $scope->getCurrentData());
    }

    public function tearDown()
    {
        m::close();
    }
}
