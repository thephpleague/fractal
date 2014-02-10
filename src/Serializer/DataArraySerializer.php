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
 * Data Array Serializer
 *
 * This output serializer converts an item into a nested array.
 */
class DataArraySerializer implements SerializerInterface
{
    /**
     * Generates output for resource.
     *
     * @param  array $data
     * @param  array $embeds
     * @param  League\Fractal\Cursor\PaginatorInterface|null $paginator
     * @param  League\Fractal\Cursor\CursorInterface|null $cursor
     * @return array
     */
    public function serialize($data, array $embeds = null, PaginatorInterface $paginator = null, CursorInterface $cursor = null)
    {
        $output = array(
            'data' => $data,
        );

        if ($embeds) {
            $output['embeds'] = $embeds;
        }

        if ($paginator !== null and $paginator instanceof PaginatorInterface) {
            $output['pagination'] = $this->outputPaginator($paginator);
        }

        if ($cursor !== null and $cursor instanceof CursorInterface) {
            $output['cursor'] = $this->outputCursor($cursor);
        }

        return $output;
    }

    /**
     * Generates output for paginator adapters.
     *
     * @param  League\Fractal\Cursor\PaginatorInterface $paginator
     * @return array
     */
    public function outputPaginator(PaginatorInterface $paginator)
    {
        $currentPage = (int) $paginator->getCurrentPage();
        $lastPage = (int) $paginator->getLastPage();

        $pagination = array(
            'total' => (int) $paginator->getTotal(),
            'count' => (int) $paginator->count(),
            'per_page' => (int) $paginator->getPerPage(),
            'current_page' => $currentPage,
            'total_pages' => $lastPage,
        );

        $pagination['links'] = array();

        // $paginator->appends(array_except(Request::query(), ['page']));

        if ($currentPage > 1) {
            $pagination['links']['previous'] = $paginator->getUrl($currentPage - 1);
        }

        if ($currentPage < $lastPage) {
            $pagination['links']['next'] = $paginator->getUrl($currentPage + 1);
        }

        return $pagination;
    }

    /**
     * Generates output for cursor adapters. We don't type hint current/next
     * because they can be either a string or a integer.
     *
     * @param  League\Fractal\Cursor\CursorInterface $cursor
     * @return array
     */
    public function outputCursor(CursorInterface $cursor)
    {
        $cursor = array(
            'current' => $cursor->getCurrent(),
            'next' => $cursor->getNext(),
            'count' => (int) $cursor->getCount(),
        );

        return $cursor;
    }
}
