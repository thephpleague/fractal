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
        $resources = [];

        foreach ($data as $resource) {
            $resources[] = $this->item($resourceKey, $resource)['data'];
        }

        return array('data' => $resources);
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
        $id = $this->getIdFromData($data);

        $resource = array(
            'data' => array(
                'type' => $resourceKey,
                'id' => "$id",
                'attributes' => $data,
            ),
        );

        if ($id === null) {
            unset($resource['data']['id']);
        }
        else {
            unset($resource['data']['attributes']['id']);
        }

        return $resource;
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
            foreach ($value as $includeKey => $includeObject) {
                $includeType = $includeObject['data']['type'];
                $includeId = $includeObject['data']['id'];
                if (!array_key_exists("$includeType:$includeId", $linkedIds)) {
                    $serializedData[] = $includeObject['data'];
                    $linkedIds["$includeType:$includeId"] = $includeObject;
                }
            }
        }

        return empty($serializedData) ? array() : array('included' => $serializedData);
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

    public function injectData($data, $includedData)
    {
        $relationships = $this->parseRelationships($includedData);

        if (!empty($relationships)) {
            $data = $this->fillRelationships($data, $relationships);
        }

        return $data;
    }

    private function isCollection($data)
    {
        return array_key_exists('data', $data) &&
               array_key_exists(0, $data['data']);
    }

    private function fillRelationships($data, $relationships)
    {
        if ($this->isCollection($data)) {
            foreach ($relationships as $key => $relationship) {
                foreach ($relationship as $index => $relationshipData) {
                    $data['data'][$index]['relationships'][$key] = $relationshipData;
                }
            }
        }
        else { // Single resource
            foreach ($relationships as $key => $relationship) {
                $data['data']['relationships'][$key] = $relationship[0];
            }
        }

        return $data;
    }

    private function parseRelationships($includedData)
    {
        $relationships = array();

        foreach ($includedData as $inclusion) {
            foreach ($inclusion as $includeKey => $includeObject)
            {
                if (!array_key_exists($includeKey, $relationships)) {
                    $relationships[$includeKey] = array();
                }

                $relationships[$includeKey][] = array(
                    'data' => array(
                        'type' => $includeObject['data']['type'],
                        'id' => $includeObject['data']['id'],
                    ),
                );
            }
        }

        return $relationships;
    }

    private function getIdFromData(array $data)
    {
        if (!array_key_exists('id', $data)) {
            return null;
        }
        return $data['id'];
    }
}
