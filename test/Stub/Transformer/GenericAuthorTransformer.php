<?php namespace League\Fractal\Test\Stub\Transformer;

use League\Fractal\TransformerAbstract;

class GenericAuthorTransformer extends TransformerAbstract
{
    public function transform(array $author)
    {
        return $author;
    }
}