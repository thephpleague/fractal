<?php


namespace League\Fractal\Serializer;

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
