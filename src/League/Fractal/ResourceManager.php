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
    }

    public function createRootData($resource)
    {
        return $this->createData($resource);
    }

    public function createData($resource, $scopeIdentifier = null, $parentScopeInstance = null)
    {
        $scopeInstance = new Scope($this, $scopeIdentifier);

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

        $scopeInstance->setCurrentData($data);

        return $scopeInstance;
    }

    protected function getCallableProcessor(ResourceInterface $resource)
    {
        $processor = $resource->getProcessor();

        if (is_callable($processor)) {
            return $processor;
        }

        return [new $processor, 'process'];
    }

    protected function processItem($scope, ItemResource $resource)
    {
        $callable = $this->getCallableProcessor($resource);
        return call_user_func($callable, $scope, $resource->getData());
    }

    protected function processCollection($scope, CollectionResource $resources)
    {
        $callable = $this->getCallableProcessor($resources);

        $data = [];
        foreach ($resources->getData() as $itemData) {
            $data []= call_user_func($callable, $scope, $itemData);
        }
        return $data;
    }

    protected function processPaginator($scope, PaginatorResource $resources)
    {
        $callable = $this->getCallableProcessor($resources);

        $data = [];
        foreach ($resources->getData() as $itemData) {
            $data []= call_user_func($callable, $scope, $itemData);
        }
        return $data;
    }

}
