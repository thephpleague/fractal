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
use League\Fractal\Resource\ResourceAbstract;

class JsonApiSerializer extends ArraySerializer
{
    protected $baseUrl;

    public function __construct($baseUrl = null)
    {
        $this->baseUrl = $baseUrl;
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

        if ($this->shouldIncludeLinks()) {
            $resource['data']['links'] = array(
                'self' => "{$this->baseUrl}/$resourceKey/$id",
            );
        }

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
        $serializedData = $this->pullOutNestedIncludedData($resource, $data);

        $linkedIds = array();
        foreach ($data as $value) {
            foreach ($value as $includeKey => $includeObject) {
                if ($this->isCollection($includeObject)) {
                    $includeObjects = $includeObject['data'];
                }
                else {
                    $includeObjects = array($includeObject['data']);
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

    /**
     * Hook to manipulate the final sideloaded includes.
     *
     * The JSON API specification does not allow the root object to be included
     * into the sideloaded `included`-array. We have to make sure it is
     * filtered out, in case some object links to the root object in a
     * relationship.
     *
     * @param array             $includedData
     * @param ResourceInterface $resource
     *
     * @return array
     */
    public function filterIncludes($includedData, ResourceInterface $resource)
    {
        if (!isset($includedData['included'])) {
            return $includedData;
        }

        if (!$resource instanceof ResourceAbstract) {
            // In order to construct the root object, we need to know its type.
            // We can't retrieve the type if $resource won't provide it to us.
            return $includedData;
        }

        $resourceData = $resource->getData();
        if (!isset($resourceData['id'])) {
            // In order to construct the root object, we need to know its id.
            // We don't need to filter, if the root object doesn't have an id.
            return $includedData;
        }

        $rootObject = array(
            'type' => $resource->getResourceKey(),
            'id' => "{$resourceData['id']}",
        );

        // Filter out the root object
        $filteredIncludes = array_filter($includedData['included'],
            function($inclusion) use ($rootObject) {
                return !(
                    $inclusion['type'] === $rootObject['type'] &&
                    $inclusion['id'] === $rootObject['id']
                );
            }
        );

        // Reset array indizes
        $includedData['included'] = array_merge(array(), $filteredIncludes);

        return $includedData;
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

                if ($this->shouldIncludeLinks()) {
                    $data['data']['relationships'][$key] = array_merge(array(
                        'links' => array(
                            'self' => "{$this->baseUrl}/{$data['data']['type']}/{$data['data']['id']}/relationships/$key",
                            'related' => "{$this->baseUrl}/{$data['data']['type']}/{$data['data']['id']}/$key",
                        ),
                    ), $data['data']['relationships'][$key]);
                }
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

                if ($this->isCollection($includeObject)) {
                    $relationship = array('data' => array());

                    foreach ($includeObject['data'] as $object) {
                        $relationship['data'][] = array(
                            'type' => $object['type'],
                            'id' => $object['id'],
                        );
                    }
                }
                else {
                    $relationship = array(
                        'data' => array(
                            'type' => $includeObject['data']['type'],
                            'id' => $includeObject['data']['id'],
                        ),
                    );
                }

                $relationships[$includeKey][] = $relationship;
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

    /**
     * Keep all sideloaded inclusion data on the top level.
     *
     * @param ResourceInterface $resource
     * @param array             $data
     *
     * @return array
     */
    private function pullOutNestedIncludedData(ResourceInterface $resource, array $data)
    {
        $includedData = array();

        foreach ($data as $value) {
            foreach ($value as $includeKey => $includeObject) {
                if (isset($includeObject['included'])) {
                    $includedData = $includeObject['included'];
                }
            }
        }

        return $includedData;
    }

    /**
     * Whether or not the serializer should include `links` for resource objects.
     *
     * @return bool
     */
    private function shouldIncludeLinks()
    {
        return $this->baseUrl !== null;
    }
}
