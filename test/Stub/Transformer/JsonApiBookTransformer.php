<?php namespace League\Fractal\Test\Stub\Transformer;

use League\Fractal\TransformerAbstract;

class JsonApiBookTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [
        'author',
        'co-author',
        'author-with-meta',
    ];

    public function transform(array $book)
    {
        $book['year'] = (int) $book['year'];
        unset($book['_author']);
        unset($book['_co_author']);

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

    public function includeAuthorWithMeta(array $book)
    {
        if (!array_key_exists('_author', $book)) {
            return;
        }

        if ($book['_author'] === null) {
            return $this->null();
        }

        return $this->item($book['_author'], new JsonApiAuthorTransformer(), 'people')
            ->setMeta(['foo' => 'bar']);
    }

    public function includeCoAuthor(array $book)
    {
        if (!array_key_exists('_co_author', $book)) {
            return;
        }

        if ($book['_co_author'] === null) {
            return $this->null();
        }

        return $this->item($book['_co_author'], new JsonApiAuthorTransformer(), 'people');
    }
}
