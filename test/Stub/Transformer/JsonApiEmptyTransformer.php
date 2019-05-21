<?php namespace League\Fractal\Test\Stub\Transformer;

use League\Fractal\TransformerAbstract;

class JsonApiEmptyTransformer extends TransformerAbstract
{
    public function transform(array $resource)
    {
        return $resource;
    }
}
