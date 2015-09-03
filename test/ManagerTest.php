<?php namespace League\Fractal\Test;

use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Mockery;

class ManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testParseIncludeSelfie()
    {
        $manager = new Manager();

        // Test that some includes provided returns self
        $this->assertInstanceOf(get_class($manager), $manager->parseIncludes(['foo']));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The parseIncludes() method expects a string or an array. NULL given
     */
    public function testInvalidParseInclude()
    {
        $manager = new Manager();

        $manager->parseIncludes(null);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The parseIncludes() method expects a string or an array. integer given
     */
    public function testIceTParseInclude()
    {
        $manager = new Manager();

        $manager->parseIncludes(99);
    }

    public function testParseIncludes()
    {
        $manager = new Manager();

        // Does a CSV string work
        $manager->parseIncludes('foo,bar');
        $this->assertEquals(['foo', 'bar'], $manager->getRequestedIncludes());

        // Does a big array of stuff work
        $manager->parseIncludes(['foo', 'bar', 'bar.baz']);
        $this->assertEquals(['foo', 'bar', 'bar.baz'], $manager->getRequestedIncludes());

        // Are repeated things stripped
        $manager->parseIncludes(['foo', 'foo', 'bar']);
        $this->assertEquals(['foo', 'bar'], $manager->getRequestedIncludes());

        // Do requests for `baz.bart` also request `baz`?
        $manager->parseIncludes(['foo.bar']);
        $this->assertEquals(['foo', 'foo.bar'], $manager->getRequestedIncludes());

        // See if fancy syntax works
        $manager->parseIncludes('foo:limit(5|1):order(-something):anotherparam');

        $params = $manager->getIncludeParams('foo');

        $this->assertInstanceOf('League\Fractal\ParamBag', $params);

        $this->assertEquals(['5', '1'], $params['limit']);
        $this->assertEquals(['-something'], $params['order']);
        $this->assertEquals([''], $params['anotherparam']);
        $this->assertNull($params['totallymadeup']);
    }

    public function testRecursionLimiting()
    {
        $manager = new Manager();

        // Should limit to 10 by default
        $manager->parseIncludes('a.b.c.d.e.f.g.h.i.j.NEVER');
        $this->assertEquals(
            [
                'a',
                'a.b',
                'a.b.c',
                'a.b.c.d',
                'a.b.c.d.e',
                'a.b.c.d.e.f',
                'a.b.c.d.e.f.g',
                'a.b.c.d.e.f.g.h',
                'a.b.c.d.e.f.g.h.i',
                'a.b.c.d.e.f.g.h.i.j',
            ],
            $manager->getRequestedIncludes()
        );

        // Try setting to 3 and see what happens
        $manager->setRecursionLimit(3);
        $manager->parseIncludes('a.b.c.NEVER');
        $this->assertEquals(
            [
                'a',
                'a.b',
                'a.b.c',
            ],
            $manager->getRequestedIncludes()
        );
    }

    public function testCreateDataWithCallback()
    {
        $manager = new Manager();

        // Item
        $resource = new Item(['foo' => 'bar'], function (array $data) {
            return $data;
        });

        $rootScope = $manager->createData($resource);

        $this->assertInstanceOf('League\Fractal\Scope', $rootScope);

        $this->assertEquals(['data' => ['foo' => 'bar']], $rootScope->toArray());
        $this->assertEquals('{"data":{"foo":"bar"}}', $rootScope->toJson());

        // Collection
        $resource = new Collection([['foo' => 'bar']], function (array $data) {
            return $data;
        });

        $rootScope = $manager->createData($resource);

        $this->assertInstanceOf('League\Fractal\Scope', $rootScope);

        $this->assertEquals(['data' => [['foo' => 'bar']]], $rootScope->toArray());
        $this->assertEquals('{"data":[{"foo":"bar"}]}', $rootScope->toJson());
    }

    public function tearDown()
    {
        Mockery::close();
    }
}
