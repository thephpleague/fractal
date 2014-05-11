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

interface SerializerInterface
{
    public function serializeData($resourceKey, array $data);
    public function serializeIncludedData($resourceKey, array $data);
    public function serializePaginator(PaginatorInterface $paginator);
    public function serializeCursor(CursorInterface $cursor);
    public function serializeAvailableIncludes(array $includes);
}
