<?php namespace League\Fractal\Test\Resource;

use League\Fractal\Resource\Primitive;
use Mockery;
use PHPUnit\Framework\TestCase;

class PrimitiveTest extends TestCase
{
    protected $simplePrimitive = 'sample string';

    public function testGetData()
    {
        $primitive = new Primitive($this->simplePrimitive);

        $this->assertSame($primitive->getData(), $this->simplePrimitive);
    }

    public function testGetTransformer()
    {
        $primitive = new Primitive($this->simplePrimitive, function () {});

        $this->assertTrue(is_callable($primitive->getTransformer()));

        $transformer = 'thismightbeacallablestring';
        $primitive = new Primitive($this->simplePrimitive, $transformer);

        $this->assertSame($primitive->getTransformer(), $transformer);
    }

    /**
     * @covers \League\Fractal\Resource\Primitive::setResourceKey
     */
    public function testSetResourceKey()
    {
        $primitive = Mockery::mock('League\Fractal\Resource\Primitive')->makePartial();

        $this->assertSame($primitive, $primitive->setResourceKey('foo'));
    }

    /**
     * @covers \League\Fractal\Resource\Primitive::getResourceKey
     */
    public function testGetResourceKey()
    {
        $primitive = Mockery::mock('League\Fractal\Resource\Primitive')->makePartial();
        $primitive->setResourceKey('foo');

        $this->assertSame('foo', $primitive->getResourceKey());
    }
}
