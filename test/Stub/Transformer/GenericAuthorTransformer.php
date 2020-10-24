<?php namespace League\Fractal\Test\Stub\Transformer;

use League\Fractal\Test\Dto\Person;
use League\Fractal\TransformerAbstract;

class GenericAuthorTransformer extends TransformerAbstract
{
    public function transform(Person $author)
    {
        return [
            'name' => $author->name,
        ];
    }
}
