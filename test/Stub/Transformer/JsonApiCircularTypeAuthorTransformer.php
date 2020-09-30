<?php namespace League\Fractal\Test\Stub\Transformer;

use League\Fractal\TransformerAbstract;

class JsonApiCircularTypeAuthorTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'books'
    ];

    public function transform(array $author)
    {
        unset($author['_books']);

        return $author;
    }

    public function includeBooks(array $author)
    {
        if (!array_key_exists('_books', $author)) {
            return;
        }

        return $this->collection($author['_books'], new JsonApiCircularTypeBookTransformer(), 'book');
    }
}
