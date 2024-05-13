<?php namespace League\Fractal\Test;

use BadMethodCallException;
use Exception;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Scope;
use League\Fractal\ScopeInterface;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class TransformerAbstractTest extends TestCase
{
    /**
     * @covers \League\Fractal\TransformerAbstract::setAvailableIncludes
     */
    public function testSetAvailableIncludes()
    {
        $transformer = m::mock('League\Fractal\TransformerAbstract')->makePartial();
        $this->assertInstanceOf('League\Fractal\TransformerAbstract', $transformer->setAvailableIncludes(['foo']));
    }

    /**
     * @covers \League\Fractal\TransformerAbstract::getAvailableIncludes
     */
    public function testGetAvailableIncludes()
    {
        $transformer = m::mock('League\Fractal\TransformerAbstract')->makePartial();

        $transformer->setAvailableIncludes(['foo', 'bar']);
        $this->assertSame(['foo', 'bar'], $transformer->getAvailableIncludes());
    }

    /**
     * @covers \League\Fractal\TransformerAbstract::setDefaultIncludes
     */
    public function testSetDefaultIncludes()
    {
        $transformer = m::mock('League\Fractal\TransformerAbstract')->makePartial();
        $this->assertInstanceOf('League\Fractal\TransformerAbstract', $transformer->setDefaultIncludes(['foo']));
    }

    /**
     * @covers \League\Fractal\TransformerAbstract::getDefaultIncludes
     */
    public function testGetDefaultIncludes()
    {
        $transformer = m::mock('League\Fractal\TransformerAbstract')->makePartial();

        $transformer->setDefaultIncludes(['foo', 'bar']);
        $this->assertSame(['foo', 'bar'], $transformer->getDefaultIncludes());
    }

    /**
     * @covers \League\Fractal\TransformerAbstract::setCurrentScope
     */
    public function testSetCurrentScope()
    {
        $transformer = $this->getMockForAbstractClass('League\Fractal\TransformerAbstract');
        $manager = new Manager();
        $scope = new Scope($manager, m::mock('League\Fractal\Resource\ResourceAbstract'));
        $this->assertInstanceOf('League\Fractal\TransformerAbstract', $transformer->setCurrentScope($scope));
    }

    /**
     * @covers \League\Fractal\TransformerAbstract::getCurrentScope
     */
    public function testGetCurrentScope()
    {
        $transformer = m::mock('League\Fractal\TransformerAbstract')->makePartial();
        $manager = new Manager();
        $scope = new Scope($manager, m::mock('League\Fractal\Resource\ResourceAbstract'));
        $transformer->setCurrentScope($scope);
        $this->assertSame($transformer->getCurrentScope(), $scope);
    }

    /**
     * @covers \League\Fractal\TransformerAbstract::getCurrentScope
     */
    public function testCanAccessScopeBeforeInitialization()
    {
        $transformer = $this->getMockForAbstractClass('League\Fractal\TransformerAbstract');
        $currentScope = $transformer->getCurrentScope();
        $this->assertNull($currentScope);
    }

    public function testProcessEmbeddedResourcesNoAvailableIncludes()
    {
        $transformer = m::mock('League\Fractal\TransformerAbstract')->makePartial();

        $manager = new Manager();
        $manager->parseIncludes('foo');

        $scope = new Scope($manager, m::mock('League\Fractal\Resource\ResourceAbstract'));
        $this->assertNull($transformer->processIncludedResources($scope, ['some' => 'data']));
    }

    public function testProcessEmbeddedResourcesNoDefaultIncludes()
    {
        $transformer = m::mock('League\Fractal\TransformerAbstract')->makePartial();

        $manager = new Manager();
        $manager->parseIncludes('foo');

        $scope = new Scope($manager, m::mock('League\Fractal\Resource\ResourceAbstract'));
        $this->assertNull($transformer->processIncludedResources($scope, ['some' => 'data']));
    }

    /**
     * @covers \League\Fractal\TransformerAbstract::processIncludedResources
     * @covers \League\Fractal\TransformerAbstract::callIncludeMethod
     */
    public function testProcessEmbeddedResourcesInvalidAvailableEmbed()
    {
        $this->expectException(BadMethodCallException::class);

        $transformer = m::mock('League\Fractal\TransformerAbstract')->makePartial();

        $manager = new Manager();
        $manager->parseIncludes('book');

        $scope = new Scope($manager, m::mock('League\Fractal\Resource\ResourceAbstract'));

        $transformer->setAvailableIncludes(['book']);
        $transformer->processIncludedResources($scope, []);
    }

    /**
     * @covers \League\Fractal\TransformerAbstract::processIncludedResources
     * @covers \League\Fractal\TransformerAbstract::callIncludeMethod
     */
    public function testProcessEmbeddedResourcesInvalidDefaultEmbed()
    {
        $this->expectException(BadMethodCallException::class);

        $transformer = m::mock('League\Fractal\TransformerAbstract')->makePartial();

        $manager = new Manager();

        $scope = new Scope($manager, m::mock('League\Fractal\Resource\ResourceAbstract'));

        $transformer->setDefaultIncludes(['book']);
        $transformer->processIncludedResources($scope, []);
    }

    /**
     * @covers \League\Fractal\TransformerAbstract::processIncludedResources
     * @covers \League\Fractal\TransformerAbstract::callIncludeMethod
     */
    public function testProcessIncludedAvailableResources()
    {
        $manager = new Manager();
        $manager->parseIncludes('book');
        $transformer = m::mock('League\Fractal\TransformerAbstract[transform]');

        $transformer->shouldReceive('includeBook')->once()->andReturnUsing(function ($data) {
            return new Item(['included' => 'thing'], function ($data) {
                return $data;
            });
        });

        $transformer->setAvailableIncludes(['book', 'publisher']);
        $scope = new Scope($manager, new Item([], $transformer));
        $included = $transformer->processIncludedResources($scope, ['meh']);
        $this->assertSame(['book' => ['data' => ['included' => 'thing']]], $included);
    }

    /**
     * @covers \League\Fractal\TransformerAbstract::processIncludedResources
     * @covers \League\Fractal\TransformerAbstract::figureOutWhichIncludes
     */
    public function testProcessExcludedAvailableResources()
    {
        $manager = new Manager();
        $transformer = m::mock('League\Fractal\TransformerAbstract[transform]');
        $scope = new Scope($manager, new Item([], $transformer));

        $transformer->shouldReceive('includeBook')->never();

        $transformer->shouldReceive('includePublisher')->once()->andReturnUsing(function ($data) {
            return new Item(['another' => 'thing'], function ($data) {
                return $data;
            });
        });

        // available includes that have been requested are excluded
        $manager->parseIncludes('book,publisher');
        $manager->parseExcludes('book');

        $transformer->setAvailableIncludes(['book', 'publisher']);

        $included = $transformer->processIncludedResources($scope, ['meh']);
        $this->assertSame(['publisher' => ['data' => ['another' => 'thing']]], $included);
    }

    /**
     * @covers \League\Fractal\TransformerAbstract::processIncludedResources
     * @covers \League\Fractal\TransformerAbstract::figureOutWhichIncludes
     */
    public function testProcessExcludedDefaultResources()
    {
        $manager = new Manager();
        $transformer = m::mock('League\Fractal\TransformerAbstract[transform]');
        $scope = new Scope($manager, new Item([], $transformer));

        $transformer->shouldReceive('includeBook')->never();

        $transformer->shouldReceive('includePublisher')->once()->andReturnUsing(function ($data) {
            return new Item(['another' => 'thing'], function ($data) {
                return $data;
            });
        });

        $manager->parseIncludes('book,publisher');
        $manager->parseExcludes('book');

        $transformer->setDefaultIncludes(['book', 'publisher']);

        $included = $transformer->processIncludedResources($scope, ['meh']);
        $this->assertSame(['publisher' => ['data' => ['another' => 'thing']]], $included);
    }

    /**
     * @covers \League\Fractal\TransformerAbstract::processIncludedResources
     * @covers \League\Fractal\TransformerAbstract::callIncludeMethod
     */
    public function testProcessIncludedAvailableResourcesEmptyEmbed()
    {
        $manager = new Manager();
        $manager->parseIncludes(['book']);
        $transformer = m::mock('League\Fractal\TransformerAbstract[transform]');

        $transformer->shouldReceive('includeBook')->once()->andReturn(null);

        $transformer->setAvailableIncludes(['book']);
        $scope = new Scope($manager, new Item([], $transformer));
        $included = $transformer->processIncludedResources($scope, ['meh']);

        $this->assertNull($included);
    }

    /**
     * @covers \League\Fractal\TransformerAbstract::callIncludeMethod
     */
    public function testCallEmbedMethodReturnsCrap()
    {
        $this->expectExceptionObject(new Exception('Invalid return value from League\Fractal\TransformerAbstract::includeBook().'));

        $manager = new Manager();
        $manager->parseIncludes('book');
        $transformer = m::mock('League\Fractal\TransformerAbstract[transform]');

        $transformer->shouldReceive('includeBook')->once()->andReturn(new \stdClass());

        $transformer->setAvailableIncludes(['book']);
        $scope = new Scope($manager, new Item([], $transformer));
        $transformer->processIncludedResources($scope, ['meh']);
    }

    /**
     * @covers \League\Fractal\TransformerAbstract::processIncludedResources
     * @covers \League\Fractal\TransformerAbstract::callIncludeMethod
     */
    public function testProcessEmbeddedDefaultResources()
    {
        $manager = new Manager();
        $transformer = m::mock('League\Fractal\TransformerAbstract[transform]');

        $transformer->shouldReceive('includeBook')->once()->andReturnUsing(function ($data) {
            return new Item(['included' => 'thing'], function ($data) {
                return $data;
            });
        });

        $transformer->setDefaultIncludes(['book']);
        $scope = new Scope($manager, new Item([], $transformer));
        $included = $transformer->processIncludedResources($scope, ['meh']);
        $this->assertSame(['book' => ['data' => ['included' => 'thing']]], $included);
    }

    /**
     * @covers \League\Fractal\TransformerAbstract::processIncludedResources
     * @covers \League\Fractal\TransformerAbstract::callIncludeMethod
     */
    public function testIncludedItem()
    {
        $manager = new Manager();
        $manager->parseIncludes('book');

        $transformer = m::mock('League\Fractal\TransformerAbstract[transform]');
        $transformer->shouldReceive('includeBook')->once()->andReturnUsing(function ($data) {
            return new Item(['included' => 'thing'], function ($data) {
                return $data;
            });
        });

        $transformer->setAvailableIncludes(['book']);
        $scope = new Scope($manager, new Item([], $transformer));
        $included = $transformer->processIncludedResources($scope, ['meh']);
        $this->assertSame(['book' => ['data' => ['included' => 'thing']]], $included);
    }

    public function testParamBagIsProvidedForIncludes()
    {
        $manager = new Manager();
        $manager->parseIncludes('book:foo(bar)');

        $transformer = m::mock('League\Fractal\TransformerAbstract')->makePartial();

        $transformer->shouldReceive('includeBook')
            ->with(
                m::any(),
                m::type('\League\Fractal\ParamBag'),
                m::type(ScopeInterface::class)
            )
            ->once();

        $transformer->setAvailableIncludes(['book']);
        $scope = new Scope($manager, new Item([], $transformer));

        $this->assertNull($transformer->processIncludedResources($scope, []));
    }

    /**
     * @covers \League\Fractal\TransformerAbstract::processIncludedResources
     * @covers \League\Fractal\TransformerAbstract::callIncludeMethod
     */
    public function testIncludedCollection()
    {
        $manager = new Manager();
        $manager->parseIncludes('book');

        $collectionData = [
            ['included' => 'thing'],
            ['another' => 'thing'],
        ];

        $transformer = m::mock('League\Fractal\TransformerAbstract[transform]');
        $transformer->shouldReceive('includeBook')->once()->andReturnUsing(function ($data) use ($collectionData) {
            return new Collection($collectionData, function ($data) {
                return $data;
            });
        });

        $transformer->setAvailableIncludes(['book']);
        $scope = new Scope($manager, new Collection([], $transformer));
        $included = $transformer->processIncludedResources($scope, ['meh']);
        $this->assertSame(['book' => ['data' => $collectionData]], $included);
    }

    /**
     * @covers \League\Fractal\TransformerAbstract::processIncludedResources
     * @covers \League\Fractal\TransformerAbstract::callIncludeMethod
     */
    public function testProcessEmbeddedDefaultResourcesEmptyEmbed()
    {
        $transformer = m::mock('League\Fractal\TransformerAbstract[transform]');
        $transformer->shouldReceive('includeBook')->once()->andReturn(null);

        $transformer->setDefaultIncludes(['book']);
        $scope = new Scope(new Manager(), new Item([], $transformer));
        $included = $transformer->processIncludedResources($scope, ['meh']);

        $this->assertNull($included);
    }

    /**
     * @covers \League\Fractal\TransformerAbstract::item
     */
    public function testItem()
    {
        $mock = m::mock('League\Fractal\TransformerAbstract');
        $item = $mock->item([], function () {
        });
        $this->assertInstanceOf('League\Fractal\Resource\Item', $item);
    }

    /**
     * @covers \League\Fractal\TransformerAbstract::collection
     */
    public function testCollection()
    {
        $mock = m::mock('League\Fractal\TransformerAbstract');
        $collection = $mock->collection([], function () {
        });
        $this->assertInstanceOf('League\Fractal\Resource\Collection', $collection);
    }

    public function tearDown(): void
    {
        m::close();
    }
}
