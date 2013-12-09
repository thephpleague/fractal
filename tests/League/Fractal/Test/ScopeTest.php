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
        $this->assertInstanceOf('League\Fractal\Scope', $scope->setCurrentData(array('foo' => 'bar')));
    }

    /**
     * @covers League\Fractal\Scope::getCurrentData
     */
    public function testGetRequestedScopes()
    {
        $manager = m::mock('League\Fractal\ResourceManager');
        $scope = new Scope($manager, 'book');
        $scope->setCurrentData(array('foo' => 'bar'));
        $this->assertEquals($scope->getCurrentData(), array('foo' => 'bar'));
    }
}
