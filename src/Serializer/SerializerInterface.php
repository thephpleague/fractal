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

use League\Fractal\Pagination\PaginatorInterface;
use League\Fractal\Cursor\CursorInterface;

/**
 * The interface for resource output serializers.
 */
interface SerializerInterface
{
    public function outputPaginator(PaginatorInterface $paginator);
    public function outputCursor(CursorInterface $cursor);
    public function serialize($data, array $embeds = null, PaginatorInterface $paginator = null, CursorInterface $cursor = null);
}
