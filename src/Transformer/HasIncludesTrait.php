<?php

declare(strict_types=1);

namespace League\Fractal\Transformer;

use League\Fractal\Resource\Primitive;
use League\Fractal\Resource\ResourceInterface;
use League\Fractal\ScopeInterface;

trait HasIncludesTrait
{
    /**
     * Resources that can be included if requested.
     */
    protected array $availableIncludes = [];

    /**
     * Include resources without needing it to be requested.
     */
    protected array $defaultIncludes = [];

    /**
     * Getter for availableIncludes.
     */
    public function getAvailableIncludes(): array
    {
        return $this->availableIncludes;
    }

    /**
     * Getter for defaultIncludes.
     */
    public function getDefaultIncludes(): array
    {
        return $this->defaultIncludes;
    }

    /**
     * Figure out which includes we need.
     */
    private function figureOutWhichIncludes(ScopeInterface $scope): array
    {
        $includes = $this->getDefaultIncludes();

        foreach ($this->getAvailableIncludes() as $include) {
            if ($scope->isRequested($include)) {
                $includes[] = $include;
            }
        }

        foreach ($includes as $include) {
            if ($scope->isExcluded($include)) {
                $includes = array_diff($includes, [$include]);
            }
        }

        return $includes;
    }

    /**
     * This method is fired to loop through available includes, see if any of
     * them are requested and permitted for this scope.
     *
     * @internal
     *
     * @param mixed $data
     */
    public function processIncludedResources(ScopeInterface $scope, $data): ?array
    {
        $includedData = [];

        $includes = $this->figureOutWhichIncludes($scope);

        foreach ($includes as $include) {
            $includedData = $this->includeResourceIfAvailable(
                $scope,
                $data,
                $includedData,
                $include
            );
        }

        return $includedData === [] ? null : $includedData;
    }

    /**
     * Include a resource only if it is available on the method.
     *
     * @param mixed $data
     */
    private function includeResourceIfAvailable(
        ScopeInterface $scope,
                       $data,
        array          $includedData,
        string         $include
    ): array {
        if ($resource = $this->callIncludeMethod($scope, $include, $data)) {
            $childScope = $scope->embedChildScope($include, $resource);

            if ($childScope->getResource() instanceof Primitive) {
                $includedData[$include] = $childScope->transformPrimitiveResource();
            } else {
                $includedData[$include] = $childScope->toArray();
            }
        }

        return $includedData;
    }

    /**
     * Call Include Method.
     *
     * @internal
     *
     * @param mixed $data
     *
     * @throws \Exception
     */
    protected function callIncludeMethod(ScopeInterface $scope, string $includeName, $data): ?ResourceInterface
    {
        $scopeIdentifier = $scope->getIdentifier($includeName);

        $params = $scope->getManager()->getIncludeParams($scopeIdentifier);

        // Check if the method name actually exists
        $methodName = $this->buildMethodName($includeName);

        $resource = call_user_func([$this, $methodName], $data, $params, $scope);

        if ($resource === null) {
            return null;
        }

        if (! $resource instanceof ResourceInterface) {
            throw new \Exception(sprintf(
                'Invalid return value from %s::%s(). Expected %s, received %s.',
                __CLASS__,
                $methodName,
                'League\Fractal\Resource\ResourceInterface',
                is_object($resource) ? get_class($resource) : gettype($resource)
            ));
        }

        return $resource;
    }

    /**
     * Setter for availableIncludes.
     */
    public function setAvailableIncludes(array $availableIncludes): self
    {
        $this->availableIncludes = $availableIncludes;

        return $this;
    }

    /**
     * Setter for defaultIncludes.
     */
    public function setDefaultIncludes(array $defaultIncludes): self
    {
        $this->defaultIncludes = $defaultIncludes;

        return $this;
    }
}
