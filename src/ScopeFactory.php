<?php

/*
 * This file is part of the League\Fractal package.
 *
 * (c) Phil Sturgeon <me@philsturgeon.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\Fractal;

use League\Fractal\Resource\ResourceInterface;

class ScopeFactory implements ScopeFactoryInterface
{
    public function createScopeFor(
        ManagerInterface  $manager,
        ResourceInterface $resource,
        ?string           $scopeIdentifier = null
    ): ScopeInterface
    {
        return new Scope($manager, $resource, $scopeIdentifier);
    }

    public function createChildScopeFor(
        ManagerInterface  $manager,
        ScopeInterface    $parentScope,
        ResourceInterface $resource,
        ?string           $scopeIdentifier = null
    ): ScopeInterface
    {
        $scopeInstance = $this->createScopeFor($manager, $resource, $scopeIdentifier);

        // This will be the new children list of parents (parents parents, plus the parent)
        $scopeArray = $parentScope->getParentScopes();
        $scopeArray[] = $parentScope->getScopeIdentifier();

        $scopeInstance->setParentScopes($scopeArray);

        return $scopeInstance;
    }
}
