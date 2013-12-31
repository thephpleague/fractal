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
use League\Fractal\Resource\PaginatedCollection;
use League\Fractal\Resource\ResourceInterface;

/**
 * Transformer Abstract
 *
 * All Transformer classes should extend this to utilize the convenience methods 
 * collectionResource(), itemResource() and paginatorResource(), and make 
 * the self::$availableEmbeds property available. Extends it and add a `transform()`
 * method to transform any data into a basic array, including embedded content.
 */
abstract class TransformerAbstract
{
    /**
     * Embed if requested
     *
     * @var array
     */
    protected $availableEmbeds;

    /**
     * Embed without needing it to be requested
     *
     * @var array
     */
    protected $defaultEmbeds;
    
    /**
     * A callable to process the data attached to this resource
     *
     * @var League\Fractal\Manager
     */
    protected $manager;

    /**
     * Getter for availableEmbeds
     *
     * @return array
     */
    public function getAvailableEmbeds()
    {
        return $this->availableEmbeds;
    }

    /**
     * Getter for defaultEmbeds
     *
     * @return array
     */
    public function getDefaultEmbeds()
    {
        return $this->defaultEmbeds;
    }

    /**
     * Getter for manager
     *
     * @return League\Fractal\Manager
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * This method is fired to loop through available embeds,
     * see if any of them are requested and permitted for this 
     * scope.
     *
     * @return array
     */
    public function processEmbededResources(Scope $scope, $data)
    {
        $embededData = array();
        $embededDataCount = 0;

        // Nothing to do, bail
        if (is_array($this->defaultEmbeds)) {

            foreach ($this->defaultEmbeds as $defaultEmbed) {
                if (! ($resource = $this->callEmbedMethod($defaultEmbed, $data))) {
                    continue;
                }

                $childScope = $scope->embedChildScope($defaultEmbed, $resource);

                $embededData[$defaultEmbed] = $childScope->toArray();
                ++$embededDataCount;
            }
        }

        // Nothing more to do? Bail
        if (is_array($this->availableEmbeds)) {

            foreach ($this->availableEmbeds as $potentialEmbed) {
                // Check if an available embed is requested
                if (! $scope->isRequested($potentialEmbed)) {
                    continue;
                }

                if (! ($resource = $this->callEmbedMethod($potentialEmbed, $data))) {
                    continue;
                }

                $childScope = $scope->embedChildScope($potentialEmbed, $resource);

                $embededData[$potentialEmbed] = $childScope->toArray();
                ++$embededDataCount;
            }
        }

        return $embededDataCount === 0 ? false : $embededData;
    }

    protected function callEmbedMethod($embed, $data)
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
     * Setter for manager
     *
     * @return self
     */
    public function setManager($manager)
    {
        $this->manager = $manager;
        return $this;
    }

    /**
     * Setter for availableEmbeds
     *
     * @return self
     */
    public function setAvailableEmbeds($availableEmbeds)
    {
        $this->availableEmbeds = $availableEmbeds;
        return $this;
    }

    /**
     * Setter for defaultEmbeds
     *
     * @return self
     */
    public function setDefaultEmbeds($defaultEmbeds)
    {
        $this->defaultEmbeds = $defaultEmbeds;
        return $this;
    }

    /**
     * Create a new item resource object
     *
     * @return League\Fractal\Resource\Item
     */
    protected function item($data, $transformer)
    {
        return new Item($data, $transformer);
    }

    /**
     * Create a new collection resource object
     *
     * @return League\Fractal\Resource\Collection
     */
    protected function collection($data, $transformer)
    {
        return new Collection($data, $transformer);
    }
}
