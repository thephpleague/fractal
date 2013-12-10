<?php namespace League\Fractal\Test;

use League\Fractal\Manager;
use League\Fractal\Scope;
use Mockery;

class TransformerAbstractTest extends \PHPUnit_Framework_TestCase
{

    public function testEmbedStructure()
    {
        $transformer = $this->getMockForAbstractClass('League\Fractal\TransformerAbstract');
        $manager = new Manager;
        $transformer->setManager($manager);
        $scope = new Scope($manager);
        $scope->setCurrentData(array('some' => 'stuff'));
        $embed = $transformer->embedStructure($scope);

        $this->assertEquals(array('data' => array('some' => 'stuff')), $embed);
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

    public function testProcessEmbededResourcesNoAvailableEmbeds()
    {
        $transformer = $this->getMockForAbstractClass('League\Fractal\TransformerAbstract');

        $manager = new Manager;
        $manager->setRequestedScopes(array('foo'));

        $transformer->setManager($manager);

        $scope = new Scope($manager);
        $this->assertFalse($transformer->processEmbededResources($scope, array('some' => 'data')));
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testProcessEmbededResourcesInvalidEmbed()
    {
        $transformer = $this->getMockForAbstractClass('League\Fractal\TransformerAbstract');

        $manager = new Manager;
        $manager->setRequestedScopes(array('foo'));

        $transformer->setManager($manager);
        $scope = new Scope($manager);

        $transformer->setAvailableEmbeds(array('foo'));
        $transformer->processEmbededResources($scope, array('some' => 'data'));
    }

    public function testProcessEmbededResources()
    {
        // $transformer = m::mock('League\Fractal\TransformerAbstract');
        // $transformer->shouldReceive('embedFoo')->once()->andReturn(array('some' => 'data'));

        // $transformer = $this->getMockForAbstractClass('League\Fractal\TransformerAbstract');
        // $transformer->expects($this->once())->method('embedFoo');

        eval('class SomeFancyTransformer extends League\Fractal\TransformerAbstract {
            public function transform($data)
            {
                return $data;
            }

            public function embedBar($data)
            {
                return $this->item($data, function() { return array(\'embedded\' => \'thing\'); });
            }
        };');

        $transformer = new \SomeFancyTransformer;

        $manager = new Manager;
        $manager->setRequestedScopes(array('bar'));

        $transformer->setManager($manager);
        $scope = new Scope($manager);

        $transformer->setAvailableEmbeds(array('foo', 'bar'));

        $embedded = $transformer->processEmbededResources($scope, array('meh'));

        $this->assertEquals(array('bar' => array('data' => array('embedded' => 'thing'))), $embedded);
    }

    /**
     * @covers League\Fractal\TransformerAbstract::collection
     */
    public function testCollection()
    {
        $mock = Mockery::mock('League\Fractal\TransformerAbstract');
        $result = $mock->collection(array(), function () {});
        $this->assertInstanceOf('League\Fractal\Resource\Collection', $result);

    }

    /**
     * @covers League\Fractal\TransformerAbstract::paginatedCollection
     */
    public function testPaginatedCollection()
    {
        $mock = Mockery::mock('League\Fractal\TransformerAbstract');
        $paginator = Mockery::mock('Illuminate\Pagination\Paginator');
        $paginator->shouldReceive('getCollection')->once()->andReturn(array());
        $result = $mock->paginatedCollection($paginator, function () {});
        $this->assertInstanceOf('League\Fractal\Resource\PaginatedCollection', $result);
    }
}
