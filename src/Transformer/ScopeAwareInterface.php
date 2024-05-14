<?php

declare(strict_types=1);

namespace League\Fractal\Transformer;

use League\Fractal\Scope;

/**
 * For backward compatibility only. You should avoid to use this interface.
 */
interface ScopeAwareInterface
{
    public function getCurrentScope(): ?Scope;

    public function setCurrentScope(Scope $currentScope): self;
}
