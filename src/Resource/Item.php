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

/**
 * Item Resource
 *
 * The Item Resource can stored any mixed data, usually an ORM, ODM or 
 * other sort of intelligent result, DataMapper model, etc but could 
 * be a basic array, object, or whatever you like.
 */
class Item implements ResourceInterface
{
    /**
     * Any item to process
     *
     * @var mixed
     */
    protected $data;
    
    /**
     * A callable to process the data attached to this resource
     *
     * @var callable|string
     */
    protected $transformer;

    /**
     * @param mixed $data
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
     * Getter for transformer
     *
     * @return callable|string
     */
    public function getTransformer()
    {
        return $this->transformer;
    }
}
