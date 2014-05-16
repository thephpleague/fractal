<?php

/*
 * This file is part of the League\Fractal package.
 *
 * (c) Phil Sturgeon <email@philsturgeon.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\Fractal;

use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\ResourceInterface;

/**
 * Transformer Interface
 *
 * All Transformer classes should extend the TransformerAbstract class
 * or implement this interface themselves
 */
interface TransformerInterface
{
    public function getAvailableEmbeds();
    public function setAvailableEmbeds($availableEmbeds);
    public function getDefaultEmbeds();
    public function setDefaultEmbeds($defaultEmbeds);
    public function processEmbededResources(Scope $scope, $data);
}
