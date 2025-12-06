<?php

namespace League\Fractal\Test\Pagination;

use League\Fractal\Pagination\PaginatorCountTrait;
use League\Fractal\Test\Stub\SimpleTraversable;
use PHPUnit\Framework\TestCase;

class PaginatorCountTraitTest extends TestCase
{
    protected $instance;

    /** @before */
    public function before()
    {
        $this->instance = new class {
            use PaginatorCountTrait;

            public function getIterableCountPublic(iterable $iterable)
            {
                return $this->getIterableCount($iterable);
            }

            public function getTraversableCountPublic(\Traversable $traversable)
            {
                return $this->getTraversableCount($traversable);
            }
        };
    }

    /**
     * @dataProvider arrayProvider
     */
    public function testSupportsIterables(array $data)
    {
        $count = count($data);
        $this->assertEquals($count, $this->instance->getIterableCountPublic($data));
        $this->assertEquals($count, $this->instance->getIterableCountPublic(new \ArrayIterator($data)));
        $this->assertEquals($count, $this->instance->getIterableCountPublic(new SimpleTraversable($data)));
    }

    /**
     * @dataProvider arrayProvider
     */
    public function testSupportsTraversables(array $data)
    {
        $count = count($data);
        $this->assertEquals($count, $this->instance->getTraversableCountPublic(new \ArrayIterator($data)));
        $this->assertEquals($count, $this->instance->getTraversableCountPublic(new SimpleTraversable($data)));
    }

    public function arrayProvider()
    {
        return [
            [[]],
            [[1, 2, 3]],
            [range(1, 100)],
        ];
    }
}
