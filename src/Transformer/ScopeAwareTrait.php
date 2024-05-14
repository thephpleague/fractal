<?php

declare(strict_types=1);

namespace League\Fractal\Transformer;

use League\Fractal\Scope;

/**
 * For backward compatibility only. You should avoid to use this trait.
 */
trait ScopeAwareTrait
{
    /**
     * The transformer should know about the current scope, so we can fetch relevant params.
     *
     * @deprecated Transformer must use Scope from method arguments.
     */
    protected ?Scope $currentScope = null;

    /**
     * Getter for currentScope.
     *
     * @deprecated Transformer must use Scope from method arguments.
     */
    public function getCurrentScope(): ?Scope
    {
        return $this->currentScope;
    }

    /**
     * Setter for currentScope.
     *
     * @deprecated Transformer must use Scope from method arguments.
     */
    public function setCurrentScope(Scope $currentScope): self
    {
        $this->currentScope = $currentScope;

        return $this;
    }
}
