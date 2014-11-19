<?php namespace League\Fractal\Test\Stub\Transformer;

use League\Fractal\TransformerAbstract;

class GenericBookTransformer extends TransformerAbstract
{
    protected $availableIncludes = array(
        'author',
        'reviews',
    );

    public function transform(array $book)
    {
        $book['year'] = (int) $book['year'];
        unset($book['_author'], $book['_reviews']);

        return $book;
    }

    public function includeAuthor(array $book)
    {
        if (! isset($book['_author'])) {
            return;
        }

        return $this->item($book['_author'], new GenericAuthorTransformer(), 'author');
    }

    public function includeReviews(array $book)
    {
        if (! isset($book['_reviews'])) {
            return null;
        }

        return $this->collection($book['_reviews'], new GenericReviewTransformer(), 'reviews');
    }
}
