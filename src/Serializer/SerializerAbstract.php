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

use League\Fractal\Pagination\CursorInterface;
use League\Fractal\Pagination\PaginatorInterface;

abstract class SerializerAbstract
{
    abstract public function item($resourceKey, array $data);
    abstract public function collection($resourceKey, array $data);

    /**
     * @param \League\Fractal\Resource\ResourceInterface $resourceKey
     */
    abstract public function includedData($resourceKey, array $data);
    abstract public function paginator(PaginatorInterface $paginator);
    abstract public function cursor(CursorInterface $cursor);
    abstract public function meta(array $meta);

    /**
     * Indicates if includes should be side-loaded
     *
     * @return bool
     */
    public function sideloadIncludes()
    {
        return false;
    }
}
