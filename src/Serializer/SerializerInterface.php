<?php

/*
 * This file is part of the League\Fractal package.
 *
 * (c) Phil Sturgeon <email@philsturgeon.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\Fractal\Serializer;

/**
 * The interface for resource output serializers.
 */
interface SerializerInterface
{
    /**
     * Serializes output for a given resource.
     *
     * @param  array $object
     * @return mixed
     */
    public function serialize(array $object);
}
