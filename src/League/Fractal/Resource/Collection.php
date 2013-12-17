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

use Illuminate\Pagination\Paginator;

/**
 * Resource Collection
 *
 * The data can be a collection of any sort of data, as long as the 
 * "collection" is either array or an object implementing ArrayIterator.
 */
class Collection implements ResourceInterface
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
     * @var Illuminate\Pagination\Paginator
     */
    protected $paginator;

    /**
     * A callable to process the data attached to this resource
     *
     * @var callable|string
     */
    protected $transformer;

    /**
     * @param array|ArrayIterator $data
     * @param callable|string $transformer
     */
    public function __construct($data, $transformer)
    {
        $this->data = $data;
        $this->transformer = $transformer;
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
     * @return Illuminate\Pagination\Paginator
     */
    public function getPaginator()
    {
        return $this->paginator;
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
     * @param Illuminate\Pagination\Paginator
     *
     * @return self
     */
    public function setPaginator(Paginator $paginator)
    {
        $this->paginator = $paginator;
        return $this;
    }
}
