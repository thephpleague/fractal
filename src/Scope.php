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
     * Get Current Scope
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

    /**
     * Is Requested
     *
     * Check if - in relation to the current scope - this specific segment is allowed.
     * That means, if a.b.c is requested and the current scope is a.b, then c is allowed. If the current
     * scope is a then c is not allowed, even if it is there and potentially transformable.
     *
     * @internal
     *
     * @param $checkScopeSegment
     *
     * @return int Returns the new number of elements in the array.
     */
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
     * Push Parent Scope
     *
     * Push a scope identifier into parentScopes
     *
     * @internal
     *
     * @param string $identifierSegment
     *
     * @return int Returns the new number of elements in the array.
     **/
    public function pushParentScope($identifierSegment)
    {
        return array_push($this->parentScopes, $identifierSegment);
    }

    /**
     * Set parent scopes
     *
     * @internal
     * @param mixed $parentScopes Value to set
     * @return $this
     **/
    public function setParentScopes($parentScopes)
    {
        $this->parentScopes = $parentScopes;

        return $this;
    }

    /**
     * Convert the current data for this scope to an array
     *
     * @api
     * @return array
     **/
    public function toArray()
    {
        $serializer = $this->manager->getSerializer();
        $resourceKey = $this->resource->getResourceKey();

        list($data, $includedData) = $this->executeResourceTransformers();

        $data = $serializer->serializeData($resourceKey, $data);

        // If the serializer wants the includes to be side-loaded then we'll
        // serialize the included data and merge it with the data.
        if ($serializer->sideloadIncludes()) {
            $includedData = $serializer->serializeIncludedData($resourceKey, $includedData);

            $data = array_merge($data, $includedData);
        }

        if ($this->resource instanceof Collection) {

            if ($this->resource->hasCursor()) {
                $pagination = $serializer->serializeCursor($this->resource->getCursor());

            } elseif ($this->resource->hasPaginator()) {
                $pagination = $serializer->serializePaginator($this->resource->getPaginator());
            }

            if (! empty($pagination)) {
                $this->resource->setMetaValue(key($pagination), current($pagination));
            }
        }

        // Pull out all of OUR metadata and any custom meta data to merge with the main level data
        $meta = $serializer->serializeMeta($this->resource->getMeta());

        return array_merge($data, $meta);
    }

    /**
     * Convert the current data for this scope to JSON
     *
     * @api
     * @return string
     **/
    public function toJson()
    {
        return json_encode($this->toArray());
    }

    /**
     * Execute the resources transformer and return the data and included data.
     *
     * @internal
     * @return array
     */
    protected function executeResourceTransformers()
    {
        $transformer = $this->resource->getTransformer();
        $data = $this->resource->getData();

        $transformedData = $includedData = array();

        if ($this->resource instanceof Item) {
            list ($transformedData, $includedData[]) = $this->fireTransformer($transformer, $data);

        } elseif ($this->resource instanceof Collection) {
            foreach ($data as $value) {
                list ($transformedData[], $includedData[]) = $this->fireTransformer($transformer, $value);
            }
        } else {
            throw new InvalidArgumentException(
                'Argument $resource should be an instance of Resource\Item or Resource\Collection'
            );
        }

        return array($transformedData, $includedData);
    }
   
    /**
     * Fire the main transformer.
     *
     * @internal
     * @param  callable|\League\Fractal\TransformerAbstract  $transformer
     * @param  mixed  $data
     * @return array
     */
    protected function fireTransformer($transformer, $data)
    {
        $transformedData = $includedData = array();

        if (is_callable($transformer)) {
            $transformedData = call_user_func($transformer, $data);
        } else {
            $transformedData = $transformer->transform($data);
        }
            
        if ($this->transformerHasIncludes($transformer)) {
            $includedData = $this->fireIncludedTransformers($transformer, $data);

            // If the serializer does not want the includes to be side-loaded then
            // the included data must be merged with the transformed data.
            if (! $this->manager->getSerializer()->sideloadIncludes()) {
                $transformedData = array_merge($transformedData, $includedData);
            }
        }
        
        return array($transformedData, $includedData);
    }

    /**
     * Fire the included transformers.
     *
     * @internal
     * @param  \League\Fractal\TransformerAbstract  $transformer
     * @param  mixed  $data
     * @return array
     **/
    protected function fireIncludedTransformers($transformer, $data)
    {
        $this->availableIncludes = $transformer->getAvailableIncludes();

        return $transformer->processIncludedResources($this, $data) ?: array();
    }

    /**
     * Determine if a transformer has any available includes.
     *
     * @internal
     * @param  callable|\League\Fractal\TransformerAbstract  $transformer
     * @return bool
     **/
    protected function transformerHasIncludes($transformer)
    {
        if (! $transformer instanceof TransformerAbstract) {
            return false;
        }
        
        $defaultIncludes = $transformer->getDefaultIncludes();
        $availableIncludes = $transformer->getAvailableIncludes();
        return ! empty($defaultIncludes) or ! empty($availableIncludes);
    }
}
