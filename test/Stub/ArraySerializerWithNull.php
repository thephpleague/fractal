<?php

namespace League\Fractal\Test\Stub;

use League\Fractal\Serializer\ArraySerializer;

class ArraySerializerWithNull extends ArraySerializer
{
    public function null(): ?array
    {
        return null;
    }
}
