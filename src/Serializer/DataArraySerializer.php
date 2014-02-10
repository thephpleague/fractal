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
     * Serializes output for a given resource.
     *
     * @param  array $object
     * @return array
     */
    public function serialize(array $object)
    {
        $output = array();

        foreach ($object as $key => $value) {
            // Don't output null value top-level items.
            // This behavior should be serializer specific.
            if (is_null($value)) {
                continue;
            }

            $method = 'process'.ucwords($key);
            if (method_exists($this, $method)) {
                $this->{$method}($value, $output);
            } else {
                $output[$key] = $value;
            }

        }

        return $output;
    }

    /**
     * Generates output for paginator adapters.
     *
     * @param League\Fractal\Cursor\PaginatorInterface $paginator
     * @param array $output
     */
    public function processPaginator(PaginatorInterface $paginator, &$output)
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

        $output['pagination'] = $pagination;
    }

    /**
     * Generates output for cursor adapters. We don't type hint current/next
     * because they can be either a string or a integer.
     *
     * @param League\Fractal\Cursor\CursorInterface $cursor
     * @param array $output
     */
    public function processCursor(CursorInterface $cursor, &$output)
    {
        $output['cursor'] = array(
            'current' => $cursor->getCurrent(),
            'next' => $cursor->getNext(),
            'count' => (int) $cursor->getCount(),
        );
    }
}
