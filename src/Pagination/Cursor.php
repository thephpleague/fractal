<?php

/*
 * This file is part of the League\Fractal package.
 *
 * (c) Phil Sturgeon <email@philsturgeon.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\Fractal\Pagination;

/**
 * A generic cursor adapter.
 *
 * @author Isern Palaus <ipalaus@ipalaus.com>
 * @author Michele Massari <michele@michelemassari.net>
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
     * Prev cursor value
     *
     * @var mixed
     */
    protected $prev;

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
    public function __construct($current = null, $prev = null, $next = null, $count = null)
    {
        $this->current = $current;
        $this->prev = $prev;
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
     * @return League\Fractal\Pagination\PaginatorInterface
     */
    public function setCurrent($current)
    {
        $this->current = $current;
        return $this;
    }

    /**
     * Get the prev cursor value.
     *
     * @return mixed
     */
    public function getPrev()
    {
        return $this->prev;
    }

    /**
     * Set the prev cursor value.
     *
     * @param  mixed $prev
     * @return League\Fractal\Pagination\PaginatorInterface
     */
    public function setPrev($prev)
    {
        $this->prev = $prev;
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
     * @return League\Fractal\Pagination\PaginatorInterface
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
     * @return League\Fractal\Pagination\PaginatorInterface
     */
    public function setCount($count)
    {
        $this->count = $count;
        return $this;
    }
}
