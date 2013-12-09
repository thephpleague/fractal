<?php namespace League\Fractal\Test;

use League\Fractal\ResourceManager;
use League\Fractal\Scope;

class TransformerAbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers League\Fractal\TransformerAbstract::setManager
     */
    public function testsetManager()
    {
        $transformer = $this->getMockForAbstractClass('League\Fractal\TransformerAbstract');
        $manager = new ResourceManager;
        $this->assertInstanceOf('League\Fractal\TransformerAbstract', $transformer->setManager($manager));
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
}
