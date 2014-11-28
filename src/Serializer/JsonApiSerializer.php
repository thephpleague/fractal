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

use League\Fractal\Resource\ResourceInterface;

class JsonApiSerializer extends ArraySerializer
{
    /**
     * Serialize a collection.
     *
     * @param string $resourceKey
     * @param array  $data
     *
     * @return array
     */
    public function collection($resourceKey, array $data)
    {
        return array($resourceKey ?: 'data' => $data);
    }

    /**
     * Serialize an item.
     *
     * @param string $resourceKey
     * @param array  $data
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
     * @param ResourceInterface $resource
     * @param array             $data
     *
     * @return array
     */
    public function includedData(ResourceInterface $resource, array $data)
    {
        $serializedData = array();
        $linkedIds = array();
        foreach ($data as $value) {
            foreach ($value as $includeKey => $includeValue) {
                foreach ($includeValue[$includeKey] as $itemValue) {
                    if (!array_key_exists('id', $itemValue)) {
                        $serializedData[$includeKey][] = $itemValue;
                        continue;
                    }

                    $itemId = $itemValue['id'];
                    if (!empty($linkedIds[$includeKey]) && in_array($itemId, $linkedIds[$includeKey], true)) {
                        continue;
                    }

                    $serializedData[$includeKey][] = $itemValue;
                    $linkedIds[$includeKey][] = $itemId;
                }
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
