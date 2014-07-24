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

class JsonApiSerializer extends ArraySerializer
{
    /**
     * Serialize an item resource
     *
     * @param string $resourceKey
     * @param array $data
     *
     * @return array
     */
    public function collection($resourceKey, array $data)
    {
        return array($resourceKey ?: 'data' => $data);
    }

    /**
     * Serialize an item resource
     *
     * @param string $resourceKey
     * @param array $data
     *
     * @return array
     */
    public function item($resourceKey, array $data)
    {
        return array($resourceKey ?: 'data' => array($data));
    }

    /**
     * Serialize the included data.
     *
     * @param  string  $resourceKey
     * @param  array  $data
     *
     * @return array
     */
    public function includedData($resourceKey, array $data)
    {
        $serializedData = array();

        foreach ($data as $value) {
            foreach ($value as $includeKey => $includeValue) {
                $serializedData = array_merge_recursive($serializedData, $includeValue);
            }
        }

        return empty($serializedData) ? array() : array('linked' => $serializedData);
    }

    /**
     * Indicates if includes should be side-loaded.
     *
     * @return bool
     */
    public function sideloadIncludes()
    {
        return true;
    }
}
