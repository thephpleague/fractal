<?php

/*
 * This file is part of the League\Fractal package.
 *
 * (c) Phil Sturgeon <me@philsturgeon.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\Fractal\Serializer;

class ExplicitNullArraySerializer extends ArraySerializer
{
    /**
     * Serialize null resource.
     *
     * @param string $resourceKey
     *
     * @return array|null
     */
    public function null($resourceKey)
    {
        return null;
    }
}
