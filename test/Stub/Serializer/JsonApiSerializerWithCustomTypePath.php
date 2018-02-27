<?php namespace League\Fractal\Test\Stub\Serializer;

use League\Fractal\Serializer\JsonApiSerializer;

class JsonApiSerializerWithCustomTypePath extends JsonApiSerializer
{
    protected function getTypePath($type)
    {
        // This would be a pluralize function
        $map = [
            'person' => 'persons',
            'book' => 'books'
        ];

        return $map[$type];
    }
}
