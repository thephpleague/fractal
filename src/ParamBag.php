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
 * A handy interface for getting at include parameters
 */
class ParamBag implements \ArrayAccess
{
	protected $params = array();

	public function __construct(array $params)
	{
		$this->params = $params;
	}

    public function get($key)
    {
        return $this->__get($key);
    }

    public function __get($key)
    {
        return isset($this->params[$key]) ? $this->params[$key] : null;
    }

    public function __isset($key)
    {
        return isset($this->params[$key]);
    }

    public function offsetExists($offset)
    {
    	return $this->__isset($offset);
    }

    public function offsetGet($offset)
    {
    	return $this->__get($offset);
    }

    public function offsetSet($offset, $value)
    {
    	throw new \Exception('NO SET FOR YOU!');
    }

    public function offsetUnset($offset)
    {
    	throw new \Exception('NO UNSET FOR YOU!');
    }
}
