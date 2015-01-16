<?php

/*
 * This file is part of the League\Fractal package.
 *
 * (c) Thomas van Lankveld <thomas.van.lankveld@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\Fractal\Serializer;

use League\Fractal\Resource\ResourceInterface;

class EmberSerializer extends ArraySerializer
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
            foreach ($value as $includeCollection) {

                // Get resource key
                $includeKey = array_keys($includeCollection)[0];

                // If the collection is empty, move along
                if (empty($includeCollection[$includeKey])) {
                    continue;
                }

                // Get includes
                $includes = $includeCollection[$includeKey];

                foreach ($includes as $item) {

                    // ???
                    if (!array_key_exists('id', $item)) {
                        $serializedData[$includeKey][] = $item;
                        continue;
                    }

                    // ???
                    $itemId = $item['id'];
                    if (!empty($linkedIds[$includeKey]) && in_array($itemId, $linkedIds[$includeKey], true)) {
                        continue;
                    }

                    $serializedData[$includeKey][] = $item;
                    $linkedIds[$includeKey][] = $itemId;
                }
            }
        }

        return empty($serializedData) ? array() : $serializedData;
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
