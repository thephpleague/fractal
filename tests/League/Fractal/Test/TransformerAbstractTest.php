<?php namespace League\Fractal\Test;

use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Scope;
use Mockery as m;

class TransformerAbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers League\Fractal\TransformerAbstract::getAvailableEmbeds
     */
    public function testGetAvailableEmbeds()
    {
        $transformer = $this->getMockForAbstractClass('League\Fractal\TransformerAbstract');
        $transformer->setAvailableEmbeds(array('foo', 'bar'));
        $this->assertEquals(array('foo', 'bar'), $transformer->getAvailableEmbeds());
    }

    /**
     * @covers League\Fractal\TransformerAbstract::setAvailableEmbeds
     */
    public function testSetAvailableEmbeds()
    {
        $transformer = $this->getMockForAbstractClass('League\Fractal\TransformerAbstract');
        $this->assertInstanceOf('League\Fractal\TransformerAbstract', $transformer->setAvailableEmbeds(array('foo')));
    }

    /**
     * @covers League\Fractal\TransformerAbstract::getManager
     */
    public function testGetManager()
    {
        $transformer = $this->getMockForAbstractClass('League\Fractal\TransformerAbstract');
        $manager = new Manager;
        $transformer->setManager($manager);
        $this->assertEquals($transformer->getManager(), $manager);
    }

    /**
     * @covers League\Fractal\TransformerAbstract::setManager
     */
    public function testSetManager()
    {
        $transformer = $this->getMockForAbstractClass('League\Fractal\TransformerAbstract');
        $manager = new Manager;
        $this->assertInstanceOf('League\Fractal\TransformerAbstract', $transformer->setManager($manager));
    }

    public function testProcessEmbededResourcesNoAvailableEmbeds()
    {
        $transformer = $this->getMockForAbstractClass('League\Fractal\TransformerAbstract');

        $manager = new Manager;
        $manager->setRequestedScopes(array('foo'));

        $transformer->setManager($manager);

        $scope = new Scope($manager, m::mock('League\Fractal\Resource\ResourceInterface'));
        $this->assertFalse($transformer->processEmbededResources($scope, array('some' => 'data')));
    }

    /**
     * @covers League\Fractal\TransformerAbstract::processEmbededResources
     * @expectedException BadMethodCallException
     */
    public function testProcessEmbededResourcesInvalidEmbed()
    {
        $transformer = $this->getMockForAbstractClass('League\Fractal\TransformerAbstract');

        $manager = new Manager;
        $manager->setRequestedScopes(array('foo'));

        $transformer->setManager($manager);
        $scope = new Scope($manager, m::mock('League\Fractal\Resource\ResourceInterface'));

        $transformer->setAvailableEmbeds(array('foo'));
        $transformer->processEmbededResources($scope, array('some' => 'data'));
    }

    /**
     * @covers League\Fractal\TransformerAbstract::processEmbededResources
     */
    public function testProcessEmbededResources()
    {
        $manager = new Manager;
        $manager->setRequestedScopes(array('book'));
        $transformer = m::mock('League\Fractal\TransformerAbstract[transform]');
        $transformer->shouldReceive('embedBook')->once()->andReturnUsing(function($data) { 
            return new Item(array('embedded' => 'thing'), function ($data) { return $data; });
        });
        $transformer->setAvailableEmbeds(array('book', 'publisher'));
        $scope = new Scope($manager, new Item(array(), $transformer));
        $embedded = $transformer->processEmbededResources($scope, array('meh'));
        $this->assertEquals(array('book' => array('data' => array('embedded' => 'thing'))), $embedded);
    }

    /**
     * @covers League\Fractal\TransformerAbstract::item
     */
    public function testItem()
    {
        $mock = m::mock('League\Fractal\TransformerAbstract');
        $item = $mock->item(array(), function () {});
        $this->assertInstanceOf('League\Fractal\Resource\Item', $item);
    }

    /**
     * @covers League\Fractal\TransformerAbstract::collection
     */
    public function testCollection()
    {
        $mock = m::mock('League\Fractal\TransformerAbstract');
        $collection = $mock->collection(array(), function () {});
        $this->assertInstanceOf('League\Fractal\Resource\Collection', $collection);
    }

    /**
     * @covers League\Fractal\TransformerAbstract::paginatedCollection
     */
    public function testPaginatedCollection()
    {
        $mock = m::mock('League\Fractal\TransformerAbstract');
        $paginator = m::mock('Illuminate\Pagination\Paginator');
        $paginator->shouldReceive('getCollection')->once()->andReturn(array());
        $collection = $mock->paginatedCollection($paginator, function () {});
        $this->assertInstanceOf('League\Fractal\Resource\PaginatedCollection', $collection);
    }

    public function tearDown()
    {
        m::close();
    }
}
