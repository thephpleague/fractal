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

use InvalidArgumentException;
use League\Fractal\Resource\ResourceInterface;

class JsonApiSerializer extends ArraySerializer
{
    protected $baseUrl;
    protected $rootObjects;

    public function __construct($baseUrl = null)
    {
        $this->baseUrl = $baseUrl;
        $this->rootObjects = [];
    }

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

        return ['data' => $resources];
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

        $resource = [
            'data' => [
                'type' => $resourceKey,
                'id' => "$id",
                'attributes' => $data,
            ],
        ];

        unset($resource['data']['attributes']['id']);

        if ($this->shouldIncludeLinks()) {
            $resource['data']['links'] = [
                'self' => "{$this->baseUrl}/$resourceKey/$id",
            ];
        }

        return $resource;
    }

    public function null()
    {
        return [
            'data' => null,
        ];
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
        list($serializedData, $linkedIds) = $this->pullOutNestedIncludedData(
            $resource,
            $data
        );

        foreach ($data as $value) {
            foreach ($value as $includeKey => $includeObject) {
                if ($this->isNull($includeObject) || $this->isEmpty($includeObject)) {
                    continue;
                }
                if ($this->isCollection($includeObject)) {
                    $includeObjects = $includeObject['data'];
                }
                else {
                    $includeObjects = [$includeObject['data']];
                }

                foreach ($includeObjects as $object) {
                    $includeType = $object['type'];
                    $includeId = $object['id'];
                    $cacheKey = "$includeType:$includeId";
                    if (!array_key_exists($cacheKey, $linkedIds)) {
                        $serializedData[] = $object;
                        $linkedIds[$cacheKey] = $object;
                    }
                }
            }
        }

        return empty($serializedData) ? [] : ['included' => $serializedData];
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

    /**
     * Hook to manipulate the final sideloaded includes.
     *
     * The JSON API specification does not allow the root object to be included
     * into the sideloaded `included`-array. We have to make sure it is
     * filtered out, in case some object links to the root object in a
     * relationship.
     *
     * @param array             $includedData
     * @param array             $data
     *
     * @return array
     */
    public function filterIncludes($includedData, $data)
    {
        if (!isset($includedData['included'])) {
            return $includedData;
        }

        if ($this->isCollection($data)) {
            $this->setRootObjects($data['data']);
        }
        else {
            $this->setRootObjects([$data['data']]);
        }

        // Filter out the root objects
        $filteredIncludes = array_filter($includedData['included'], [$this, 'filterRootObject']);

        // Reset array indizes
        $includedData['included'] = array_merge([], $filteredIncludes);

        return $includedData;
    }

    /**
     * Filter function to delete root objects from array.
     *
     * @param array $object
     *
     * @return bool
     */
    protected function filterRootObject($object)
    {
        return !$this->isRootObject($object);
    }

    /**
     * Set the root objects of the JSON API tree.
     *
     * @param array $objects
     */
    protected function setRootObjects(array $objects = [])
    {
        $this->rootObjects = array_map(function($object) {
            return "{$object['type']}:{$object['id']}";
        }, $objects);
    }

    /**
     * Determines whether an object is a root object of the JSON API tree.
     *
     * @param array $object
     *
     * @return bool
     */
    protected function isRootObject($object)
    {
        $objectKey = "{$object['type']}:{$object['id']}";
        return in_array($objectKey, $this->rootObjects);
    }

    protected function isCollection($data)
    {
        return array_key_exists('data', $data) &&
               array_key_exists(0, $data['data']);
    }

    protected function isNull($data)
    {
        return array_key_exists('data', $data) && $data['data'] === null;
    }

    protected function isEmpty($data) {
        return array_key_exists('data', $data) && $data['data'] === [];
    }

    protected function fillRelationships($data, $relationships)
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

                if ($this->shouldIncludeLinks()) {
                    $data['data']['relationships'][$key] = array_merge([
                        'links' => [
                            'self' => "{$this->baseUrl}/{$data['data']['type']}/{$data['data']['id']}/relationships/$key",
                            'related' => "{$this->baseUrl}/{$data['data']['type']}/{$data['data']['id']}/$key",
                        ],
                    ], $data['data']['relationships'][$key]);
                }
            }
        }

        return $data;
    }

    protected function parseRelationships($includedData)
    {
        $relationships = [];

        foreach ($includedData as $inclusion) {
            foreach ($inclusion as $includeKey => $includeObject)
            {
                if (!array_key_exists($includeKey, $relationships)) {
                    $relationships[$includeKey] = [];
                }

                if ($this->isNull($includeObject)) {
                    $relationship = $this->null();
                }
                elseif ($this->isEmpty($includeObject)) {
                    $relationship = [
                        'data' => [],
                    ];
                }
                elseif ($this->isCollection($includeObject)) {
                    $relationship = ['data' => []];

                    foreach ($includeObject['data'] as $object) {
                        $relationship['data'][] = [
                            'type' => $object['type'],
                            'id' => $object['id'],
                        ];
                    }
                }
                else {
                    $relationship = [
                        'data' => [
                            'type' => $includeObject['data']['type'],
                            'id' => $includeObject['data']['id'],
                        ],
                    ];
                }

                $relationships[$includeKey][] = $relationship;
            }
        }

        return $relationships;
    }

    protected function getIdFromData(array $data)
    {
        if (!array_key_exists('id', $data)) {
            throw new InvalidArgumentException(
                'JSON API resource objects MUST have a valid id'
            );
        }
        return $data['id'];
    }

    /**
     * Keep all sideloaded inclusion data on the top level.
     *
     * @param ResourceInterface $resource
     * @param array             $data
     *
     * @return array
     */
    protected function pullOutNestedIncludedData(ResourceInterface $resource, array $data)
    {
        $includedData = [];
        $linkedIds = [];

        foreach ($data as $value) {
            foreach ($value as $includeKey => $includeObject) {
                if (isset($includeObject['included'])) {
                    foreach ($includeObject['included'] as $object) {
                        $includeType = $object['type'];
                        $includeId = $object['id'];
                        $cacheKey = "$includeType:$includeId";

                        if (!array_key_exists($cacheKey, $linkedIds)) {
                            $includedData[] = $object;
                            $linkedIds[$cacheKey] = $object;
                        }
                    }
                }
            }
        }

        return [$includedData, $linkedIds];
    }

    /**
     * Whether or not the serializer should include `links` for resource objects.
     *
     * @return bool
     */
    protected function shouldIncludeLinks()
    {
        return $this->baseUrl !== null;
    }
}
