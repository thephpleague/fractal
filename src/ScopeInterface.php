<?php

namespace League\Fractal;

use League\Fractal\Resource\ResourceInterface;

interface ScopeInterface
{
    public function getManager(): ManagerInterface;

    public function getResource(): ResourceInterface;

    public function toArray(): ?array;

    public function getScopeIdentifier(): ?string;

    public function getIdentifier(?string $appendIdentifier = null): string;

    public function embedChildScope(string $scopeIdentifier, ResourceInterface $resource): ScopeInterface;

    public function pushParentScope(string $identifierSegment): int;

    public function getParentScopes();

    public function isExcluded(string $checkScopeSegment): bool;

    public function isRequested(string $checkScopeSegment): bool;

    public function transformPrimitiveResource();

    /**
     * @param list<string> $parentScopes
     */
    public function setParentScopes(array $parentScopes): self;
}
