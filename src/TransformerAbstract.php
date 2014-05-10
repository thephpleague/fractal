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

use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\ResourceInterface;

/**
 * Transformer Abstract
 *
 * All Transformer classes should extend this to utilize the convenience methods 
 * collectionResource(), itemResource() and paginatorResource(), and make 
 * the self::$availableIncludes property available. Extends it and add a `transform()`
 * method to transform any data into a basic array, including embedded content.
 */
abstract class TransformerAbstract
{
    /**
     * Embed if requested
     *
     * @var array
     */
    protected $availableIncludes;

    /**
     * Embed without needing it to be requested
     *
     * @var array
     */
    protected $defaultIncludes;

    /**
     * Allow transformer to access its scope
     *
     * @var League\Fractal\Scope
     */
    protected $scope;

    /**
     * Getter for availableIncludes
     *
     * @return array
     */
    public function getAvailableIncludes()
    {
        return $this->availableIncludes;
    }

    /**
     * Getter for defaultIncludes
     *
     * @return array
     */
    public function getDefaultIncludes()
    {
        return $this->defaultIncludes;
    }

    /**
     * This method is fired to loop through available embeds,
     * see if any of them are requested and permitted for this
     * scope.
     *
     * @param Scope $scope
     * @param $data
     * @return array
     */
    public function processEmbeddedResources(Scope $scope, $data)
    {
        $embeddedData = array();
        $embeddedDataCount = 0;

        // Nothing to do, bail
        if (is_array($this->defaultIncludes)) {

            foreach ($this->defaultIncludes as $defaultInclude) {
                if (! ($resource = $this->callIncludeMethod($defaultInclude, $data))) {
                    continue;
                }

                $childScope = $scope->embedChildScope($defaultInclude, $resource);

                $embeddedData[$defaultInclude] = $childScope->toArray();
                ++$embeddedDataCount;
            }
        }

        // Nothing more to do? Bail
        if (is_array($this->availableIncludes)) {

            foreach ($this->availableIncludes as $potentialInclude) {
                // Check if an available embed is requested
                if (! $scope->isRequested($potentialInclude)) {
                    continue;
                }

                if (! ($resource = $this->callIncludeMethod($potentialInclude, $data))) {
                    continue;
                }

                $childScope = $scope->embedChildScope($potentialInclude, $resource);

                $embeddedData[$potentialInclude] = $childScope->toArray();
                ++$embeddedDataCount;
            }
        }

        return $embeddedDataCount === 0 ? false : $embeddedData;
    }

    protected function callIncludeMethod($embed, $data)
    {
        // Check if the method name actually exists
        $methodName = 'embed'.str_replace(' ', '', ucwords(str_replace('_', ' ', $embed)));

        $resource = call_user_func(array($this, $methodName), $data);

        if ($resource === null) {
            return false;
        }

        if (! $resource instanceof ResourceInterface) {
            throw new \Exception(sprintf(
                'Invalid return value from %s::%s(). Expected %s, received %s.',
                __CLASS__,
                $methodName,
                'League\Fractal\Resource\ResourceInterface',
                gettype($resource)
            ));
        }

        return $resource;
    }

    /**
     * Setter for availableIncludes
     *
     * @param array $availableIncludes
     * @return $this
     */
    public function setAvailableIncludes(array $availableIncludes)
    {
        $this->availableIncludes = $availableIncludes;
        return $this;
    }

    /**
     * Setter for defaultIncludes
     *
     * @param array $defaultIncludes
     * @return $this
     **/
    public function setDefaultIncludes($defaultIncludes)
    {
        $this->defaultIncludes = $defaultIncludes;
        return $this;
    }

    /**
     * Create a new item resource object
     *
     * @param $data
     * @param $transformer
     * @return League\Fractal\Resource\Item
     **/
    protected function item($data, $transformer)
    {
        return new Item($data, $transformer);
    }

    /**
     * Create a new collection resource object
     *
     * @param $data
     * @param $transformer
     * @return League\Fractal\Resource\Collection
     **/
    protected function collection($data, $transformer)
    {
        return new Collection($data, $transformer);
    }

    /**
     * Create a new collection resource object
     *
     * @param $data
     * @internal param $transformer
     * @return mixed
     */
    protected function param($data)
    {
        return [get_class($this->scope)];
    }
}
