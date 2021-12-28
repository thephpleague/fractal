<?php

/*
 * This file is part of the League\Fractal package.
 *
 * (c) Phil Sturgeon <me@philsturgeon.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\Fractal;

/**
 * A handy interface for getting at include parameters.
 */
class ParamBag implements \ArrayAccess, \IteratorAggregate
{
    /**
     * @var array
     */
    protected array $params = [];

    /**
     * Create a new parameter bag instance.
     *
     * @param array $params
     *
     * @return void
     */
    public function __construct(array $params)
    {
        $this->params = $params;
    }

    /**
     * Get parameter values out of the bag.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get(string $key): mixed
    {
        return $this->__get($key);
    }

    /**
     * Get parameter values out of the bag via the property access magic method.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get(string $key)
    {
        return $this->params[$key] ?? null;
    }

    /**
     * Check if a param exists in the bag via an isset() check on the property.
     *
     * @param string $key
     *
     * @return bool
     */
    public function __isset(string $key)
    {
        return isset($this->params[$key]);
    }

    /**
     * Disallow changing the value of params in the data bag via property access.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     *@throws \LogicException
     *
     */
    public function __set(string $key, mixed $value)
    {
        throw new \LogicException('Modifying parameters is not permitted');
    }

    /**
     * Disallow unsetting params in the data bag via property access.
     *
     * @param string $key
     *
     * @return void
     *@throws \LogicException
     *
     */
    public function __unset(string $key)
    {
        throw new \LogicException('Modifying parameters is not permitted');
    }

    /**
     * Check if a param exists in the bag via an isset() and array access.
     *
     * @param string $offset
     *
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return $this->__isset($offset);
    }

    /**
     * Get parameter values out of the bag via array access.
     *
     * @param string $offset
     *
     * @return mixed
     */
    public function offsetGet($offset): mixed
    {
        return $this->__get($offset);
    }

    /**
     * Disallow changing the value of params in the data bag via array access.
     *
     * @param string $offset
     * @param mixed  $value
     *
     * @throws \LogicException
     *
     * @return void
     */
    public function offsetSet($offset, mixed $value): void
    {
        throw new \LogicException('Modifying parameters is not permitted');
    }

    /**
     * Disallow unsetting params in the data bag via array access.
     *
     * @param string $offset
     *
     * @throws \LogicException
     *
     * @return void
     */
    public function offsetUnset($offset): void
    {
        throw new \LogicException('Modifying parameters is not permitted');
    }

    /**
     * IteratorAggregate for iterating over the object like an array.
     *
     * @return \ArrayIterator
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->params);
    }
}
