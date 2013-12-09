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

class ResourceManager
{
    protected $requestedScopes = array();
    
    public function getRequestedScopes()
    {
        return $this->requestedScopes;
    }
    
    public function setRequestedScopes(array $requestedScopes)
    {
        $this->requestedScopes = $requestedScopes;
        return $this;
    }

    public function createRootData($resource)
    {
        return $this->createData($resource);
    }

    public function createData($resource, $scopeIdentifier = null, $parentScopeInstance = null)
    {
        $scopeInstance = new Scope($this, $scopeIdentifier);

        // Update scope history
        if ($parentScopeInstance !== null) {
            
            // This will be the new childs list of partents (parents parents, plus the parent)
            $scopeArray = $parentScopeInstance->getParentScopes();
            $scopeArray[] = $parentScopeInstance->getCurrentScope();

            $scopeInstance->setParentScopes($scopeArray);
        }

        // if's n shit
        if ($resource instanceof ItemResource) {
            $data = $this->processItem($scopeInstance, $resource);
        } elseif ($resource instanceof CollectionResource) {
            $data = $this->processCollection($scopeInstance, $resource);
        } elseif ($resource instanceof PaginatorResource) {
            $data = $this->processPaginator($scopeInstance, $resource);
        } else {
            throw new \InvalidArgumentException(
                'Argument $resource should be an instance of ItemResource, CollectionResource or PaginatorResource'
            );
        }

        // So, this data is the current scope data
        $scopeInstance->setCurrentData($data);

        return $scopeInstance;
    }

    protected function getCallableTransformer(ResourceInterface $resource)
    {
        $transformer = $resource->getTransformer();

        if (is_callable($transformer)) {
            return $transformer;
        }

        return new $transformer;
    }

    protected function fireTransformer($transformer, Scope $scope, $data)
    {
        // Fire Main Transformer
        if (is_callable($transformer)) {
            $processedData = call_user_func($transformer, $data);

        } else {
            $processedData = call_user_func(array($transformer, 'transform'), $data);

            // If its an object, process potential embeded resources
            if ($transformer instanceof TransformerAbstract) {
                $embededData = $transformer->processEmbededResources($scope, $data);

                // Push the new embeds in with the main data
                $processedData = array_merge($processedData, $embededData);
            }
        }
        
        return $processedData;
    }


    protected function processItem($scope, ItemResource $resource)
    {
        $transformer = $this->getCallableTransformer($resource);
        return $this->fireTransformer($transformer, $scope, $resource->getData());
    }

    protected function processCollection($scope, CollectionResource $resources)
    {
        $transformer = $this->getCallableTransformer($resources);

        $data = array();
        foreach ($resources->getData() as $itemData) {
            $data []= $this->fireTransformer($transformer, $scope, $itemData);
        }
        return $data;
    }

    protected function processPaginator($scope, PaginatorResource $resources)
    {
        $transformer = $this->getCallableTransformer($resources);

        $data = array();
        foreach ($resources->getData() as $itemData) {
            $data []= $this->fireTransformer($transformer, $scope, $itemData);
        }
        return $data;
    }
}
