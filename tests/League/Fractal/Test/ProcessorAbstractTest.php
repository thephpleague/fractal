<?php namespace League\Fractal\Test;

use League\Fractal\ResourceManager;
use League\Fractal\Scope;

class ProcessorAbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers League\Fractal\ProcessorAbstract::setRequestedScopes
     */
    public function testsetManager()
    {
        $processor = $this->getMockForAbstractClass('League\Fractal\ProcessorAbstract');
        $manager = new ResourceManager;
        $this->assertInstanceOf('League\Fractal\ProcessorAbstract', $processor->setManager($manager));
    }

    /**
     * @covers League\Fractal\ProcessorAbstract::getRequestedScopes
     */
    public function testGetManager()
    {
        $processor = $this->getMockForAbstractClass('League\Fractal\ProcessorAbstract');
        $manager = new ResourceManager;
        $processor->setManager($manager);
        $this->assertEquals($processor->getManager(), $manager);
    }
}
