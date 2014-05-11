<?php

/*
 * This file is part of the League\Fractal package.
 *
 * (c) Phil Sturgeon <email@philsturgeon.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 **/

namespace League\Fractal;

use InvalidArgumentException;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\ResourceAbstract;
use League\Fractal\Pagination\CursorInterface;
use League\Fractal\Pagination\PaginatorInterface;

class Scope
{
    protected $availableIncludes = array();

    protected $currentScope;

    protected $manager;

    protected $resource;

    protected $parentScopes = array();

    public function __construct(Manager $manager, ResourceAbstract $resource, $currentScope = null)
    {
        $this->manager = $manager;
        $this->currentScope = $currentScope;
        $this->resource = $resource;
    }

    public function embedChildScope($scopeIdentifier, $resource)
    {
        return $this->manager->createData($resource, $scopeIdentifier, $this);
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
     * Get the unique identifier for this scope
     *
     * @param string $appendIdentifier
     * @return string
     **/
    public function getIdentifier($appendIdentifier = null)
    {
        $identifierParts = array_merge($this->parentScopes, array($this->currentScope, $appendIdentifier));
        return implode('.', array_filter($identifierParts));
    }

    /**
     * Getter for parentScopes
     *
     * @return mixed
     **/
    public function getParentScopes()
    {
        return $this->parentScopes;
    }

    /**
     * Getter for manager
     *
     * @return \League\Fractal\Manager
     **/
    public function getManager()
    {
        return $this->manager;
    }

    public function isRequested($checkScopeSegment)
    {
        if ($this->parentScopes) {
            $scopeArray = array_slice($this->parentScopes, 1);
            array_push($scopeArray, $this->currentScope, $checkScopeSegment);
        } else {
            $scopeArray = array($checkScopeSegment);
        }

        $scopeString = implode('.', (array) $scopeArray);

        $checkAgainstArray = $this->manager->getRequestedIncludes();

        return in_array($scopeString, $checkAgainstArray);
    }

    /**
     * Push a scope identifier into parentScopes
     *
     * @param string $newScope
     *
     * @return int Returns the new number of elements in the array.
     **/
    public function pushParentScope($newScope)
    {
        return array_push($this->parentScopes, $newScope);
    }

    /**
     * Setter for parentScopes
     *
     * @param mixed $parentScopes Value to set
     *
     * @return self
     **/
    public function setParentScopes($parentScopes)
    {
        $this->parentScopes = $parentScopes;

        return $this;
    }

    /**
     * Convert the current data for this scope to an array
     *
     * @return array
     **/
    public function toArray()
    {
        list($data, $includedData) = $this->executeResourceTransformer();

        $serializer = $this->manager->getSerializer();
        $resourceKey = $this->resource->getResourceKey();

        $data = $serializer->serializeData($resourceKey, $data);
        $includedData = $serializer->serializeIncludedData($resourceKey, $includedData);
        $availableIncludes = $serializer->serializeAvailableIncludes($this->availableIncludes);

        $pagination = array();

        if ($this->resource instanceof Collection) {
            if ($this->resource->hasCursor()) {
                $pagination = $serializer->serializeCursor($this->resource->getCursor());
            } elseif ($this->resource->hasPaginator()) {
                $pagination = $serializer->serializePaginator($this->resource->getPaginator());
            }
        }

        return array_merge_recursive($data, $includedData, $availableIncludes, $pagination);
    }

    /**
     * Convert the current data for this scope to JSON
     *
     * @return string
     **/
    public function toJson()
    {
        return json_encode($this->toArray());
    }

    /**
     * Execute the resources transformer and return the data and included data.
     * 
     * @return array
     */
    protected function executeResourceTransformer()
    {
        $transformer = $this->resource->getTransformer();
        $data = $includedData = array();

        if ($this->resource instanceof Item) {
            $data = $this->fireTransformer($transformer, $this->resource->getData());
            
            if ($this->transformerHasIncludes($transformer)) {
                $includedData = $this->fireIncludedTransformers($transformer, $this->resource->getData());
            }
        } elseif ($this->resource instanceof Collection) {
            foreach ($this->resource->getData() as $item) {
                $data[] = $this->fireTransformer($transformer, $item);

                if ($this->transformerHasIncludes($transformer)) {
                    $includedData[] = $this->fireIncludedTransformers($transformer, $item);
                }
            }
        } else {
            throw new InvalidArgumentException(
                'Argument $resource should be an instance of Resource\Item or Resource\Collection'
            );
        }

        return array($data, $includedData);
    }

   
    /**
     * Fire the main transformer.
     * 
     * @param  callable|\League\Fractal\TransformerAbstract  $transformer
     * @param  mixed  $data
     * @return array
     */
    protected function fireTransformer($transformer, $data)
    {
        if (is_callable($transformer)) {
            return call_user_func($transformer, $data);
        }

        return $transformer->transform($data);
    }

    /**
     * Fire the included transformers.
     * 
     * @param  \League\Fractal\TransformerAbstract  $transformer
     * @param  mixed  $data
     * @return array
     */
    protected function fireIncludedTransformers($transformer, $data)
    {
        $this->availableIncludes = $transformer->getAvailableIncludes();

        return $transformer->processIncludedResources($this, $data) ?: array();
    }

    /**
     * Determine if a transformer has any available includes.
     * 
     * @param  callable|\League\Fractal\TransformerAbstract  $transformer
     * @return bool
     */
    protected function transformerHasIncludes($transformer)
    {
        if ($transformer instanceof TransformerAbstract) {
            $availableIncludes = $transformer->getAvailableIncludes();

            return ! empty($availableIncludes);
        }
        
        return false;
    }
}
