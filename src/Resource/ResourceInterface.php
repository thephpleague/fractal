<?php

/*
 * This file is part of the League\Fractal package.
 *
 * (c) Phil Sturgeon <me@philsturgeon.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\Fractal\Resource;

interface ResourceInterface
{
    /**
     * Get the data.
     *
     * @return array|ArrayIterator
     */
    public function getData();

    /**
     * Get the transformer.
     *
     * @return callable|string
     */
    public function getTransformer();
}
