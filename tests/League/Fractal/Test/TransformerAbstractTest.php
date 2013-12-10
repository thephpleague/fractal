<?php namespace League\Fractal\Test;

use League\Fractal\ResourceManager;
use League\Fractal\Scope;
// use Mockery as m;

class TransformerAbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers League\Fractal\TransformerAbstract::setManager
     */
    public function testSetManager()
    {
        $transformer = $this->getMockForAbstractClass('League\Fractal\TransformerAbstract');
        $manager = new ResourceManager;
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
        $manager = new ResourceManager;
        $transformer->setManager($manager);
        $this->assertEquals($transformer->getManager(), $manager);
    }

    public function testProcessEmbededResourcesNoAvailableEmbeds()
    {
        $transformer = $this->getMockForAbstractClass('League\Fractal\TransformerAbstract');
       
        $manager = new ResourceManager;
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
       
        $manager = new ResourceManager;
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
                return $this->itemResource($data, function() { return array(\'embedded\' => \'thing\'); });
            }
        };');

        $transformer = new \SomeFancyTransformer;

        $manager = new ResourceManager;
        $manager->setRequestedScopes(array('bar'));

        $transformer->setManager($manager);
        $scope = new Scope($manager);

        $transformer->setAvailableEmbeds(array('foo', 'bar'));

        $embedded = $transformer->processEmbededResources($scope, array('meh'));

        $this->assertEquals(array('bar' => array('data' => array('embedded' => 'thing'))), $embedded);
    }
}
