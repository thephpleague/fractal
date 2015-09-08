<?php

/*
 * This file is part of the League\Fractal package.
 *
 * (c) Phil Sturgeon <me@philsturgeon.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\Fractal\Resource;

abstract class ResourceAbstract implements ResourceInterface
{
    /**
     * Any item to process.
     *
     * @var mixed
     */
    protected $data;

    /**
     * Array of meta data.
     *
     * @var array
     */
    protected $meta = [];

    /**
     * The resource key.
     *
     * @var string
     */
    protected $resourceKey;

    /**
     * A callable to process the data attached to this resource.
     *
     * @var callable|string
     */
    protected $transformer;

    /**
     * Create a new resource instance.
     *
     * @param mixed           $data
     * @param callable|string $transformer
     * @param string          $resourceKey
     *
     * @return void
     */
    public function __construct($data = null, $transformer = null, $resourceKey = null)
    {
        $this->data = $data;
        $this->transformer = $transformer;
        $this->resourceKey = $resourceKey;
    }

    /**
     * Get the data.
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set the data.
     *
     * @param mixed $data
     * @return \League\Fractal\Resource\ResourceAbstract
     */
    public function setData($data)
    {
         $this->data = $data;

         return $this;
    }

    /**
     * Get the meta data.
     *
     * @return array
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * Get the meta data.
     *
     * @param string $metaKey
     *
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
     * Set the transformer.
     *
     * @param callable|string $transformer
     * @return \League\Fractal\Resource\ResourceAbstract
     */
    public function setTransformer($transformer)
    {
        $this->transformer = $transformer;

        return $this;
    }

    /**
     * Set the meta data.
     *
     * @param array $meta
     *
     * @return $this
     */
    public function setMeta(array $meta)
    {
        $this->meta = $meta;

        return $this;
    }

    /**
     * Set the meta data.
     *
     * @param string $metaKey
     * @param mixed  $metaValue
     *
     * @return $this
     */
    public function setMetaValue($metaKey, $metaValue)
    {
        $this->meta[$metaKey] = $metaValue;

        return $this;
    }

    /**
     * Set the resource key.
     *
     * @param string $resourceKey
     *
     * @return \League\Fractal\Resource\ResourceAbstract
     */
    public function setResourceKey($resourceKey)
    {
        $this->resourceKey = $resourceKey;

        return $this;
    }
}
