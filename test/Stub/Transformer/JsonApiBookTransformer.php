<?php namespace League\Fractal\Test\Stub\Transformer;

use League\Fractal\TransformerAbstract;

class JsonApiBookTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'author',
    ];

    public function transform(array $book)
    {
        $book['year'] = (int) $book['year'];
        unset($book['_author']);

        return $book;
    }

    public function includeAuthor(array $book)
    {
        if (!array_key_exists('_author', $book)) {
            return;
        }

        if ($book['_author'] === null) {
            return $this->null();
        }

        return $this->item($book['_author'], new JsonApiAuthorTransformer(), 'people');
    }
}
