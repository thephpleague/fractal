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

use RuntimeException;

class JsonApiSerializer extends ArraySerializer
{
    /**
     * Serialize the top level data.
     * 
     * @param array $data
     * 
     * @return array
     */
    public function serializeData($resourceKey, array $data)
    {
        if (! $resourceKey) {
            throw new RuntimeException('The $resourceKey parameter has not been set on the resource.');
        }

        if (count($data) == count($data, COUNT_RECURSIVE)) {
            $data = array($data);
        }

        return array($resourceKey => $data);
    }

    /**
     * Serialize the included data.
     * 
     * @param  string  $resourceKey
     * @param  array  $data
     * @return array
     */
    public function serializeIncludedData($resourceKey, array $data)
    {
        if (empty($data)) {
            return array();
        }

        $response = array('linked' => array());

        foreach ($data as $key => $value) {
            $response['linked'] = array_merge($response['linked'], $value);
        }

        return $response;
    }

    /**
     * Serialize the available includes.
     * 
     * @param  array  $includes
     * @return array
     */
    public function serializeAvailableIncludes(array $includes)
    {
        return array();
    }
}
