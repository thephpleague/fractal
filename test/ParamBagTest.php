<?php

namespace League\Fractal\Test;

use League\Fractal\ParamBag;
use LogicException;
use PHPUnit\Framework\TestCase;

class ParamBagTest extends TestCase
{
    public function testOldFashionedGet()
    {
        $params = new ParamBag(['one' => 'potato', 'two' => 'potato2']);

        $this->assertSame('potato', $params->get('one'));
        $this->assertSame('potato2', $params->get('two'));
    }

    public function testGettingValuesTheOldFashionedWayArray()
    {
        $params = new ParamBag(['one' => ['potato', 'tomato']]);

        $this->assertSame(['potato', 'tomato'], $params->get('one'));
    }

    public function testArrayAccess()
    {
        $params = new ParamBag(['foo' => 'bar', 'baz' => 'ban']);

        $this->assertInstanceOf('ArrayAccess', $params);
        $this->assertArrayHasKey('foo', $params);
        $this->assertTrue(isset($params['foo']));
        $this->assertSame('bar', $params['foo']);
        $this->assertSame('ban', $params['baz']);
        $this->assertNull($params['totallymadeup']);
    }

    public function testArrayAccessSetFails()
    {
        $this->expectExceptionObject(new LogicException('Modifying parameters is not permitted'));

        $params = new ParamBag(['foo' => 'bar']);

        $params['foo'] = 'someothervalue';
    }

    public function testArrayAccessUnsetFails()
    {
        $this->expectExceptionObject(new LogicException('Modifying parameters is not permitted'));

        $params = new ParamBag(['foo' => 'bar']);

        unset($params['foo']);
    }

    public function testObjectAccess()
    {
        $params = new ParamBag(['foo' => 'bar', 'baz' => 'ban']);

        $this->assertSame('bar', $params->foo);
        $this->assertSame('ban', $params->baz);
        $this->assertNull($params->totallymadeup);
        $this->assertTrue(isset($params->foo));
    }

    public function testObjectAccessSetFails()
    {
        $this->expectExceptionObject(new LogicException('Modifying parameters is not permitted'));

        $params = new ParamBag(['foo' => 'bar']);

        $params->foo = 'someothervalue';
    }

    public function testObjectAccessUnsetFails()
    {
        $this->expectExceptionObject(new LogicException('Modifying parameters is not permitted'));

        $params = new ParamBag(['foo' => 'bar']);

        unset($params->foo);
    }
}
