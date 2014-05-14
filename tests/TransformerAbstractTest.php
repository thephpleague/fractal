<?php namespace League\Fractal\Test;

use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Scope;
use Mockery as m;

class TransformerAbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers League\Fractal\TransformerAbstract::setAvailableIncludes
     */
    public function testSetAvailableIncludes()
    {
        $transformer = m::mock('League\Fractal\TransformerAbstract')->makePartial();
        $this->assertInstanceOf('League\Fractal\TransformerAbstract', $transformer->setAvailableIncludes(array('foo')));
    }

    /**
     * @covers League\Fractal\TransformerAbstract::getAvailableIncludes
     */
    public function testGetAvailableIncludes()
    {
        $transformer = m::mock('League\Fractal\TransformerAbstract')->makePartial();
        $transformer->setAvailableIncludes(array('foo', 'bar'));
        $this->assertEquals(array('foo', 'bar'), $transformer->getAvailableIncludes());
    }

    /**
     * @covers League\Fractal\TransformerAbstract::setDefaultIncludes
     */
    public function testSetDefaultIncludes()
    {
        $transformer = m::mock('League\Fractal\TransformerAbstract')->makePartial();
        $this->assertInstanceOf('League\Fractal\TransformerAbstract', $transformer->setDefaultIncludes(array('foo')));
    }

    /**
     * @covers League\Fractal\TransformerAbstract::getDefaultIncludes
     */
    public function testGetDefaultIncludes()
    {
        $transformer = m::mock('League\Fractal\TransformerAbstract')->makePartial();
        $transformer->setDefaultIncludes(array('foo', 'bar'));
        $this->assertEquals(array('foo', 'bar'), $transformer->getDefaultIncludes());
    }

    /**
     * @covers League\Fractal\TransformerAbstract::setCurrentScope
     */
    public function testSetCurrentScope()
    {
        $transformer = $this->getMockForAbstractClass('League\Fractal\TransformerAbstract');
        $manager = new Manager;
        $scope = new Scope($manager, m::mock('League\Fractal\Resource\ResourceAbstract'));
        $this->assertInstanceOf('League\Fractal\TransformerAbstract', $transformer->setCurrentScope($scope));
    }

    /**
     * @covers League\Fractal\TransformerAbstract::getCurrentScope
     */
    public function testGetCurrentScope()
    {
        $transformer = m::mock('League\Fractal\TransformerAbstract')->makePartial();
        $manager = new Manager;
        $scope = new Scope($manager, m::mock('League\Fractal\Resource\ResourceAbstract'));
        $transformer->setCurrentScope($scope);
        $this->assertEquals($transformer->getCurrentScope(), $scope);
    }

    public function testProcessEmbeddedResourcesNoAvailableIncludes()
    {
        $transformer = m::mock('League\Fractal\TransformerAbstract')->makePartial();

        $manager = new Manager;
        $manager->parseIncludes('foo');

        $scope = new Scope($manager, m::mock('League\Fractal\Resource\ResourceAbstract'));
        $this->assertFalse($transformer->processIncludedResources($scope, array('some' => 'data')));
    }

    public function testProcessEmbeddedResourcesNoDefaultIncludes()
    {
        $transformer = m::mock('League\Fractal\TransformerAbstract')->makePartial();

        $manager = new Manager;
        $manager->parseIncludes('foo');

        $scope = new Scope($manager, m::mock('League\Fractal\Resource\ResourceAbstract'));
        $this->assertFalse($transformer->processIncludedResources($scope, array('some' => 'data')));
    }

    /**
     * @covers League\Fractal\TransformerAbstract::processIncludedResources
     * @covers League\Fractal\TransformerAbstract::callIncludeMethod
     * @expectedException BadMethodCallException
     */
    public function testProcessEmbeddedResourcesInvalidAvailableEmbed()
    {
        $transformer = m::mock('League\Fractal\TransformerAbstract')->makePartial();

        $manager = new Manager;
        $manager->parseIncludes('book');

        $scope = new Scope($manager, m::mock('League\Fractal\Resource\ResourceAbstract'));
        $transformer->setCurrentScope($scope);

        $transformer->setAvailableIncludes(array('book'));
        $transformer->processIncludedResources($scope, array());
    }

    /**
     * @covers League\Fractal\TransformerAbstract::processIncludedResources
     * @covers League\Fractal\TransformerAbstract::callIncludeMethod
     * @expectedException BadMethodCallException
     */
    public function testProcessEmbeddedResourcesInvalidDefaultEmbed()
    {
        $transformer = m::mock('League\Fractal\TransformerAbstract')->makePartial();

        $manager = new Manager;

        $scope = new Scope($manager, m::mock('League\Fractal\Resource\ResourceAbstract'));

        $transformer->setDefaultIncludes(array('book'));
        $transformer->processIncludedResources($scope, array());
    }

    /**
     * @covers League\Fractal\TransformerAbstract::processIncludedResources
     * @covers League\Fractal\TransformerAbstract::callIncludeMethod
     */
    public function testProcessIncludedAvailableResources()
    {
        $manager = new Manager;
        $manager->parseIncludes('book');
        $transformer = m::mock('League\Fractal\TransformerAbstract[transform]');

        $transformer->shouldReceive('includeBook')->once()->andReturnUsing(function ($data) {
            return new Item(array('included' => 'thing'), function ($data) {
                return $data;
            });
        });
        $transformer->setAvailableIncludes(array('book', 'publisher'));
        $scope = new Scope($manager, new Item(array(), $transformer));
        $included = $transformer->processIncludedResources($scope, array('meh'));
        $this->assertEquals(array('book' => array('data' => array('included' => 'thing'))), $included);
    }

    /**
     * @covers League\Fractal\TransformerAbstract::processIncludedResources
     * @covers League\Fractal\TransformerAbstract::callIncludeMethod
     */
    public function testProcessIncludedAvailableResourcesEmptyEmbed()
    {
        $manager = new Manager;
        $manager->parseIncludes(array('book'));
        $transformer = m::mock('League\Fractal\TransformerAbstract[transform]');

        $transformer->shouldReceive('includeBook')->once()->andReturn(null);

        $transformer->setAvailableIncludes(array('book'));
        $scope = new Scope($manager, new Item(array(), $transformer));
        $included = $transformer->processIncludedResources($scope, array('meh'));

        $this->assertFalse($included);
    }

    /**
     * @covers League\Fractal\TransformerAbstract::callIncludeMethod
     * @expectedException Exception
     * @expectedExceptionMessage Invalid return value from League\Fractal\TransformerAbstract::includeBook().
     */
    public function testCallEmbedMethodReturnsCrap()
    {
        $manager = new Manager;
        $manager->parseIncludes('book');
        $transformer = m::mock('League\Fractal\TransformerAbstract[transform]');

        $transformer->shouldReceive('includeBook')->once()->andReturn(new \stdClass);

        $transformer->setAvailableIncludes(array('book'));
        $scope = new Scope($manager, new Item(array(), $transformer));
        $transformer->processIncludedResources($scope, array('meh'));
    }

    /**
     * @covers League\Fractal\TransformerAbstract::processIncludedResources
     * @covers League\Fractal\TransformerAbstract::callIncludeMethod
     */
    public function testProcessEmbeddedDefaultResources()
    {
        $manager = new Manager;
        $transformer = m::mock('League\Fractal\TransformerAbstract[transform]');

        $transformer->shouldReceive('includeBook')->once()->andReturnUsing(function ($data) {
            return new Item(array('included' => 'thing'), function ($data) {
                return $data;
            });
        });
        $transformer->setDefaultIncludes(array('book'));
        $scope = new Scope($manager, new Item(array(), $transformer));
        $included = $transformer->processIncludedResources($scope, array('meh'));
        $this->assertEquals(array('book' => array('data' => array('included' => 'thing'))), $included);
    }

    /**
     * @covers League\Fractal\TransformerAbstract::processIncludedResources
     * @covers League\Fractal\TransformerAbstract::callIncludeMethod
     */
    public function testIncludedItem()
    {
        $manager = new Manager;
        $manager->parseIncludes('book');

        $transformer = m::mock('League\Fractal\TransformerAbstract[transform]');
        $transformer->shouldReceive('includeBook')->once()->andReturnUsing(function ($data) {
            return new Item(array('included' => 'thing'), function ($data) {
                return $data;
            });
        });
        $transformer->setAvailableIncludes(array('book'));
        $scope = new Scope($manager, new Item(array(), $transformer));
        $included = $transformer->processIncludedResources($scope, array('meh'));
        $this->assertEquals(array('book' => array('data' => array('included' => 'thing'))), $included);
    }

    /**
     * @covers League\Fractal\TransformerAbstract::processIncludedResources
     * @covers League\Fractal\TransformerAbstract::callIncludeMethod
     */
    public function testIncludedCollection()
    {
        $manager = new Manager;
        $manager->parseIncludes('book');

        $collectionData = array(
            array('included' => 'thing'),
            array('another' => 'thing'),
        );

        $transformer = m::mock('League\Fractal\TransformerAbstract[transform]');
        $transformer->shouldReceive('includeBook')->once()->andReturnUsing(function ($data) use ($collectionData) {
            return new Collection($collectionData, function ($data) {
                return $data;
            });
        });
        $transformer->setAvailableIncludes(array('book'));
        $scope = new Scope($manager, new Collection(array(), $transformer));
        $included = $transformer->processIncludedResources($scope, array('meh'));
        $this->assertEquals(array('book' => array('data' => $collectionData)), $included);
    }

    /**
     * @covers League\Fractal\TransformerAbstract::processIncludedResources
     * @covers League\Fractal\TransformerAbstract::callIncludeMethod
     */
    public function testProcessEmbeddedDefaultResourcesEmptyEmbed()
    {
        $transformer = m::mock('League\Fractal\TransformerAbstract[transform]');
        $transformer->shouldReceive('includeBook')->once()->andReturn(null);

        $transformer->setDefaultIncludes(array('book'));
        $scope = new Scope(new Manager, new Item(array(), $transformer));
        $included = $transformer->processIncludedResources($scope, array('meh'));

        $this->assertFalse($included);
    }

    /**
     * @covers League\Fractal\TransformerAbstract::item
     */
    public function testItem()
    {
        $mock = m::mock('League\Fractal\TransformerAbstract');
        $item = $mock->item(array(), function () {
        });
        $this->assertInstanceOf('League\Fractal\Resource\Item', $item);
    }

    /**
     * @covers League\Fractal\TransformerAbstract::collection
     */
    public function testCollection()
    {
        $mock = m::mock('League\Fractal\TransformerAbstract');
        $collection = $mock->collection(array(), function () {
        });
        $this->assertInstanceOf('League\Fractal\Resource\Collection', $collection);
    }

    public function tearDown()
    {
        m::close();
    }
}
