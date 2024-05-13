<?php

namespace League\Fractal\Transformer;

use League\Fractal\ScopeInterface;

interface HasIncludesInterface
{
    /**
     * @return list<non-empty-string>
     */
    public function getAvailableIncludes(): array;

    /**
     * @return list<non-empty-string>
     */
    public function getDefaultIncludes(): array;

    /**
     * @param mixed $data
     * @return mixed
     */
    public function processIncludedResources(ScopeInterface $scope, $data);
}
