<?php namespace League\Fractal\Test;

use League\Fractal\Scope;
use Mockery as m;

class ScopeTest extends \PHPUnit_Framework_TestCase
{
     /**
     * @covers League\Fractal\Scope::setCurrentData
     */
    public function testSetCurrentData()
    {
        $manager = m::mock('League\Fractal\ResourceManager');
        $scope = new Scope($manager, 'book');
        $this->assertInstanceOf('League\Fractal\Scope', $scope->setCurrentData(['foo' => 'bar']));
    }

    /**
     * @covers League\Fractal\Scope::getCurrentData
     */
    public function testGetRequestedScopes()
    {
        $manager = m::mock('League\Fractal\ResourceManager');
        $scope = new Scope($manager, 'book');
        $scope->setCurrentData(['foo' => 'bar']);
        $this->assertEquals($scope->getCurrentData(), ['foo' => 'bar']);
    }
}
