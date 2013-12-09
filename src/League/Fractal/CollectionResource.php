<?php

/*
 * This file is part of the League\Fractal package.
 *
 * (c) Phil Sturgeon <email@philsturgeon.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\Fractal;

/**
 * Collection Resource
 *
 * The Collection Resource is really a collection of resources. The data can 
 * be a collection of any sort of data, as long as the "collection" is either 
 * array or an object implementing ArrayIterator.
 */
class CollectionResource implements ResourceInterface
{
    /**
     * A collection of data
     *
     * @var array|ArrayIterator
     */
    protected $data;
    
    /**
     * A callable to process the data attached to this resource
     *
     * @var callable|string
     */
    protected $processor;

    /**
     * @param array|ArrayIterator $data
     * @param callable|string $processor
     */
    public function __construct($data, $processor)
    {
        $this->data = $data;
        $this->processor = $processor;
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
     * Getter for processor
     *
     * @return callable|string
     */
    public function getProcessor()
    {
        return $this->processor;
    }
}
