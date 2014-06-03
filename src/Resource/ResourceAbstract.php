<?php

namespace League\Fractal\Resource;

abstract class ResourceAbstract implements ResourceInterface
{
    /**
     * Any item to process
     *
     * @var mixed
     */
    protected $data;

    /**
     * Array of meta data
     *
     * @var array
     */
    protected $meta = array();

    /**
     * The resource key.
     *
     * @var string
     */
    protected $resourceKey;

    /**
     * A callable to process the data attached to this resource
     *
     * @var callable|string
     */
    protected $transformer;

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
     * Get the meta data
     *
     * @return array
     **/
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * Get the meta data
     *
     * @param $metaKey
     * @return array
     */
    public function getMetaValue($metaKey)
    {
        return $this->meta[$metaKey];
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
     * Set the meta data
     *
     * @param string $metaKey
     * @param mixed $metaValue
     *
     * @return $this
     **/
    public function setMetaValue($metaKey, $metaValue)
    {
        $this->meta[$metaKey] = $metaValue;
        return $this;
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
}
