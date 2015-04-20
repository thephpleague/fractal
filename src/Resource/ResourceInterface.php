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
     * @return mixed
     */
    public function getData();

    /**
     * Get the transformer.
     *
     * @return callable|string
     */
    public function getTransformer();

    /**
     * Set the data.
     *
     * @param mixed $data
     * @return \League\Fractal\Resource\ResourceAbstract
     */
    public function setData($data);

    /**
     * Set the transformer.
     *
     * @param callable|string $transformer
     * @return \League\Fractal\Resource\ResourceAbstract
     */
    public function setTransformer($transformer);
}
