<?php namespace League\Fractal\Test;

use League\Fractal;

class ResourceManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testSetRequestedScopes()
    {
        $manager = new Fractal\ResourceManager();
        $this->assertInstanceOf('League\Fractal\ResourceManager', $manager->setRequestedScopes(array('foo')));
    }

    public function testGetRequestedScopes()
    {
        $manager = new Fractal\ResourceManager();
        $manager->setRequestedScopes(array('foo', 'bar', 'baz.bart'));
        $this->assertEquals($manager->getRequestedScopes(), array('foo', 'bar', 'baz.bart'));
    }

    public function testCreateData()
    {
        $manager = new Fractal\ResourceManager();

        $resource = new Fractal\ItemResource(array('foo' => 'bar'), function (array $data) {
            return $data;
        });

        $rootScope = $manager->createData($resource);
        
        $this->assertInstanceOf('League\Fractal\Scope', $rootScope);
        $this->assertEquals($rootScope->toArray(), array('foo' => 'bar'));
    }
}
