<?php namespace League\Fractal\Test\Stub\Transformer;

use League\Fractal\TransformerAbstract;

class JsonApiBookTransformerWithSingularType extends TransformerAbstract
{
    protected $availableIncludes = [
        'author'
    ];

    public function transform(array $book)
    {
        $book['year'] = (int) $book['year'];
        unset($book['_author']);

        return $book;
    }

    public function includeAuthor(array $book)
    {
        return $this->item($book['_author'], new JsonApiAuthorTransformerWithSingularType(), 'person');
    }
}
