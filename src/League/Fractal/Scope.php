<?php namespace League\Fractal;

class Scope
{
    protected $currentData;

    protected $currentScope;

    protected $manager;

    protected $parentScopes = [];

    public function __construct(ResourceManager $resourceManager, $currentScope)
    {
        $this->resourceManager = $resourceManager;
        $this->currentScope = $currentScope;
    }
    
    /**
     * Setter for currentData
     *
     * @param mixed $parentScopes Value to set
     *
     * @return self
     */
    public function setCurrentData($currentData)
    {
        $this->currentData = $currentData;
        return $this;
    }

    /**
     * Getter for currentScope
     *
     * @return mixed
     */
    public function getCurrentScope()
    {
        return $this->currentScope;
    }

    /**
     * Getter for parentScopes
     *
     * @return mixed
     */
    public function getParentScopes()
    {
        return $this->parentScopes;
    }
    
    /**
     * Setter for parentScopes
     *
     * @param mixed $parentScopes Value to set
     *
     * @return self
     */
    public function setParentScopes($parentScopes)
    {
        $this->parentScopes = $parentScopes;
        return $this;
    }
    
    public function embedChildScope($scopeIdentifier, $resource)
    {
        return [
            'data' => $this->resourceManager->createData($resource, $scopeIdentifier, $this)->toArray(),
        ];
    }
    
    public function isRequested($checkScopeSegment)
    {
        if ($this->parentScopes) {
            $scopeArray = array_slice($this->parentScopes, 1);
            array_push($scopeArray, $this->currentScope, $checkScopeSegment);
        } else {
            $scopeArray = [$checkScopeSegment];
        }

        $scopeString = implode('.', (array) $scopeArray);

        $checkAgainstArray = $this->resourceManager->getRequestedScopes();

        return in_array($scopeString, $checkAgainstArray);
    }

    public function pushParentScope($newScope)
    {
        return array_push($this->parentScopes, $newScope);
    }

    public function toArray()
    {
        return (array) $this->currentData;
    }

    public function toJson()
    {
        return json_encode($this->toArray());
    }
}
