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

class ArraySerializer extends SerializerAbstract
{
    /**
     * Serialize the top level data.
     * 
     * @param  string  $resourceKey
     * @param  array  $data
     * @return array
     */
    public function serializeData($resourceKey, array $data)
    {
        return $data;
    }

    /**
     * Serialize the included data
     * 
     * @param  string  $resourceKey
     * @param  array  $data
     * @return array
     */
    public function serializeIncludedData($resourceKey, array $data)
    {
        return $data;
    }

    /**
     * Serialize the meta
     * 
     * @param  array  $meta
     * @return array
     */
    public function serializeMeta(array $meta)
    {
        if (empty($meta)) {
            return array();
        }

        return array('meta' => $meta);
    }

    /**
     * Serialize the paginator
     *
     * @param \League\Fractal\Pagination\PaginatorInterface $paginator
     * @return array
     **/
    public function serializePaginator(PaginatorInterface $paginator)
    {
        $currentPage = (int) $paginator->getCurrentPage();
        $lastPage = (int) $paginator->getLastPage();

        $pagination = array(
            'total' => (int) $paginator->getTotal(),
            'count' => (int) $paginator->getCount(),
            'per_page' => (int) $paginator->getPerPage(),
            'current_page' => $currentPage,
            'total_pages' => $lastPage,
        );

        $pagination['links'] = array();

        if ($currentPage > 1) {
            $pagination['links']['previous'] = $paginator->getUrl($currentPage - 1);
        }

        if ($currentPage < $lastPage) {
            $pagination['links']['next'] = $paginator->getUrl($currentPage + 1);
        }

        return array('pagination' => $pagination);
    }

    /**
     * Serialize the cursor
     * 
     * @param  \League\Fractal\Pagination\CursorInterface  $cursor
     * @return array
     **/
    public function serializeCursor(CursorInterface $cursor)
    {
        $cursor = array(
            'current' => $cursor->getCurrent(),
            'prev' => $cursor->getPrev(),
            'next' => $cursor->getNext(),
            'count' => (int) $cursor->getCount(),
        );

        return array('cursor' => $cursor);
    }
}
