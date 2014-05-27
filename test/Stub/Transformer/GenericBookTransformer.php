<?php namespace League\Fractal\Test\Stub\Transformer;

use League\Fractal\TransformerAbstract;

class GenericBookTransformer extends TransformerAbstract
{
    protected $availableIncludes = array(
        'author'
    );

    public function transform(array $book)
    {
        return array(
            'title' => $book['title'],
            'year' => (int) $book['year'],
        );
    }

    public function includeAuthor(array $book)
    {
        if (! isset($book['_author'])) {
            return null;
        }

        return $this->item($book['_author'], new GenericAuthorTransformer, 'author');
    }
}