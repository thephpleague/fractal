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

use League\Fractal\Pagination\CursorInterface;
use League\Fractal\Pagination\PaginatorInterface;

abstract class SerializerAbstract
{
    abstract public function serializeData($resourceKey, array $data);
    abstract public function serializeIncludedData($resourceKey, array $data);
    abstract public function serializePaginator(PaginatorInterface $paginator);
    abstract public function serializeCursor(CursorInterface $cursor);
    abstract public function serializeMeta(array $meta);

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
