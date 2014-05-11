<?php

/*
 * This file is part of the League\Fractal package.
 *
 * (c) Phil Sturgeon <email@philsturgeon.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\Fractal\Resource;

use League\Fractal\Pagination\CursorInterface;
use League\Fractal\Pagination\PaginatorInterface;

/**
 * Resource Collection
 *
 * The data can be a collection of any sort of data, as long as the
 * "collection" is either array or an object implementing ArrayIterator.
 */
class Collection extends ResourceAbstract
{
    /**
     * A collection of data
     *
     * @var array|ArrayIterator
     */
    protected $data;

    /**
     * A collection of data
     *
     * @var League\Fractal\Pagination\PaginatorInterface
     */
    protected $paginator;

    /**
     * Cursor implementation.
     *
     * @var League\Fractal\Pagination\CursorInterface
     */
    protected $cursor;

    /**
     * A callable to process the data attached to this resource
     *
     * @var callable|string
     */
    protected $transformer;

    /**
     * Array of meta data
     * 
     * @var array
     */
    protected $meta = array();

    /**
     * @param array|ArrayIterator $data
     * @param callable|string $transformer
     */
    public function __construct($data, $transformer, $resourceKey = null)
    {
        $this->data = $data;
        $this->transformer = $transformer;
        $this->resourceKey = $resourceKey;
    }

    /**
     * Getter for data
     *
     * @return array|ArrayIterator
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Getter for paginator
     *
     * @return League\Fractal\Pagination\PaginatorInterface
     */
    public function getPaginator()
    {
        return $this->paginator;
    }

    /**
     * Determine if the resouce has a paginator implementation.
     * 
     * @return bool
     */
    public function hasPaginator()
    {
        return $this->paginator instanceof PaginatorInterface;
    }

    /**
     * Set the cursor implementation.
     *
     * @return League\Fractal\Pagination\CursorInterface
     */
    public function getCursor()
    {
        return $this->cursor;
    }

    /**
     * Determine if the resouce has a cursor implementation.
     * 
     * @return bool
     */
    public function hasCursor()
    {
        return $this->cursor instanceof CursorInterface;
    }

    /**
     * Getter for transformer
     *
     * @return callable|string
     */
    public function getTransformer()
    {
        return $this->transformer;
    }

    /**
     * Setter for paginator
     *
     * @param League\Fractal\Pagination\PaginatorInterface $paginator
     *
     * @return self
     */
    public function setPaginator(PaginatorInterface $paginator)
    {
        $this->paginator = $paginator;
        return $this;
    }

    /**
     * Set the cursor implementation.
     *
     * @param League\Fractal\Pagination\CursorInterface $cursor
     */
    public function setCursor(CursorInterface $cursor)
    {
        $this->cursor = $cursor;
        return $this;
    }

    /**
     * Set the meta data
     * 
     * @param array $meta
     * 
     * @return self
     */
    public function setMeta(array $meta)
    {
        $this->meta = $meta;
        return $this;
    }

    /**
     * Get the meta data
     * 
     * @return array
     */
    public function getMeta()
    {
        return $this->meta;
    }
}
