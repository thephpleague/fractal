<?php


namespace League\Fractal\Test\Stub;

use League\Fractal\Serializer\ArraySerializer;

class ArraySerializerWithNull extends ArraySerializer
{
    /**
     * Serialize null resource.
     *
     * @return null
     */
    public function null()
    {
        return null;
    }
}
