<?php namespace League\Fractal\Test\Stub\Transformer;

use League\Fractal\TransformerAbstract;

class JsonApiCircularTypeBookTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'prequel'
    ];

    public function transform(array $book)
    {
        unset($book['_prequel']);

        return $book;
    }

    public function includePrequel(array $book)
    {
        if (!array_key_exists('_prequel', $book)) {
            return;
        }

        return $this->item($book['_prequel'], new self(), 'book');
    }
}
