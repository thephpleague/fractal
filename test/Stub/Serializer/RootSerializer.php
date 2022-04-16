<?php

namespace League\Fractal\Test\Stub\Serializer;

use League\Fractal\Serializer\DataArraySerializer;

class RootSerializer extends DataArraySerializer
{
    /**
     * Serialize a collection.
     *
     * @param  string  $resourceKey
     * @param  array   $data
     * @return array
     */
    public function collection($resourceKey, array $data): array
    {
        return is_null($resourceKey) ? $data : [$resourceKey => $data];
    }

    /**
     * Serialize an item.
     *
     * @param  string  $resourceKey
     * @param  array   $data
     * @return array
     */
    public function item($resourceKey, array $data): array
    {
        return is_null($resourceKey) ? $data : [$resourceKey => $data];
    }
}
