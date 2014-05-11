<?php namespace League\Fractal\Test;

use League\Fractal\Resource\Item;
use League\Fractal\Resource\Collection;
use League\Fractal\Manager;
use Mockery;

class ManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testParseIncludes()
    {
        $manager = new Manager();

        // Does a CSV string work
        $manager->parseIncludes('foo,bar');
        $this->assertEquals(array('foo', 'bar'), $manager->getRequestedIncludes());

        // Does a big array of stuff work
        $manager->parseIncludes(array('foo', 'bar', 'bar.baz'));
        $this->assertEquals(array('foo', 'bar', 'bar.baz'), $manager->getRequestedIncludes());

        // Do requests for `baz.bart` also request `baz`?
        $manager->parseIncludes(array('foo.bar'));
        $this->assertEquals(array('foo', 'foo.bar'), $manager->getRequestedIncludes());

        // See if fancy syntax works
        $manager->parseIncludes('foo:limit(5|1):order(-something)');
        $this->assertEquals(array('limit' => array('5', '1'), 'order' => array('-something')), $manager->getIncludeParams('foo'));
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
