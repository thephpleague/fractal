<?php

/*
 * This file is part of the League\Fractal package.
 *
 * (c) Phil Sturgeon <email@philsturgeon.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\Fractal\Cursor;

/**
 * A generic cursor adapter.
 *
 * @author Isern Palaus <ipalaus@ipalaus.com>
 */
class Cursor implements CursorInterface
{
    /**
     * Current cursor value.
     *
     * @var mixed
     */
    protected $current;

    /**
     * Next cursor value.
     *
     * @var mixed
     */
    protected $next;

    /**
     * Items being holded for the current cursor position.
     *
     * @var integer
     */
    protected $count;

    /**
     * Create a new Cursor instance.
     *
     * @param mixed   $current
     * @param mixed   $next
     * @param integer $count
     */
    public function __construct($current = null, $next = null, $count = null)
    {
        $this->current = $current;
        $this->next = $next;
        $this->count = $count;
    }

    /**
     * Get the current cursor value.
     *
     * @return mixed
     */
    public function getCurrent()
    {
        return $this->current;
    }

    /**
     * Set the current cursor value.
     *
     * @param  mixed $current
     * @return League\Fractal\Cursor\Cursor
     */
    public function setCurrent($current)
    {
        $this->current = $current;
        return $this;
    }

    /**
     * Get the next cursor value.
     *
     * @return mixed
     */
    public function getNext()
    {
        return $this->next;
    }

    /**
     * Set the next cursor value.
     *
     * @param  mixed $next
     * @return League\Fractal\Cursor\Cursor
     */
    public function setNext($next)
    {
        $this->next = $next;
        return $this;
    }

    /**
     * Returns the total items in the current cursor.
     *
     * @return integer
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * Set the total items in the current cursor.
     *
     * @param integer $count
     * @return League\Fractal\Cursor\Cursor
     */
    public function setCount($count)
    {
        $this->count = $count;
        return $this;
    }
}
