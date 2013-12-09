<?php namespace League\Fractal;

class ResourceManager
{
    protected $requestedScopes = [];
    
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
            throw new Exceptions('No idea what type of resource format this is');
        }

        // So, this data is the current scope data
        $scopeInstance->setCurrentData($data);

        return $scopeInstance;
    }

    protected function getCallableProcessor(ResourceInterface $resource)
    {
        $processor = $resource->getProcessor();

        if (is_callable($processor)) {
            return $processor;
        }

        return new $processor;
    }

    protected function fireProcessor($processor, Scope $scope, $data)
    {
        // Fire Main Processor
        if (is_callable($processor)) {
            $processedData = call_user_func($processor, $data);

        } else {
            $processedData = call_user_func([$processor, 'process'], $data);

            // If its an object, process potential embeded resources
            if ($processor instanceof ProcessorAbstract) {
                $embededData = $processor->processEmbededResources($scope, $data);

                // Push the new embeds in with the main data
                $processedData = array_merge($processedData, $embededData);
            }
        }
        
        return $processedData;
    }


    protected function processItem($scope, ItemResource $resource)
    {
        $processor = $this->getCallableProcessor($resource);
        return $this->fireProcessor($processor, $scope, $resource->getData());
    }

    protected function processCollection($scope, CollectionResource $resources)
    {
        $processor = $this->getCallableProcessor($resources);

        $data = [];
        foreach ($resources->getData() as $itemData) {
            $data []= $this->fireProcessor($processor, $scope, $itemData);
        }
        return $data;
    }

    protected function processPaginator($scope, PaginatorResource $resources)
    {
        $processor = $this->getCallableProcessor($resources);

        $data = [];
        foreach ($resources->getData() as $itemData) {
            $data []= $this->fireProcessor($processor, $scope, $itemData);
        }
        return $data;
    }


}
