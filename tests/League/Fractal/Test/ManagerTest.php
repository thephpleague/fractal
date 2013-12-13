<?php namespace League\Fractal\Test;

use League\Fractal\Resource\Item;
use League\Fractal\Resource\Collection;
use League\Fractal\Manager;
use Mockery;

class ManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testSetRequestedScopes()
    {
        $manager = new Manager();
        $this->assertInstanceOf('League\Fractal\Manager', $manager->setRequestedScopes(array('foo')));
    }

    public function testGetRequestedScopes()
    {
        $manager = new Manager();
        $manager->setRequestedScopes(array('foo', 'bar', 'baz', 'baz.bart'));
        $this->assertEquals(array('foo', 'bar', 'baz', 'baz.bart'), $manager->getRequestedScopes());

        $manager = new Manager();
        $manager->setRequestedScopes(array('foo', 'bar', 'baz.bart'));
        $this->assertEquals(array('foo', 'bar', 'baz', 'baz.bart'), $manager->getRequestedScopes());
    }

    public function testCreateDataWithCallback()
    {
        $manager = new Manager();

        $resource = new Item(array('foo' => 'bar'), function (array $data) {
            return $data;
        });

        $rootScope = $manager->createData($resource);

        $this->assertInstanceOf('League\Fractal\Scope', $rootScope);

        $this->assertEquals(array('data' => array('foo' => 'bar')), $rootScope->toArray());
        $this->assertEquals('{"data":{"foo":"bar"}}', $rootScope->toJson());

    }

    public function tearDown()
    {
        Mockery::close();
    }
}
