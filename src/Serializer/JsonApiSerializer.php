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
                    if (!$this->shouldIncludeData($includeKey, $itemValue, $linkedIds)) {
                        continue;
                    }

                    $serializedData[$includeKey][] = $itemValue;
                    $linkedIds[$includeKey][] = $itemValue['id'];
                }

                if (!empty($includeValue['linked'])) {
                    $serializedData = array_merge_recursive(
                        $serializedData,
                        $this->includedLinkedData($includeValue['linked'], $linkedIds)
                    );
                }
            }
        }

        return empty($serializedData) ? array() : array('linked' => $serializedData);
    }

    /**
     * Include data from the 'linked' section.
     *
     * @param array $data
     * @param array $linkedIds
     *
     * @return array
     */
    protected function includedLinkedData(array $data, array &$linkedIds)
    {
        $serializedData = array();
        foreach ($data as $includeKey => $values) {
            foreach ($values as $itemValue) {
                if (!$this->shouldIncludeData($includeKey, $itemValue, $linkedIds)) {
                    continue;
                }

                $serializedData[$includeKey][] = $itemValue;
                $linkedIds[$includeKey][] = $itemValue['id'];
            }
        }

        return $serializedData;
    }

    /**
     * Whether the data should be included.
     *
     * @param string $includeKey
     * @param array $item
     * @param array $linkedIds
     *
     * @return boolean False when there is no 'id' or id is already included. True otherwise
     */
    protected function shouldIncludeData($includeKey, array $item, array $linkedIds)
    {
        if (!array_key_exists('id', $item)) {
            return false;
        }
        if (!empty($linkedIds[$includeKey]) && in_array($item['id'], $linkedIds[$includeKey], true)) {
            return false;
        }
        return true;
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
