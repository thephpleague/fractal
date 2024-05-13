<?php namespace League\Fractal\Test;

use InvalidArgumentException;
use League\Fractal\Manager;
use League\Fractal\ParamBag;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Mockery;
use PHPUnit\Framework\TestCase;

class ManagerTest extends TestCase
{
    public function testParseIncludeSelfie()
    {
        $manager = new Manager();

        // Test that some includes provided returns self
        $this->assertInstanceOf(get_class($manager), $manager->parseIncludes(['foo']));
    }

    public function testInvalidParseInclude()
    {
		$this->expectExceptionObject(new InvalidArgumentException('The parseIncludes() method expects a string or an array. NULL given'));

        $manager = new Manager();

        $manager->parseIncludes(null);
    }

    public function testIceTParseInclude()
    {
		$this->expectExceptionObject(new InvalidArgumentException('The parseIncludes() method expects a string or an array. integer given'));

        $manager = new Manager();

        $manager->parseIncludes(99);
    }

    public function testParseIncludes()
    {
        $manager = new Manager();

        // Does a CSV string work
        $manager->parseIncludes('foo,bar');

        $this->assertSame(['foo', 'bar'], $manager->getRequestedIncludes());

        // Does a big array of stuff work
        $manager->parseIncludes(['foo', 'bar', 'bar.baz']);
        $this->assertSame(['foo', 'bar', 'bar.baz'], $manager->getRequestedIncludes());

        // Are repeated things stripped
        $manager->parseIncludes(['foo', 'foo', 'bar']);
        $this->assertSame(['foo', 'bar'], $manager->getRequestedIncludes());

        $manager->parseIncludes(['foo.bar', 'foo:limit(10|1).bar']);
        $this->assertSame(['foo', 'foo.bar'], $manager->getRequestedIncludes());
        $this->assertSame(['10', '1'], $manager->getIncludeParams('foo')->get('limit'));

        // Do requests for `baz.bart` also request `baz`?
        $manager->parseIncludes(['foo.bar']);
        $this->assertSame(['foo', 'foo.bar'], $manager->getRequestedIncludes());


        // See if fancy syntax works
        $manager->parseIncludes('foo:limit(5|1):order(-something):anotherparam');

        $params = $manager->getIncludeParams('foo');

        $this->assertInstanceOf('League\Fractal\ParamBag', $params);


        $this->assertSame(['5', '1'], $params['limit']);
        $this->assertSame(['-something'], $params['order']);
        $this->assertSame([''], $params['anotherparam']);

        $this->assertNull($params['totallymadeup']);

        // Relation with params and sub relation
        $manager->parseIncludes('foo:limit(5|1):order(name).bar,baz');

        $params = $manager->getIncludeParams('foo');

        $this->assertInstanceOf('League\Fractal\ParamBag', $params);

        $this->assertSame(['5', '1'], $params['limit']);
        $this->assertSame(['name'], $params['order']);
        $this->assertSame(['foo', 'foo.bar', 'baz'], $manager->getRequestedIncludes());
    }

    public function testParseExcludeSelfie()
    {
        $manager = new Manager();

        // Test that some excludes provided returns self
        $this->assertInstanceOf(get_class($manager), $manager->parseExcludes(['foo']));
    }

    public function testInvalidParseExclude()
    {
		$this->expectExceptionObject(new InvalidArgumentException('The parseExcludes() method expects a string or an array. NULL given'));

        $manager = new Manager();

        $manager->parseExcludes(null);
    }

    public function testIceTParseExclude()
    {
		$this->expectExceptionObject(new InvalidArgumentException('The parseExcludes() method expects a string or an array. integer given'));

        $manager = new Manager();

        $manager->parseExcludes(99);
    }

    public function testParseExcludes()
    {
        $manager = new Manager();

        // Does a CSV string work
        $manager->parseExcludes('foo,bar');

        $this->assertSame(['foo', 'bar'], $manager->getRequestedExcludes());

        // Does a big array of stuff work
        $manager->parseExcludes(['foo', 'bar', 'bar.baz']);
        $this->assertSame(['foo', 'bar', 'bar.baz'], $manager->getRequestedExcludes());

        // Are repeated things stripped
        $manager->parseExcludes(['foo', 'foo', 'bar']);
        $this->assertSame(['foo', 'bar'], $manager->getRequestedExcludes());

        // Do requests for `baz.bart` also request `baz`?
        $manager->parseExcludes(['foo.bar']);
        $this->assertSame(['foo.bar'], $manager->getRequestedExcludes());
    }

    public function testRecursionLimiting()
    {
        $manager = new Manager();

        // Should limit to 10 by default
        $manager->parseIncludes('a.b.c.d.e.f.g.h.i.j.NEVER');

        $this->assertSame(
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

        $manager->parseIncludes('a:limit(5|1).b.c.d.e.f.g.h.i.j.NEVER');

        $this->assertSame(
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

        $this->assertSame(
            [
                'a',
                'a.b',
                'a.b.c',
            ],
            $manager->getRequestedIncludes()
        );

        $manager->parseIncludes('a:limit(5|1).b.c.NEVER');

        $this->assertSame(
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

        $this->assertInstanceOf('League\Fractal\ScopeInterface', $rootScope);


        $this->assertSame(['data' => ['foo' => 'bar']], $rootScope->toArray());
        $this->assertSame('{"data":{"foo":"bar"}}', $rootScope->toJson());

        // Collection
        $resource = new Collection([['foo' => 'bar']], function (array $data) {
            return $data;
        });

        $rootScope = $manager->createData($resource);

        $this->assertInstanceOf('League\Fractal\ScopeInterface', $rootScope);


        $this->assertSame(['data' => [['foo' => 'bar']]], $rootScope->toArray());
        $this->assertSame('{"data":[{"foo":"bar"}]}', $rootScope->toJson());

    }

    public function testParseFieldsets()
    {
        $manager = new Manager();

        $fields = [
            'articles' => 'title,body',
            'people' => 'name'
        ];

        $expectedFieldset = [
            'articles' => ['title' , 'body'],
            'people' => ['name']
        ];

        $manager->parseFieldsets($fields);
        $this->assertSame($expectedFieldset, $manager->getRequestedFieldsets());

        $paramBag = new ParamBag($expectedFieldset['articles']);
        $this->assertEquals($paramBag, $manager->getFieldset('articles'));

        // Are repeated fields stripped
        $manager->parseFieldsets(['foo' => 'bar,baz,bar']);
        $this->assertSame(['foo' => ['bar', 'baz']], $manager->getRequestedFieldsets());

        // Are empty fields stripped
        $manager->parseFieldsets(['foo' => 'bar,']);
        $this->assertSame(['foo' => ['bar']], $manager->getRequestedFieldsets());

        // Verify you can send in arrays directly
        $manager->parseFieldsets(['foo' => ['bar', 'baz']]);
        $this->assertSame(['foo' => ['bar', 'baz']], $manager->getRequestedFieldsets());

        $this->assertSame(null, $manager->getFieldset('inexistent'));
    }

    public function tearDown(): void
    {
        Mockery::close();
    }
}
