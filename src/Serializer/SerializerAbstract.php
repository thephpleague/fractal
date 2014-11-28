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
    /**
     * Serialize a collection.
     *
     * @param string $resourceKey
     * @param array  $data
     * @param string $scopeIdentifier
     *
     * @return array
     */
    abstract public function collection($resourceKey, array $data, $scopeIdentifier);

    /**
     * Serialize an item.
     *
     * @param string $resourceKey
     * @param array  $data
     * @param string $scopeIdentifier
     *
     * @return array
     */
    abstract public function item($resourceKey, array $data, $scopeIdentifier);

    /**
     * Serialize the included data.
     *
     * @param string $resourceKey
     * @param array  $data
     *
     * @return array
     */
    abstract public function includedData($resourceKey, array $data);

    /**
     * Serialize the meta.
     *
     * @param array $meta
     *
     * @return array
     */
    abstract public function meta(array $meta);

    /**
     * Serialize the paginator.
     *
     * @param PaginatorInterface $paginator
     *
     * @return array
     */
    abstract public function paginator(PaginatorInterface $paginator);

    /**
     * Serialize the cursor.
     *
     * @param CursorInterface $cursor
     *
     * @return array
     */
    abstract public function cursor(CursorInterface $cursor);

    /**
     * Indicates if includes should be side-loaded.
     *
     * @return bool
     */
    public function sideloadIncludes()
    {
        return false;
    }
}
