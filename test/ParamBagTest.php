<?php namespace League\Fractal\Test;

use League\Fractal\ParamBag;

class ParamBagTest extends \PHPUnit_Framework_TestCase
{
    public function testOldFashionedGet()
    {
        $params = new ParamBag(array('one' => 'potato', 'two' => 'potato2'));

        $this->assertSame('potato', $params->get('one'));
        $this->assertSame('potato2', $params->get('two'));
    }

    public function testGettingValuesTheOldFashionedWayArray()
    {
        $params = new ParamBag(array('one' => array('potato', 'tomato')));

        $this->assertSame(array('potato', 'tomato'), $params->get('one'));
    }

    public function testArrayAccess()
    {
        $params = new ParamBag(array('foo' => 'bar', 'baz' => 'ban'));

        $this->assertInstanceOf('ArrayAccess', $params);
        $this->assertArrayHasKey('foo', $params);
        $this->assertTrue(isset($params['foo']));
        $this->assertSame('bar', $params['foo']);
        $this->assertSame('ban', $params['baz']);
        $this->assertNull($params['totallymadeup']);
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Modifying parameters is not permitted
     */
    public function testArrayAccessSetFails()
    {
        $params = new ParamBag(array('foo' => 'bar'));

        $params['foo'] = 'someothervalue';
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Modifying parameters is not permitted
     */
    public function testArrayAccessUnsetFails()
    {
        $params = new ParamBag(array('foo' => 'bar'));

        unset($params['foo']);
    }

    public function testObjectAccess()
    {
        $params = new ParamBag(array('foo' => 'bar', 'baz' => 'ban'));

        $this->assertSame('bar', $params->foo);
        $this->assertSame('ban', $params->baz);
        $this->assertNull($params->totallymadeup);
        $this->assertTrue(isset($params->foo));
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Modifying parameters is not permitted
     */
    public function testObjectAccessSetFails()
    {
        $params = new ParamBag(array('foo' => 'bar'));

        $params->foo = 'someothervalue';
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Modifying parameters is not permitted
     */
    public function testObjectAccessUnsetFails()
    {
        $params = new ParamBag(array('foo' => 'bar'));

        unset($params->foo);
    }
}
