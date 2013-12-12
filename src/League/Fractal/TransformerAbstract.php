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
     * Array of embeds available for this transformer
     *
     * @var array
     */
    protected $availableEmbeds;
    
    /**
     * A callable to process the data attached to this resource
     *
     * @var League\Fractal\Manager
     */
    protected $manager;

    /**
     * Getter for availableEmbeds
     *
     * @return self
     */
    public function getAvailableEmbeds()
    {
        return $this->availableEmbeds;
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
        if ($this->availableEmbeds === null) {
            return false;
        }

        $embededData = array();

        foreach ($this->availableEmbeds as $potentialEmbed) {
            if (! $scope->isRequested($potentialEmbed)) {
                continue;
            }

            $methodName = 'embed'.ucfirst($potentialEmbed);
            if (! is_callable(array($this, $methodName))) {
                throw new \BadMethodCallException(sprintf(
                    'Call to undefined method %s::%s()',
                    get_class($this),
                    $methodName
                ));
            }

            $resource = call_user_func(array($this, $methodName), $data);

            $childScope = $scope->embedChildScope($potentialEmbed, $resource);

            $embededData[$potentialEmbed] = $childScope->toArray();
        }

        return $embededData;
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

    /**
     * Create a new paginated collection
     *
     * @return League\Fractal\Resource\PaginatedCollection
     */
    protected function paginatedCollection($data, $transformer)
    {
        return new PaginatedCollection($data, $transformer);
    }
}
