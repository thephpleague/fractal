<?php namespace League\Fractal\Test;

use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\Collection;
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

    public function testProcessEmbeddedResourcesNoAvailableEmbeds()
    {
        $transformer = m::mock('League\Fractal\TransformerAbstract')->makePartial();

        $manager = new Manager;
        $manager->parseIncludes('foo');

        $scope = new Scope($manager, m::mock('League\Fractal\Resource\ResourceInterface'));
        $this->assertFalse($transformer->processEmbeddedResources($scope, array('some' => 'data')));
    }

    public function testProcessEmbeddedResourcesNoDefaultEmbeds()
    {
        $transformer = m::mock('League\Fractal\TransformerAbstract')->makePartial();

        $manager = new Manager;
        $manager->parseIncludes('foo');

        $scope = new Scope($manager, m::mock('League\Fractal\Resource\ResourceInterface'));
        $this->assertFalse($transformer->processEmbeddedResources($scope, array('some' => 'data')));
    }

    /**
     * @covers League\Fractal\TransformerAbstract::processEmbeddedResources
     * @covers League\Fractal\TransformerAbstract::callIncludeMethod
     * @expectedException BadMethodCallException
     */
    public function testProcessEmbeddedResourcesInvalidAvailableEmbed()
    {
        $transformer = m::mock('League\Fractal\TransformerAbstract')->makePartial();

        $manager = new Manager;
        $manager->parseIncludes('book');

        $scope = new Scope($manager, m::mock('League\Fractal\Resource\ResourceInterface'));

        $transformer->setAvailableIncludes(array('book'));
        $transformer->processEmbeddedResources($scope, array());
    }

    /**
     * @covers League\Fractal\TransformerAbstract::processEmbeddedResources
     * @covers League\Fractal\TransformerAbstract::callIncludeMethod
     * @expectedException BadMethodCallException
     */
    public function testProcessEmbeddedResourcesInvalidDefaultEmbed()
    {
        $transformer = m::mock('League\Fractal\TransformerAbstract')->makePartial();

        $manager = new Manager;

        $scope = new Scope($manager, m::mock('League\Fractal\Resource\ResourceInterface'));

        $transformer->setDefaultIncludes(array('book'));
        $transformer->processEmbeddedResources($scope, array());
    }

    /**
     * @covers League\Fractal\TransformerAbstract::processEmbeddedResources
     * @covers League\Fractal\TransformerAbstract::callIncludeMethod
     */
    public function testProcessEmbeddedAvailableResources()
    {
        $manager = new Manager;
        $manager->parseIncludes('book');
        $transformer = m::mock('League\Fractal\TransformerAbstract[transform]');

        $transformer->shouldReceive('embedBook')->once()->andReturnUsing(function ($data) {
            return new Item(array('embedded' => 'thing'), function ($data) {
                return $data;
            });
        });
        $transformer->setAvailableIncludes(array('book', 'publisher'));
        $scope = new Scope($manager, new Item(array(), $transformer));
        $embedded = $transformer->processEmbeddedResources($scope, array('meh'));
        $this->assertEquals(array('book' => array('data' => array('embedded' => 'thing'))), $embedded);
    }

    /**
     * @covers League\Fractal\TransformerAbstract::processEmbeddedResources
     * @covers League\Fractal\TransformerAbstract::callIncludeMethod
     */
    public function testProcessEmbeddedAvailableResourcesEmptyEmbed()
    {
        $manager = new Manager;
        $manager->parseIncludes(array('book'));
        $transformer = m::mock('League\Fractal\TransformerAbstract[transform]');

        $transformer->shouldReceive('embedBook')->once()->andReturn(null);

        $transformer->setAvailableIncludes(array('book'));
        $scope = new Scope($manager, new Item(array(), $transformer));
        $embedded = $transformer->processEmbeddedResources($scope, array('meh'));

        $this->assertFalse($embedded);
    }

    /**
     * @covers League\Fractal\TransformerAbstract::callIncludeMethod
     * @expectedException Exception
     * @expectedExceptionMessage Invalid return value from League\Fractal\TransformerAbstract::embedBook().
     */
    public function testCallEmbedMethodReturnsCrap()
    {
        $manager = new Manager;
        $manager->parseIncludes('book');
        $transformer = m::mock('League\Fractal\TransformerAbstract[transform]');

        $transformer->shouldReceive('embedBook')->once()->andReturn(new \stdClass);

        $transformer->setAvailableIncludes(array('book'));
        $scope = new Scope($manager, new Item(array(), $transformer));
        $transformer->processEmbeddedResources($scope, array('meh'));
    }

    /**
     * @covers League\Fractal\TransformerAbstract::processEmbeddedResources
     * @covers League\Fractal\TransformerAbstract::callIncludeMethod
     */
    public function testProcessEmbeddedDefaultResources()
    {
        $manager = new Manager;
        $transformer = m::mock('League\Fractal\TransformerAbstract[transform]');

        $transformer->shouldReceive('embedBook')->once()->andReturnUsing(function ($data) {
            return new Item(array('embedded' => 'thing'), function ($data) {
                return $data;
            });
        });
        $transformer->setDefaultIncludes(array('book'));
        $scope = new Scope($manager, new Item(array(), $transformer));
        $embedded = $transformer->processEmbeddedResources($scope, array('meh'));
        $this->assertEquals(array('book' => array('data' => array('embedded' => 'thing'))), $embedded);
    }

    public function testTransformerEmbedParams()
    {
        $manager = new Manager;

        // See if fancy syntax works
        $manager->parseIncludes('foo:limit(1|2):order(-something)');

        $transformer = m::mock('League\Fractal\TransformerAbstract[transform]');

        $resource = new Collection(array('foo' => 'bar'), $transformer);

        $that = $transformer;

        $transformer->shouldReceive('embedFoo')->once()->andReturnUsing(function ($data) use ($that) {
            return new Item($that->param('limit'), function ($data) {
                return $data;
            });
        });

        $rootScope = $manager->createData($resource);

        $this->assertEquals(array('data' => array('foo' => 'bar')), $rootScope->toArray());


//        $scope = new Scope($manager, new Item(array(), $transformer));
//        $embedded = $transformer->processEmbeddedResources($scope, array('meh'));
//        $this->assertEquals(array('book' => array('data' => array('limit' => array ('1', '2')))), $embedded);
    }

    /**
     * @covers League\Fractal\TransformerAbstract::processEmbeddedResources
     * @covers League\Fractal\TransformerAbstract::callIncludeMethod
     */
    public function testProcessEmbeddedDefaultResourcesEmptyEmbed()
    {
        $manager = new Manager;
        $transformer = m::mock('League\Fractal\TransformerAbstract[transform]');

        $transformer->shouldReceive('embedBook')->once()->andReturn(null);

        $transformer->setDefaultIncludes(array('book'));
        $scope = new Scope($manager, new Item(array(), $transformer));
        $embedded = $transformer->processEmbeddedResources($scope, array('meh'));

        $this->assertFalse($embedded);
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
