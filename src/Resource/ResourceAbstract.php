<?php

namespace League\Fractal\Resource;

abstract class ResourceAbstract
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
     * The resource key.
     * 
     * @var string
     */
    protected $resourceKey;

    /**
     * Create a new resource instance.
     * 
     * @param  mixed  $data
     * @param  callable|string  $transformer
     * @param  string  $resourceKey
     */
    public function __construct($data, $transformer, $resourceKey = null)
    {
        $this->data = $data;
        $this->transformer = $transformer;
        $this->resourceKey = $resourceKey;
    }
    
    /**
     * Get the data.
     *
     * @return array|ArrayIterator
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get the transformer.
     *
     * @return callable|string
     */
    public function getTransformer()
    {
        return $this->transformer;
    }

    /**
     * Set the resource key.
     * 
     * @param  string  $resourceKey
     * @return \League\Fractal\Resource\ResourceAbstract
     */
    public function setResourceKey($resourceKey)
    {
        $this->resourceKey = $resourceKey;
        return $this;
    }

    /**
     * Get the resource key.
     * 
     * @return string
     */
    public function getResourceKey()
    {
        return $this->resourceKey;
    }
}
