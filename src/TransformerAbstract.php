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
use League\Fractal\Resource\ResourceAbstract;
use League\Fractal\Scope;

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
     * Resources that can be included if requested
     *
     * @var array
     */
    protected $availableIncludes = array();

    /**
     * Include resources without needing it to be requested
     *
     * @var array
     */
    protected $defaultIncludes;
    
    /**
     * The transformer should know about the current scope, so we can fetch relevant params
     *
     * @var \League\Fractal\Scope
     */
    protected $currentScope;

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
     **/
    public function getDefaultIncludes()
    {
        return $this->defaultIncludes;
    }

    /**
     * Getter for currentScope
     *
     * @return \League\Fractal\Scope
     **/
    public function getCurrentScope()
    {
        return $this->currentScope;
    }

    /**
     * This method is fired to loop through available embeds,
     * see if any of them are requested and permitted for this
     * scope.
     *
     * @internal
     * @param Scope $scope
     * @param $data
     * @return array
     **/
    public function processIncludedResources(Scope $scope, $data)
    {
        $embeddedData = array();
        $embeddedDataCount = 0;

        // Nothing to do, bail
        if (is_array($this->defaultIncludes)) {

            foreach ($this->defaultIncludes as $defaultInclude) {

                if (! ($resource = $this->callIncludeMethod($scope, $defaultInclude, $data))) {
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

                if (! ($resource = $this->callIncludeMethod($scope, $potentialInclude, $data))) {
                    continue;
                }

                $childScope = $scope->embedChildScope($potentialInclude, $resource);

                $embeddedData[$potentialInclude] = $childScope->toArray();
                ++$embeddedDataCount;
            }
        }

        return $embeddedDataCount === 0 ? false : $embeddedData;
    }

    /**
     * Call Include Method
     *
     * @internal
     * @param \League\Fractal\Scope $scope
     * @param string $includeName
     * @param mixed $data
     * @throws \Exception
     * @return \League\Fractal\Resource\ResourceInterface
     */
    protected function callIncludeMethod(Scope $scope, $includeName, $data)
    {
        $scopeIdentifier = $scope->getIdentifier($includeName);
        $params = $scope->getManager()->getIncludeParams($scopeIdentifier);

        // Check if the method name actually exists
        $methodName = 'include'.str_replace(' ', '', ucwords(str_replace('_', ' ', $includeName)));

        $resource = call_user_func(array($this, $methodName), $data, $params);

        if ($resource === null) {
            return false;
        }

        if (! $resource instanceof ResourceAbstract) {
            throw new \Exception(sprintf(
                'Invalid return value from %s::%s(). Expected %s, received %s.',
                __CLASS__,
                $methodName,
                'League\Fractal\Resource\ResourceAbstract',
                gettype($resource)
            ));
        }

        return $resource;
    }

    /**
     * Setter for availableIncludes
     *
     * @api
     * @param $availableIncludes
     * @return $this
     */
    public function setAvailableIncludes($availableIncludes)
    {
        $this->availableIncludes = $availableIncludes;
        return $this;
    }

    /**
     * Setter for defaultIncludes
     *
     * @api
     * @param $defaultIncludes
     * @return $this
     **/
    public function setDefaultIncludes($defaultIncludes)
    {
        $this->defaultIncludes = $defaultIncludes;
        return $this;
    }

    /**
     * Setter for currentScope
     *
     * @api
     * @param $currentScope
     * @return $this
     **/
    public function setCurrentScope($currentScope)
    {
        $this->currentScope = $currentScope;
        return $this;
    }

    /**
     * Create a new item resource object
     *
     * @api
     * @param $data
     * @param $transformer
     * @param $resourceKey
     * @return \League\Fractal\Resource\Item
     **/
    protected function item($data, $transformer, $resourceKey = null)
    {
        return new Item($data, $transformer, $resourceKey);
    }

    /**
     * Create a new collection resource object
     *
     * @api
     * @param $data
     * @param $transformer
     * @param $resourceKey
     * @return \League\Fractal\Resource\Collection
     */
    protected function collection($data, $transformer, $resourceKey = null)
    {
        return new Collection($data, $transformer, $resourceKey);
    }
}
