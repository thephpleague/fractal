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

use League\Fractal\Transformer\HasIncludesInterface;
use League\Fractal\Transformer\HasIncludesTrait;
use League\Fractal\Transformer\IncludeMethodBuildTrait;
use League\Fractal\Transformer\ResourceCreateTrait;
use League\Fractal\Transformer\ScopeAwareInterface;
use League\Fractal\Transformer\ScopeAwareTrait;

/**
 * All Transformer classes should extend this to utilize the convenience methods
 * collection() and item(), and make the self::$availableIncludes property available.
 * Extend it and add a `transform()` method to transform any default or included data
 * into a basic array.
 *
 * @method transform(array $data, ScopeInterface $scope): array
 *
 * @deprecated You should build your own AbstractTransformer without ScopeAwareTrait.
 */
abstract class TransformerAbstract implements HasIncludesInterface, ScopeAwareInterface
{
    use HasIncludesTrait;
    use IncludeMethodBuildTrait;
    use ResourceCreateTrait;
    use ScopeAwareTrait;
}
