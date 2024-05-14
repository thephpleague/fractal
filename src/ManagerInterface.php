<?php

namespace League\Fractal;

use League\Fractal\Resource\ResourceInterface;
use League\Fractal\Serializer\Serializer;

interface ManagerInterface
{
    public function getSerializer(): Serializer;

    public function getIncludeParams(string $include): ParamBag;

    public function getFieldset(string $type): ?ParamBag;

    public function getRequestedFieldsets(): array;

    public function getRequestedIncludes(): array;

    public function getRequestedExcludes(): array;

    public function createData(
        ResourceInterface $resource,
        ?string           $scopeIdentifier = null,
        ScopeInterface    $parentScopeInstance = null
    ): ScopeInterface;
}
