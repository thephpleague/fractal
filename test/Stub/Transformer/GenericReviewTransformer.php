<?php namespace League\Fractal\Test\Stub\Transformer;

use League\Fractal\TransformerAbstract;

class GenericReviewTransformer extends TransformerAbstract
{
    protected $availableIncludes = array(
        'author',
    );

    public function transform(array $review)
    {
        unset($review['_author']);

        return $review;
    }

    public function includeAuthor(array $review)
    {
        if (! isset($review['_author'])) {
            return null;
        }

        return $this->item($review['_author'], new GenericAuthorTransformer(), 'author');
    }
}
