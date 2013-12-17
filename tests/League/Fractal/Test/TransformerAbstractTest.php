<?php namespace League\Fractal\Test;

use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Scope;
use Mockery as m;

class TransformerAbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers League\Fractal\TransformerAbstract::setAvailableEmbeds
     */
    public function testSetAvailableEmbeds()
    {
        $transformer = m::mock('League\Fractal\TransformerAbstract')->makePartial();
        $this->assertInstanceOf('League\Fractal\TransformerAbstract', $transformer->setAvailableEmbeds(array('foo')));
    }

    /**
     * @covers League\Fractal\TransformerAbstract::getAvailableEmbeds
     */
    public function testGetAvailableEmbeds()
    {
        $transformer = m::mock('League\Fractal\TransformerAbstract')->makePartial();
        $transformer->setAvailableEmbeds(array('foo', 'bar'));
        $this->assertEquals(array('foo', 'bar'), $transformer->getAvailableEmbeds());
    }

    /**
     * @covers League\Fractal\TransformerAbstract::setDefaultEmbeds
     */
    public function testSetDefaultEmbeds()
    {
        $transformer = m::mock('League\Fractal\TransformerAbstract')->makePartial();
        $this->assertInstanceOf('League\Fractal\TransformerAbstract', $transformer->setDefaultEmbeds(array('foo')));
    }

    /**
     * @covers League\Fractal\TransformerAbstract::getDefaultEmbeds
     */
    public function testGetDefaultEmbeds()
    {
        $transformer = m::mock('League\Fractal\TransformerAbstract')->makePartial();
        $transformer->setDefaultEmbeds(array('foo', 'bar'));
        $this->assertEquals(array('foo', 'bar'), $transformer->getDefaultEmbeds());
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

    /**
     * @covers League\Fractal\TransformerAbstract::getManager
     */
    public function testGetManager()
    {
        $transformer = m::mock('League\Fractal\TransformerAbstract')->makePartial();
        $manager = new Manager;
        $transformer->setManager($manager);
        $this->assertEquals($transformer->getManager(), $manager);
    }

    public function testProcessEmbededResourcesNoAvailableEmbeds()
    {
        $transformer = m::mock('League\Fractal\TransformerAbstract')->makePartial();

        $manager = new Manager;
        $manager->setRequestedScopes(array('foo'));

        $transformer->setManager($manager);

        $scope = new Scope($manager, m::mock('League\Fractal\Resource\ResourceInterface'));
        $this->assertFalse($transformer->processEmbededResources($scope, array('some' => 'data')));
    }

    public function testProcessEmbededResourcesNoDefaultEmbeds()
    {
        $transformer = m::mock('League\Fractal\TransformerAbstract')->makePartial();

        $manager = new Manager;
        $manager->setRequestedScopes(array('foo'));

        $transformer->setManager($manager);

        $scope = new Scope($manager, m::mock('League\Fractal\Resource\ResourceInterface'));
        $this->assertFalse($transformer->processEmbededResources($scope, array('some' => 'data')));
    }

    /**
     * @covers League\Fractal\TransformerAbstract::processEmbededResources
     * @covers League\Fractal\TransformerAbstract::callEmbedMethod
     * @expectedException BadMethodCallException
     */
    public function testProcessEmbededResourcesInvalidAvailableEmbed()
    {
        $transformer = m::mock('League\Fractal\TransformerAbstract')->makePartial();

        $manager = new Manager;
        $manager->setRequestedScopes(array('book'));

        $transformer->setManager($manager);
        $scope = new Scope($manager, m::mock('League\Fractal\Resource\ResourceInterface'));

        $transformer->setAvailableEmbeds(array('book'));
        $transformer->processEmbededResources($scope, array());
    }

    /**
     * @covers League\Fractal\TransformerAbstract::processEmbededResources
     * @covers League\Fractal\TransformerAbstract::callEmbedMethod
     * @expectedException BadMethodCallException
     */
    public function testProcessEmbededResourcesInvalidDefaultEmbed()
    {
        $transformer = m::mock('League\Fractal\TransformerAbstract')->makePartial();

        $manager = new Manager;
        $transformer->setManager($manager);

        $scope = new Scope($manager, m::mock('League\Fractal\Resource\ResourceInterface'));

        $transformer->setDefaultEmbeds(array('book'));
        $transformer->processEmbededResources($scope, array());
    }

    /**
     * @covers League\Fractal\TransformerAbstract::processEmbededResources
     * @covers League\Fractal\TransformerAbstract::callEmbedMethod
     */
    public function testProcessEmbededAvailableResources()
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
     * @covers League\Fractal\TransformerAbstract::processEmbededResources
     * @covers League\Fractal\TransformerAbstract::callEmbedMethod
     */
    public function testProcessEmbededAvailableResourcesEmptyEmbed()
    {
        $manager = new Manager;
        $manager->setRequestedScopes(array('book'));
        $transformer = m::mock('League\Fractal\TransformerAbstract[transform]');

        $transformer->shouldReceive('embedBook')->once()->andReturn(null);

        $transformer->setAvailableEmbeds(array('book'));
        $scope = new Scope($manager, new Item(array(), $transformer));
        $embedded = $transformer->processEmbededResources($scope, array('meh'));

        $this->assertFalse($embedded);
    }

    /**
     * @covers League\Fractal\TransformerAbstract::callEmbedMethod
     * @expectedException Exception
     * @expectedExceptionMessage Invalid return value from League\Fractal\TransformerAbstract::embedBook(). 
     */
    public function testCallEmbedMethodReturnsCrap()
    {
        $manager = new Manager;
        $manager->setRequestedScopes(array('book'));
        $transformer = m::mock('League\Fractal\TransformerAbstract[transform]');

        $transformer->shouldReceive('embedBook')->once()->andReturn(new \stdClass);

        $transformer->setAvailableEmbeds(array('book'));
        $scope = new Scope($manager, new Item(array(), $transformer));
        $embedded = $transformer->processEmbededResources($scope, array('meh'));
    }

    /**
     * @covers League\Fractal\TransformerAbstract::processEmbededResources
     * @covers League\Fractal\TransformerAbstract::callEmbedMethod
     */
    public function testProcessEmbededDefaultResources()
    {
        $manager = new Manager;
        $transformer = m::mock('League\Fractal\TransformerAbstract[transform]');

        $transformer->shouldReceive('embedBook')->once()->andReturnUsing(function($data) { 
            return new Item(array('embedded' => 'thing'), function ($data) { return $data; });
        });
        $transformer->setDefaultEmbeds(array('book'));
        $scope = new Scope($manager, new Item(array(), $transformer));
        $embedded = $transformer->processEmbededResources($scope, array('meh'));
        $this->assertEquals(array('book' => array('data' => array('embedded' => 'thing'))), $embedded);
    }

    /**
     * @covers League\Fractal\TransformerAbstract::processEmbededResources
     * @covers League\Fractal\TransformerAbstract::callEmbedMethod
     */
    public function testProcessEmbededDefaultResourcesEmptyEmbed()
    {
        $manager = new Manager;
        $transformer = m::mock('League\Fractal\TransformerAbstract[transform]');

        $transformer->shouldReceive('embedBook')->once()->andReturn(null);

        $transformer->setDefaultEmbeds(array('book'));
        $scope = new Scope($manager, new Item(array(), $transformer));
        $embedded = $transformer->processEmbededResources($scope, array('meh'));

        $this->assertFalse($embedded);
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

    public function tearDown()
    {
        m::close();
    }
}
