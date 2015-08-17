<?php namespace League\Fractal\Test\Stub\Transformer;

use League\Fractal\TransformerAbstract;

class JsonApiBookTransformer extends TransformerAbstract
{
    protected $availableIncludes = array(
        'author',
    );

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

        return $this->item($book['_author'], new JsonApiAuthorTransformer(), 'people');
    }
}
