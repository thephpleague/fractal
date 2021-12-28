<?php namespace League\Fractal\Test\Stub\Transformer;

use League\Fractal\TransformerAbstract;

class GenericBookTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [
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
        if (! isset($book['_author'])) {
            return;
        }

        return $this->item($book['_author'], new GenericAuthorTransformer(), 'author');
    }
}
